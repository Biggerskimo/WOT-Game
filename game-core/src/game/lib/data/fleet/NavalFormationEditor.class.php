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

require_once(LW_DIR.'lib/data/AbstractDecorator.class.php');
require_once(LW_DIR.'lib/data/fleet/NavalFormation.class.php');

/**
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class NavalFormationEditor extends AbstractDecorator {
	protected $formationObj = 0;
	
	/**
	 * Creates a new NavalFormationEditor object.
	 * 
	 * @param	NavalFormation
	 */
	public function __construct(NavalFormation $formationObj) {
		$this->formationObj = $formationObj;
	}
	
	
	/**
	 * @see AbstractDecorator::getObject()
	 */
	protected function getObject() {
		return $this->formationObj;
	}
	
	/**
	 * Creates a new naval formation.
	 * 
	 * @param	int		leader fleet
	 * @return	NavalFormation
	 */
	public static function create($leaderFleetID, $userID) {
		// create
		$formationName = WCF::getLanguage()->get('wot.fleet.navalFormation.name').' '.substr(base_convert(StringUtil::getRandomID(), 16, 10), 0, 5);
		
		$sql = "INSERT INTO ugml_naval_formation
				(formationName, leaderFleetID,
				 endPlanetID, impactTime)
				VALUES
				('".$formationName."', ".$leaderFleetID.",
				 (SELECT targetPlanetID
				  FROM ugml_fleet
				  WHERE fleetID = ".$leaderFleetID."), (SELECT impactTime
													     FROM ugml_fleet
													     WHERE fleetID = ".$leaderFleetID."))";
		WCF::getDB()->sendQuery($sql);
		$formationID = WCF::getDB()->getInsertID();
		
		$navalFormation = new NavalFormation($formationID);
		$navalFormation->getEditor()->addUser($userID, null, false);
		$navalFormation->getEditor()->addFleet($leaderFleetID);
		
		return $navalFormation;
	}
	
	/**
	 * Deletes this naval formation.
	 */
	public function delete() {
		// fleets
		$fleetsArray = $this->getFleets();
		$fleetIDsStr = '';
		
		foreach($fleetsArray as $fleetID => $fleetObj) {
			if(!empty($fleetIDsStr)) $fleetIDsStr .= ','.$fleetID;
			else $fleetIDsStr = $fleetID;
		}
		
		if(!empty($fleetIDsStr)) $this->deleteFleets($fleetIDsStr);
		
		// user
		$usersArray = $this->getUsers();
		$userIDsStr = '';
		
		foreach($usersArray as $userID => $username) {
			if(!empty($userIDsStr)) $userIDsStr .= ','.$userID;
			else $userIDsStr = $userID;
		}
		if(!empty($userIDsStr)) $this->deleteUsers($userIDsStr);
		
		// naval formation
		$sql = "DELETE FROM ugml_naval_formation
				WHERE formationID = ".$this->formationID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
 	 * Adds a fleet to the naval formation.
 	 * 
 	 * @param	int		fleet id
 	 */
	public function addFleet($fleetID) {
		if(NavalFormation::getByFleetID($fleetID) !== null) {
			return;
		}
		
		$fleet = Fleet::getInstance($fleetID);
		
		// impact calculation
		$currentImpactDiff = $this->impactTime - microtime(true);
		$correctureDiff = $fleet->impactTime - microtime(true);
		$duration = $fleet->returnTime - $this->impactTime;
		
		// correct impact time of formation
		if($correctureDiff > $currentImpactDiff) {
			$this->impactTime = $fleet->impactTime;
			
			foreach($this->fleets as $ifleetID => $ifleet) {				
				$addition = $this->impactTime - $ifleet->impactTime;
				
				$ifleet->getEditor()->changeTime($addition);
			}
			
			$sql = "UPDATE ugml_naval_formation
					SET ugml_naval_formation.impactTime = ".$this->impactTime."
					WHERE ugml_naval_formation.formationID = ".$this->formationID;
			WCF::getDB()->sendQuery($sql);
		}
		// correct time of fleet
		else {
			$addition = $this->impactTime - $fleet->impactTime;
			
			$fleet->getEditor()->changeTime($addition);
		}
		
		$fleet->getEditor()->update(array('missionID' => 11, 'formationID' => $this->formationID));
	}
	
	/**
	 * Sets this fleet as leader fleet.
	 * 
	 * @param	int		fleet id
	 */
	public function setLeaderFleetID($fleetID) {
		$sql = "UPDATE ugml_naval_formation
				SET leaderFleetID = ".$fleetID."
				WHERE formationID = ".$this->formationID;
		WCF::getDB()->sendQuery($sql);
		
		$this->leaderFleetID = $fleetID;
	}
	
	/**
	 * Delete a fleets from the naval formation.
	 *
	 * @param	str		fleet ids
	 */
	public function deleteFleets($fleetIDsStr) {
		$sql = "DELETE FROM ugml_naval_formation_to_fleets
				WHERE fleetID IN(".$fleetIDsStr.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes users from the naval formation.
	 * 
	 * @param	str		user ids
	 */
	public function deleteUsers($userIDsStr) {
		if(!$this->checkUsers($userIDsStr)) {
			require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
			throw new SystemException('can not delete users '.$userIDsStr.' in naval formation #'.$this->formationID);
		}
		
		// delete
		$sql = "DELETE FROM ugml_naval_formation_to_users
				WHERE userID IN(".$userIDsStr.")
					AND formationID = ".$this->formationID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Adds a user to the naval formation.
	 * 
	 * @param	int		user id
	 * @param	LWUser	inviter
	 */
	public function addUser($userID, $inviter = null, $sendMessage = true) {	
		// add
		$sql = "INSERT IGNORE INTO ugml_naval_formation_to_users
				(formationID, userID, joinTime)
				VALUES
				(".$this->formationID.", ".$userID.", ".time().")";
		WCF::getDB()->sendQuery($sql);
		
		// send message
		if($sendMessage) {
			if($inviter === null) {
				$inviter = WCF::getUser();
			}
			
			$subject = WCF::getLanguage()->get('wot.fleet.navalFormation.invite.message.subject');
			$sender = WCF::getLanguage()->get('wot.fleet.navalFormation.invite.message.sender');
			$text = WCF::getLanguage()->get('wot.fleet.navalFormation.invite.message.text', array('$formationName' => $this->formationName, '$impactTime' => DateUtil::formatTime(null, $this->impactTime), '$inviter' => $inviter, '$planet' => $this->getTargetPlanet()));
			
			require_once(LW_DIR.'lib/data/message/MessageEditor.class.php');
			MessageEditor::create($userID, $subject, $text, 0, $sender, 0);
		}
	}
	
	/**
	 * Changes the name of the naval formation.
	 * 
	 * @param	string	name
	 */
	public function setName($name) {
		$this->formationName = $name;
	
		$sql = "UPDATE ugml_naval_formation
				SET formationName = '".escapeString($name)."'
				WHERE formationID = ".$this->formationID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>