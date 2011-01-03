<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(LW_DIR.'lib/data/message/sender/UserMessageSender.class.php');
require_once(LW_DIR.'lib/data/message/NMessage.class.php');

/**
 * Deletes or remembers a message.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class MessageManipulationAction extends AbstractAction {
	public $command = '';
	public $messageID = 0;
	public $folderIDs = array();

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['messageID']))
			$this->messageID = intval($_REQUEST['messageID']);
		
		if(isset($_REQUEST['command']))
			$this->command = StringUtil::trim($_REQUEST['command']);
		
		if(isset($_REQUEST['folderIDs']))
			$this->folderIDs = ArrayUtil::toIntegerArray(explode(',', $_REQUEST['folderIDs']));
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		if (!WCF::getUser()->userID) {
			die('invalid userID');
		}
		
		if(!preg_match('/^(?:check(?:|All|Visible)|delete(?:|All|(?:Unc|C)hecked)|notify|uncheck(?:Checked|Visible))$/', $this->command))
		{
			die('invalid command');
		}
		
		// message-related commands
		if(preg_match('/^(?:check|delete|notify)$/', $this->command))
		{
			$message = new NMessage($this->messageID);
			if(!$message->messageID || $message->recipentID != WCF::getUser()->userID)
			{
				die('invalid messageID');
			}
			if($this->command == 'notify' && !($message->getSender() instanceof UserMessageSender))
				die('invalid messageID');
			
			$editor = $message->getEditor();
		}
		
		if($this->command == 'check')
			$editor->check();
		if($this->command == 'checkAll')
			NMessageEditor::checkAll(WCF::getUser()->userID);
		if($this->command == 'checkVisible')
			NMessageEditor::checkAll(WCF::getUser()->userID, 1, $this->folderIDs);
		if($this->command == 'delete')
			$editor->delete();
		if($this->command == 'deleteAll')
			NMessageEditor::deleteAll(WCF::getUser()->userID);
		if($this->command == 'deleteChecked')
			NMessageEditor::deleteAll(WCF::getUser()->userID, 1);
		if($this->command == 'deleteUnchecked')
			NMessageEditor::deleteAll(WCF::getUser()->userID, 0);
		if($this->command == 'notify')
			$message->notify();
		if($this->command == 'uncheckChecked')
			NMessageEditor::checkAll(WCF::getUser()->userID, 0);
		if($this->command == 'uncheckVisible')
			NMessageEditor::checkAll(WCF::getUser()->userID, 0, $this->folderIDs);
			
		$this->executed();
		
		// message-related commands
		if(preg_match('/^(?:check|delete|notify)$/', $this->command))
			die('done');

		$matches = array();
		$referrer = $_SERVER["HTTP_REFERER"];
		$url = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		preg_match('/^https?:(\/\/[^\/]*)\/[^\?]*\??[^\?]*$/', $url, $matches);
		$base = $matches[1];
		preg_match('/^https?:(\/\/[^\/]*)\/[^\?]*\??[^\?]*$/', $referrer, $matches);
		$base2 = $matches[1];
		
		if($base == $base2)
			header('Location: '.$referrer);
		else
			header('Location: index.php?page=Messages');
		
		exit;
	}
}
?>