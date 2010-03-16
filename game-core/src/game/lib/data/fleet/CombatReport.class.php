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
 * Includes functions for handling combat reports.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class CombatReport extends DatabaseObject {
	public $users = array();
	
	/**
	 * Creates a new CombatReport object.
	 * 
	 * @param	int		report id
	 * @param	array	db row
	 */
	public function __construct($reportID, $row = null) {
		if($row === null) {
		    $sql = "SELECT ugml_combat_report.*,
						GROUP_CONCAT(ugml_combat_report_user.userID SEPARATOR ',')
							AS users
					FROM ugml_combat_report
					LEFT JOIN ugml_combat_report_user
						ON ugml_combat_report.reportID = ugml_combat_report_user.reportID
					WHERE ugml_combat_report.reportID = ".$reportID."
					GROUP BY ugml_combat_report.reportID";
		    $row = WCF::getDB()->getFirstRow($sql);
		}

		parent::__construct($row);
		
		// create users array
		$parts = explode(',', $this->data['users']);
		
		foreach($parts as $userID) {
			if(strlen($userID) > 0) {
				$this->users[$userID] = new LWUser($userID);
			}
		}
	}
	
	/**
	 * Adds users to the list of allowed users.
	 * 
	 * @param	mixed	integer or array
	 */
	public function addUsers($array) {
		if(!is_array($array)) {
			$array = array($array => $array);
		}
		
		$inserts = "";
		foreach($array as $userID => $unused) {
			if(isset($this->users[$userID])) {
				continue;
			}
			
			if(!empty($inserts)) {
				$inserts .= ",";
			}
			$inserts .= "(".$this->reportID.", ".$userID.")";
			$this->users[$userID] = new LWUser($userID);
		}
		if(!empty($inserts)) {
			$sql = "INSERT INTO ugml_combat_report_user
					(reportID, userID)
					VALUES ".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Creates a new combat report database representation.
	 * 
	 * @param	int		time
	 * @param	string	text
	 * @param	boolean	one round
	 * @param	array	users
	 * @return	CombatReport
	 */
	public static function create($time, $text, $oneRound, $users) {
		$reportID = self::insert($time, $text, $oneRound);
		$reportObj = new CombatReport($reportID);
		
		$reportObj->addUsers($users);
		
		return $reportObj;
	}
	
	/**
	 * Inserts a new combat report database row.
	 * 
	 * @param	int		time
	 * @param	string	text
	 * @param	boolean	one round
	 * @return	int		report id
	 */
	public static function insert($time, $text, $oneRound) {
		$sql = "INSERT INTO ugml_combat_report
				(`time`, text, oneRound)
				VALUES
				(".$time.", '".escapeString($text)."', ".($oneRound ? 1 : 0).")";
		WCF::getDB()->sendQuery($sql);
		
		$reportID = WCF::getDB()->getInsertID();
		
		return $reportID;
	}
}
?>