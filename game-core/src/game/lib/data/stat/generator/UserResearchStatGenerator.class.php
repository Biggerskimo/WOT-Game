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
 * This class creates the stats of the research points of the users.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class UserResearchStatGenerator extends UserPointsStatGenerator {	
	/**
	 * @see AbstractStatGenerator::generateEntries()
	 */
	protected function generateEntries() {
		$this->generateDummies();
		
		// user subselect
		$specs = Spec::getByFlag(0x02, true);
		$generated = "";
		
		foreach($specs as $specID => $specObj) {
			// planet		
			if(!empty($generated)) {
				$generated .= " + ";
			}
			
			$generated .= "`".$specObj->colName."`";
		}
		$user = "SELECT ".$generated."
				FROM ugml_users
				WHERE ugml_users.id = ugml_stat_entry.relationalID";
		
		$sql = "UPDATE ugml_stat_entry,
					ugml_users
						AS checkUser
				SET points = (".$user.")
				WHERE ugml_stat_entry.relationalID = checkUser.id
					AND ugml_stat_entry.statTypeID = ".$this->statTypeID."
					AND checkUser.banned = 0
					AND checkUser.authlevel = 0";
		// lets go!
		WCF::getDB()->sendQuery($sql);
	}
}
?>