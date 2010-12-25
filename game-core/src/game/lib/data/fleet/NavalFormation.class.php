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
require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/fleet/NavalFormationEditor.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');

/**
 * Handles functions for handling naval formation fleets.
 * 
 * @author		Biggerskimo
 * @copyright	2008-2010 Lost Worlds <http://lost-worlds.net>
 */
class NavalFormation extends DatabaseObject {
	const MAX_TIME_MOVEMENT = 30;
	const MAX_USERS = 20;
	
	protected $usersWithFleets = array();
	
	public $users = array();
	public $fleets = array();

	protected $editor = null;
	
	/**
	 * Creates a new NavalFormation object.
	 * 
	 * @param	int		nf id
	 * @param	array	db row
	 */
	public function __construct($navalFormationID, $row = null) {
		if($row === null) {
		    $sql = "SELECT ugml_naval_formation.*,
		    			GROUP_CONCAT(
		    				CONCAT(ugml_naval_formation_to_users.userID, ',', ugml_naval_formation_to_users.joinTime)
		    				SEPARATOR ';')
		    			AS users,
		    			GROUP_CONCAT(
		    				CONCAT(ugml_fleet.ownerID, ',', ugml_fleet.fleetID)
		    				SEPARATOR ';')
		    			AS fleets
					FROM ugml_naval_formation
					LEFT JOIN ugml_naval_formation_to_users
						ON ugml_naval_formation.formationID = ugml_naval_formation_to_users.formationID
					LEFT JOIN ugml_fleet
						ON ugml_naval_formation.formationID = ugml_fleet.formationID
					WHERE ugml_naval_formation.formationID = ".intval($navalFormationID)."
					GROUP BY ugml_naval_formation.formationID";
		    $row = WCF::getDB()->getFirstRow($sql);
		}

		parent::__construct($row);
		
		// create users array
		$parts = explode(';', $this->data['users']);
		
		foreach($parts as $part) {
			if(strlen($part) > 2) {
				list($userID, $joinTime) = explode(',', $part);
				
				if(!isset($this->users[$userID])) {
					$this->users[$userID] = new LWUser($userID);
					$this->users[$userID]->joinTime = $joinTime;
				}
			}
		}
		
		// create fleets array
		$parts = explode(';', $this->data['fleets']);
		
		foreach($parts as $part) {
			if(strlen($part) > 2) {
				list($userID, $fleetID) = explode(',', $part);
				
				$this->fleets[$fleetID] = Fleet::getInstance($fleetID);
			}
		}
	}
	
	/**
	 * Returns the naval formation of a fleet.
	 * 
	 * @param	int		fleet id
	 * @return	NavalFormation
	 */
	public static function getByFleetID($fleetID, $editor = false) {
		$sub = "SELECT formationID
				FROM ugml_fleet
				WHERE fleetID = ".intval($fleetID);
		
		
	    $sql = "SELECT ugml_naval_formation.*,
	    			GROUP_CONCAT(
	    				CONCAT(ugml_naval_formation_to_users.userID, ',', ugml_naval_formation_to_users.joinTime)
	    				SEPARATOR ';')
	    			AS users,
	    			GROUP_CONCAT(
	    				CONCAT(ugml_fleet.ownerID, ',', ugml_fleet.fleetID)
	    				SEPARATOR ';')
	    			AS fleets
				FROM ugml_naval_formation
				LEFT JOIN ugml_naval_formation_to_users
					ON ugml_naval_formation.formationID = ugml_naval_formation_to_users.formationID
				LEFT JOIN ugml_fleet
					ON ugml_naval_formation.formationID = ugml_fleet.formationID
				WHERE ugml_naval_formation.formationID IN (".$sub.")
				GROUP BY ugml_naval_formation.formationID";
	    $row = WCF::getDB()->getFirstRow($sql);
	    
	    if(!$row['formationID']) {
	    	return null;
	    }
	    
	    return new NavalFormation(null, $row);
	}
	
	/**
	 * Returns the naval formations of a user as a array.
	 * 
	 * @param	int		user id
	 * @return	array
	 */
	public static function getByUserID($userID, $editor = false) {
		$navalFormations = array();	
		
		$sub = "SELECT ugml_naval_formation_to_users.formationID
				FROM ugml_naval_formation_to_users
				WHERE ugml_naval_formation_to_users.userID = ".intval($userID);		
		$sql = "SELECT ugml_naval_formation.*,
	    			GROUP_CONCAT(
	    				CONCAT(ugml_naval_formation_to_users.userID, ',', ugml_naval_formation_to_users.joinTime)
	    				SEPARATOR ';')
	    			AS users,
	    			GROUP_CONCAT(
	    				CONCAT(ugml_fleet.ownerID, ',', ugml_fleet.fleetID)
	    				SEPARATOR ';')
	    			AS fleets
				FROM ugml_naval_formation
				LEFT JOIN ugml_naval_formation_to_users
					ON ugml_naval_formation.formationID = ugml_naval_formation_to_users.formationID
				LEFT JOIN ugml_fleet
					ON ugml_naval_formation.formationID = ugml_fleet.formationID
				WHERE ugml_fleet.formationID IN (".$sub.")
				GROUP BY ugml_naval_formation.formationID";
	    $result = WCF::getDB()->sendQuery($sql);
	    
	    while($row = WCF::getDB()->fetchArray($result)) {
	    	$navalFormations[$row['formationID']] = new NavalFormation(null, $row);
	    }

	    return $navalFormations;
	}
	
