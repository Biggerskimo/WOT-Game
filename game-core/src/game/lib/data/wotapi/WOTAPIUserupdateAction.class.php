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

/**
 * Updates the data of a user.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIUserupdateAction extends AbstractWOTAPIAction {	
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->data['userid'] = intval($this->data['userid']);
	}

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		$sql = "SELECT username,
					email_2
				FROM ugml_users
				WHERE id = ".$this->data['userid'];
		$result = WCF::getDB()->getFirstRow($sql);
		
		if(empty($this->data['username'])) $username = $result['username'];
		else $username = $this->data['username'];
		
		if(empty($this->data['email'])) $email = $result['email_2'];
		else $email = $this->data['email'];
		
		$sql = "UPDATE ugml_users
				SET username = '".escapeString($username)."',
					email = '".escapeString($email)."',
					email_2 = '".escapeString($email)."'
				WHERE id = ".$this->data['userid'];
		WCF::getDB()->sendQuery($sql);
		
		parent::answer();
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