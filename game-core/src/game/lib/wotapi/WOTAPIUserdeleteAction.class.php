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

require_once(LW_DIR.'lib/wotapi/AbstractWOTAPIAction.class.php');

require_once(LW_DIR.'lib/data/account/AccountEditor.class.php');

/**
 * Deletes a account.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIUserdeleteAction extends AbstractWOTAPIAction {
	public $userIDsStr = '';
	
	public $userIDsArray = array();
	public $userObjsArray = array();
		
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->userIDsStr = WOTAPIUtil::escape($this->data['useridsstr']);
	}

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		$this->userIDsArray = explode(',', $this->userIDsStr);
		
		foreach($this->userIDsArray as $userID) {
			$accountEditor = new AccountEditor($userID);
			
			$accountEditor->delete();
			
			$this->userObjsArray[$userID] = $accountEditor;
		}
		
		parent::execute();
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();
	
		$this->wotAPIServerClient->send('account successful deleted');		
	}
}
?>