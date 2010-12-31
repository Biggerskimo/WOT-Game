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

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['messageID'])) {
			$this->messageID = intval($_REQUEST['messageID']);
		}
		
		if(isset($_REQUEST['command'])) {
			$this->command = StringUtil::trim($_REQUEST['command']);
		}
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
		
		if($this->command != 'delete' && $this->command != 'remember'
			&& $this->command != 'notify')
		{
			die('invalid command');
		}
		
		$message = new NMessage($this->messageID);
		if(!$message->messageID || $message->recipentID != WCF::getUser()->userID)
		{
			die('invalid messageID');
		}
		if($this->command == 'notify' && !($message->getSender() instanceof UserMessageSender))
			die('invalid messageID');
		
		$editor = $message->getEditor();
		
		if($this->command == 'delete')
			$editor->delete();
		if($this->command == 'remember')
			$editor->remember();
		if($this->command == 'notify')
			$message->notify();
		
		$this->executed();
		
		die('done');
	}
}
?>