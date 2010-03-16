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

require_once(LW_DIR.'lib/data/wotapi/AbstractWOTAPIAction.class.php');
require_once(LW_DIR.'lib/data/account/AccountEditor.class.php');

/**
 * Changes the owners of two accounts.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIUserswitchAction extends AbstractWOTAPIAction {	
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		$this->data['userid1'] = intval($this->data['userid1']);
		$this->data['userid2'] = intval($this->data['userid2']);
	}

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		$account = new AccountEditor($this->data['userid1']);
		
		$account->doSwitch($this->data['userid2']);
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		$this->wotAPIServerClient->send('users successful switched');		
	}
}
?>