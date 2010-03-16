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
 * Updates the data of a user.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIUserupdateAction extends AbstractWOTAPIAction {	
	public $userID = 0;
	public $username = '';
	public $email = '';
	
	public $accountObj = null;
	
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->userID = intval($this->data['userid']);
		if(isset($this->data['username'])) $this->username = WOTAPIUtil::escape($this->data['username']);
		if(isset($this->data['email'])) $this->email = WOTAPIUtil::escape($this->data['email']);
	}

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		$this->accountObj = new AccountEditor($this->userID);
		
		if(empty($this->username)) $username = $this->accountObj->username;
		else $username = $this->username;
		
		if(empty($this->email)) $email = $this->accountObj->email_2;
		else $email = $this->email;
		
		$this->accountObj->update($username, $email);
		
		parent::execute();
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();
		
		$this->wotAPIServerClient->send('user successful updated');		
	}
}
?>