	/**
	 * Returns the naval formations of a user as a array.
	 * 
	 * @param	int		planet id
	 * @param	int		user id
	 * @return	array
	 */
	public static function getByTargetPlanetID($planetID, $userID = null, $editor = false) {
		$navalFormations = array();	
		
		$sub = "SELECT ugml_naval_formation.formationID
				FROM ugml_naval_formation
				LEFT JOIN ugml_naval_formation_to_users
					ON ugml_naval_formation.formationID = ugml_naval_formation_to_users.formationID
				WHERE ugml_naval_formation.endPlanetID = ".intval($planetID).
				($userID !== null ? " AND ugml_naval_formation_to_users.userID = ".intval($userID) : "");		
		$sql = "SELECT ugml_naval_formation.*,
	    			GROUP_CONCAT(
	    				CONCAT(ugml_naval_formation_to_users.userID, ',', ugml_naval_formation_to_users.joinTime)
	    				SEPARATOR ';')
	    			AS users,
	    			GROUP_CONCAT(
	    				CONCAT(ugml_fleet.ownerID, ',', ugml_fleet.fleetID)
	    				SEPARATOR ';')
	    			AS fleets
				FROM ugml_naval_formation
				LEFT JOIN ugml_naval_formation_to_users
					ON ugml_naval_formation.formationID = ugml_naval_formation_to_users.formationID
				LEFT JOIN ugml_fleet
					ON ugml_naval_formation.formationID = ugml_fleet.formationID
				WHERE ugml_fleet.formationID IN (".$sub.")
				GROUP BY ugml_naval_formation.formationID";	    
		$result = WCF::getDB()->sendQuery($sql);
	    
	    while($row = WCF::getDB()->fetchArray($result)) {
	    	$navalFormations[$row['formationID']] = new NavalFormation(null, $row);	    		  
	    }

	    return $navalFormations;
	}
	
	/**
	 * Returns the leader fleet.
	 * 
	 * @return	Fleet
	 */
	public function getLeaderFleet() {
		return Fleet::getInstance($this->leaderFleetID);
	}
	
	/**
	 * Checks whether new users may be added or not.
	 * 
	 * @return	bool
	 */
	public function usersLimitReached()
	{
		return (count($this->users) >= self::MAX_USERS);
	}
	
	/**
	 * Checks user if they have fleets in the formation.
	 * 
	 * @param	mixed	user ids as array or string
	 * @return	bool	deletable
	 */
	public function checkUsers($userIDsStr) {		
		// get
		if(!count($this->usersWithFleets)) {
			foreach($this->fleets as $fleetID => $fleet) {
				$ownerID = $fleet->ownerID;
				
				if(!isset($this->usersWithFleets[$ownerID])) {
					$this->usersWithFleets[$ownerID] = 1;
				}
				else {
					++$this->usersWithFleets[$ownerID];
				}
			}
		}
		
		// check
		if(!is_array($userIDsStr)) {
			$userIDsArr = explode(',', $userIDsStr);
		}
		else {
			$userIDsArr = $userIDsStr;
		}
		
		foreach($userIDsArr as $userID) {
			if(isset($this->usersWithFleets[$userID])) {
				return false;
			}
		}
		
		return true;
	}
		
	/**
	 * Returns the target planet.
	 * 
	 * @return	Planet
	 */
	public function getTargetPlanet() {
		return Planet::getInstance($this->endPlanetID);
	}
	
	/**
	 * Returns the editor for this formation.
	 * 
	 * @return	NavalFormationEditor
	 */
	public function getEditor() {
		if($this->editor === null) {
			$this->editor = new NavalFormationEditor($this);
		}
		return $this->editor;
	}
	
	/**
	 * If this was the last fleet, cancel the hole naval formation
	 *  or search a new leader fleet if the cancel was this.
	 *  
	 * @param	int		fleet id
	 * @param	int		new leader fleet id
	 */
	public function cancelFleet($fleetID) {
		Fleet::getInstance($fleetID)->getEditor()->update('formationID', 'NULL');
		
		unset($this->fleets[$fleetID]);
		
		if($fleetID != $this->leaderFleetID) {
			return Fleet::getInstance($this->leaderFleetID);
		}
		
		if(!count($this->fleets)) {
			$this->getEditor()->delete();
			return null;
		}
		else {
			$sql = "SELECT fleetID
					FROM ugml_fleet
					WHERE formationID = ".$this->formationID."
					ORDER BY fleetID ASC";
			$row = WCF::getDB()->getFirstRow($sql);
			
			$leaderFleetID = $row['fleetID'];
			$this->getEditor()->setLeaderFleetID($leaderFleetID);
		}
		
		return Fleet::getInstance($leaderFleetID);
	}
}
?>