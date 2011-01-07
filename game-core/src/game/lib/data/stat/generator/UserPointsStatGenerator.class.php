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

require_once(LW_DIR.'lib/data/stat/generator/AbstractStatGenerator.class.php');

/**
 * This class creates the stats of the points of the users.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2010 Lost Worlds <http://lost-worlds.net>
 */
class UserPointsStatGenerator extends AbstractStatGenerator {
	public $template = 'userPointsStatBit';
	
	/**
	 * @see AbstractStatGenerator::__construct()
	 */
	public function __construct($statTypeID) {
		parent::__construct($statTypeID);
		
		// user
		$this->sqlSelects .= "ugml_users.id
									AS userID,
								ugml_users.username,";
		$this->sqlJoins .= " LEFT JOIN ugml_users
								ON ugml_stat_entry.relationalID = ugml_users.id ";
		
		// alliance
		$this->sqlSelects .= "ugml_alliance.id
									AS allianceID,
								ugml_alliance.ally_tag
									AS allianceTag,
								ugml_alliance.ally_name
									AS allianceName,";
		$this->sqlJoins .= " LEFT JOIN ugml_alliance
								ON ugml_users.ally_id = ugml_alliance.id ";
	}
	
	/**
	 * Creates the dummy entries.
	 */
	protected function generateDummies() {
		$sql = "INSERT IGNORE INTO ugml_stat_entry
				(statTypeID, relationalID)
				SELECT ".$this->statTypeID.", id
				FROM ugml_users";
		WCF::getDB()->sendQuery($sql);
		WCF::getDB()->sendQuery("COMMIT");
	}
	
	/**
	 * @see AbstractStatGenerator::generateEntries()
	 */
	protected function generateEntries($tryAgainOnError = true) {	
		WCF::getDB()->sendQuery("SET AUTOCOMMIT = 0");		
		WCF::getDB()->sendQuery("START TRANSACTION");
		
		try {
			$this->generateDummies();
			
			// planet subselect
			$specs = Spec::getByFlag(0x39, true);
			$generated = "";
			
			foreach($specs as $specID => $specObj) {
				if(!empty($generated)) {
					$generated .= " + ";
				}
				$costs = ($specObj->costsMetal + $specObj->costsCrystal + $specObj->costsDeuterium);
				
				if($specObj->costsFactor != 1) {
					$generated .= "(".$costs." * (1 - POW(".$specObj->costsFactor.", `".$specObj->colName."`)) / -(".$specObj->costsFactor." - 1))";
				}
				else {
					$generated .= "(".$costs." * `".$specObj->colName."`)";
				}
			}
			
			$planet = "SELECT SUM(".$generated.")
					FROM ugml_planets
					WHERE ugml_planets.id_owner = ugml_stat_entry.relationalID";
			
			// user subselect
			$specs = Spec::getByFlag(0x02, true);
			//var_dump($specs);
			$generated = "";
			
			foreach($specs as $specID => $specObj) {
				if(!empty($generated)) {
					$generated .= " + ";
				}
				$costs = ($specObj->costsMetal + $specObj->costsCrystal + $specObj->costsDeuterium);
				
				if($specObj->costsFactor != 1) {
					$generated .= "(".$costs." * (1 - POW(".$specObj->costsFactor.", `".$specObj->colName."`)) / -(".$specObj->costsFactor." - 1))";
				}
				else {
					$generated .= "(".$costs." * `".$specObj->colName."`)";
				}
			}
			
			$user = "SELECT ".$generated."
					FROM ugml_users
					WHERE ugml_users.id = ugml_stat_entry.relationalID";
			
			// fleet subselect
			$specs = Spec::getByFlag(0x48, true);
			$generated = "";
			
			foreach($specs as $specID => $specObj) {
				$costs = ($specObj->costsMetal + $specObj->costsCrystal + $specObj->costsDeuterium);
				
				$generated .= " WHEN ".$specID." THEN ".$costs;

			}
			
			$fleet = "SELECT COALESCE(SUM(CASE ugml_fleet_spec.specID".$generated." ELSE 0 END * shipCount), 0)
					FROM ugml_fleet
					LEFT JOIN ugml_fleet_spec
						ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
					WHERE ugml_fleet.ownerID = ugml_stat_entry.relationalID";
			
			$sql = "UPDATE ugml_stat_entry,
						ugml_users
							AS checkUser
					SET points = ((".$planet.") + (".$user.") + (".$fleet.")) / 1000
					WHERE ugml_stat_entry.relationalID = checkUser.id
						AND ugml_stat_entry.statTypeID = 1
						AND checkUser.banned != 1
						AND checkUser.authlevel = 0";
			echo $sql;
			// lets go!
			WCF::getDB()->sendQuery($sql);
			
			WCF::getDB()->sendQuery("COMMIT");
			WCF::getDB()->sendQuery("SET AUTOCOMMIT = 1");
		}
		catch(DatabaseException $e) {
			WCF::getDB()->sendQuery("ROLLBACK");
			WCF::getDB()->sendQuery("SET AUTOCOMMIT = 1");
			
			if($tryAgainOnError) {
				$this->generateEntries(false);
			}
		}		
	}
	
	/**
	 * @see AbstractStatGenerator::getCurrentRelationalID()
	 */
	public function getCurrentRelationalID() {
		return WCF::getUser()->userID;
	}
}
?>