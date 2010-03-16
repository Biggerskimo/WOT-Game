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

require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * This class holds game account-related information.
 *
 * @author		Biggerskimo
 * @copyright 	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.user
 */
class Account extends DatabaseObject {
	/**
	 * Creates a new account object.
	 * 
	 * @param	int		user id
	 * @param	array	user row
	 */
	public function __construct($userID, $row = null) {
		if($row === null) {
			$sql = "SELECT *
					FROM ugml_users
					WHERE id = ".$userID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
		
		$this->userID = intval($this->id);
	}

	/**
	 * Returns a array with the planets of this user.
	 * 
	 * @param	bool	with moons
	 * @return	array
	 */
	public function getPlanets($moons = false) {
		$planets = array();
		
		$sql = "SELECT *
				FROM ugml_planets
				WHERE id_owner = ".$this->userID;
		if(!$moons) {
			$sql .= " AND planet_type = 1";
		}
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			 $planets[$row['id']] = $row;
		}
		
		return $planets;
	}
}
?>