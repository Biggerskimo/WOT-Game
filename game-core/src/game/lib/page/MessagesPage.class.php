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

require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(LW_DIR.'lib/data/message/MessageFolder.class.php');
require_once(LW_DIR.'lib/data/message/NMessage.class.php');
require_once(LW_DIR.'lib/data/message/NMessageEditor.class.php');

/**
 * This page views all the messages in a users' inbox.
 * 
 * @author		Biggerskimo
 * @copyright	2010 - 2011 Lost Worlds <http://lost-worlds.net>
 */
class MessagesPage extends AbstractPage {
	const MESSAGES = 10;
	const MESSAGES_FOLDERS = 10;
	
	public $templateName = 'messages';
	
	public $checked = null;
	public $active = null;
	public $spare = array();
	public $pageNo = 1;
	public $messageID = 0;
	
	public $messages = array();
	public $folders = array();
	public $nextPage = false;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['checked'])) $this->checked = 1;
		if(isset($_REQUEST['active'])) $this->active = ArrayUtil::toIntegerArray(explode(',', $_REQUEST['active']));
		if(isset($_REQUEST['pageNo'])) $this->pageNo = intval($_REQUEST['pageNo']);
		if(isset($_REQUEST['messageID'])) $this->messageID = intval($_REQUEST['messageID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if(WCF::getUser()->hasDiliziumFeature("messageFolders"))
		{
			$this->folders = MessageFolder::getByUserID(WCF::getUser()->userID);
			
			if(WCF::getUser()->new_message && $this->active === null && !$this->checked) {
				// show new messages
				$this->messages = NMessage::getByUserID(WCF::getUser()->userID, null, null, true, self::MESSAGES_FOLDERS + 1, ($this->pageNo - 1) * self::MESSAGES_FOLDERS);
			
				foreach($this->messages as $message) {
					if(!isset($this->spare[$message->folderID])) {
						$this->spare[$message->folderID] = $message->folderID;
					}
				}
			}
			else {
				// show requested messages
				if($this->active === null && !$this->checked) {
					$this->active = array();
				}
				$this->messages = NMessage::getByUserID(WCF::getUser()->userID, $this->checked, $this->active, false, self::MESSAGES_FOLDERS + 1, ($this->pageNo - 1) * self::MESSAGES_FOLDERS);
			}
			$this->nextPage = count($this->messages) > self::MESSAGES_FOLDERS;
			
			if($this->nextPage)
				array_pop($this->messages);
		}
		else
		{
			$this->pageNo = 1;
			$this->messages = NMessage::getByUserID(WCF::getUser()->userID, $this->checked, $this->active, false, self::MESSAGES);
		}
		
		// update data
		$messageUpdates = array();
		foreach($this->messages as $message)
		{
			if(!$message->viewed)
				$messageUpdates[] = $message->messageID;
		}
		if(count($messageUpdates))
			NMessageEditor::view($messageUpdates, WCF::getUser()->userID);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'active' => $this->active,
			'spare' => $this->spare,
			'folders' => $this->folders,
			'messages' => $this->messages,
			'checked' => $this->checked,
			'pageNo' => $this->pageNo,
			'nextPage' => $this->nextPage,
			'messageID' => $this->messageID
		));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
?>