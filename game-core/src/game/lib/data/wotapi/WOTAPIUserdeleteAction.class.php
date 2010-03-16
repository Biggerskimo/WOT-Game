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
 * Deletes a user
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIUserdeleteAction extends AbstractWOTAPIAction {
	public static $tables = array(
			array('ugml_alliance', 'ally_owner'),
			array('ugml_buddy', 'sender'),
			array('ugml_buddy', 'owner'),
			array('ugml_fleets', 'fleet_owner'),
			array('ugml_fleets', 'fleet_ofiara'),
			array('ugml_fleet_queue', 'userID'),
			array('ugml_galactic_jump_queue', 'userID'),
			array('ugml_messages', 'message_owner'),
			array('ugml_messages', 'message_sender'),
			array('ugml_naval_formation_to_users', 'userID'),
			array('ugml_notes', 'owner'),
			array('ugml_planets', 'id_owner'),
			array('ugml_stat', 'userID'),
			array('ugml_users', 'id')
		);

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		foreach(self::$tables as $tableArray) {
			$table = $tableArray[0];
			$col = $tableArray[1];
			
			$sql = "DELETE FROM ".$table."
					WHERE ".$col." IN(".$this->data['useridstr'].")";			
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		$this->wotAPIServerClient->send('account successful deleted');		
	}
}
?>