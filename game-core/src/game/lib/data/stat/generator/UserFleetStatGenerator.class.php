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

require_once(LW_DIR.'lib/data/stat/generator/UserPointsStatGenerator.class.php');

/**
 * This class creates the stats of the points of the users.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class UserFleetStatGenerator extends UserPointsStatGenerator {	
	/**
	 * @see AbstractStatGenerator::generateEntries()
	 */
	protected function generateEntries() {
		$this->generateDummies();
		
		// planet subselect
		$specs = Spec::getByFlag(0x08, true);
		$generated = "";
		
		foreach($specs as $specID => $specObj) {
			// planet		
			if(!empty($generated)) {
				$generated .= " + ";
			}
			
			$generated .= "`".$specObj->colName."`";
		}
		$planet = "SELECT SUM(".$generated.")
				FROM ugml_planets
				WHERE ugml_planets.id_owner = ugml_stat_entry.relationalID";
		
		// fleet subselect
		$fleet = "SELECT COALESCE(SUM(shipCount), 0)
				FROM ugml_fleet
				LEFT JOIN ugml_fleet_spec
					ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
				WHERE ugml_fleet.ownerID = ugml_stat_entry.relationalID";
		
		$sql = "UPDATE ugml_stat_entry,
					ugml_users
						AS checkUser
				SET points = (".$planet.") + (".$fleet.")
				WHERE ugml_stat_entry.relationalID = checkUser.id
					AND ugml_stat_entry.statTypeID = ".$this->statTypeID."
					AND checkUser.banned = 0
					AND checkUser.authlevel = 0";
		// lets go!
		WCF::getDB()->sendQuery($sql);
	}
}
?>