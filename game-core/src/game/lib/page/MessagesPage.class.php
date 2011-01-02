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
require_once(LW_DIR.'lib/data/message/NMessage.class.php');
require_once(LW_DIR.'lib/data/message/NMessageEditor.class.php');

/**
 * This page views all the messages in a users' inbox.
 * 
 * @author		Biggerskimo
 * @copyright	2010 - 2011 Lost Worlds <http://lost-worlds.net>
 */
class MessagesPage extends AbstractPage {
	public $templateName = 'messages';
	
	public $remembered = null;
	public $messages = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['remembered'])) $this->remembered = 1;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->messages = NMessage::getByUserID(WCF::getUser()->userID, $this->remembered);
		
		// update data
		$messageUpdates = array();
		foreach($this->messages as $message)
		{
			if(!$message->viewed)
				$messageUpdates[] = $message->messageID;
		}
		if(count($messageUpdates))
			NMessageEditor::view($messageUpdates);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'messages' => $this->messages,
			'remembered' => $this->remembered
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