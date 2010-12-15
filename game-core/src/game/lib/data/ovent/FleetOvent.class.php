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

require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOventEditor.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOverview.class.php');

/**
 * This class shows a fleet overview event.
 * 
 * @author		Biggerskimo
 * @copyright	2009 - 2010 Lost Worlds <http://lost-worlds.net>
 */
class FleetOvent extends Ovent {
	private static $registeredFleetIDs = array();
	private static $fleetOverview = null;
	
	const OVENT_TYPE_ID = 1;
	
	public function __construct($oventID, $row = null) {
		parent::__construct($oventID, $row);
		
		if(self::$fleetOverview === null) {
			self::$fleetOverview = new FleetOverview();
		}
		
		$resources = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);
		$this->extractPool();
		
		$add = false;
		foreach($this->poolData as $fleetData) {
			// TODO: modularize mission id
			if(!in_array($fleetData['fleetID'], self::$registeredFleetIDs)
					&& ($fleetData['ownerID'] == WCF::getUser()->userID || $fleetData['missionID'] == 3)) {
				$resources['metal'] += $fleetData['resources']['metal'];
				$resources['crystal'] += $fleetData['resources']['crystal'];
				$resources['deuterium'] += $fleetData['resources']['deuterium'];
				
				self::$registeredFleetIDs[] = $fleetData['fleetID'];
				
				$add = true;
			}
		}
		
		if($add)
			self::$fleetOverview->add($this->poolData[0]['missionID'], $resources['metal'], $resources['crystal'], $resources['deuterium']);
	}
	
	/**
	 * Creates the ovents for a fleet.
	 * 
	 * @param	Fleet	fleet
	 * @param	bool	delete old ovents (= update)
	 * @param	bool	wrap in transaction
	 * @todo move this to FleetOventEditor
	 */
	public static function create($fleet, $deleteOld = true, $transact = false, $forceNfsUpdate = false) {
		if($transact) {
			WCF::getDB()->sendQuery("SET AUTOCOMMIT = 0");
			WCF::getDB()->sendQuery("START TRANSACTION");
		}			
		if($deleteOld) {
			$sql = "DELETE FROM ugml_ovent
					WHERE oventTypeID = ".self::OVENT_TYPE_ID."
						AND relationalID = ".$fleet->fleetID;
		}
		$data = self::getData($fleet);
		$ownerFields = array('userID' => $fleet->ownerID, 'planetID' => $fleet->startPlanetID);
		$ofiaraFields = array('userID' => $fleet->ofiaraID, 'planetID' => $fleet->targetPlanetID);
		
		$data['passage'] = 'flight';
		$impactOwnerOvent = OventEditor::create(self::OVENT_TYPE_ID, $fleet->impactTime, $fleet->impactEventID, $fleet->fleetID, $ownerFields, 0, array($data));
		
		$data['passage'] = 'return';
		$returnOwnerOvent = OventEditor::create(self::OVENT_TYPE_ID, $fleet->returnTime, $fleet->returnEventID, $fleet->fleetID, $ownerFields, 0, array($data));
		
		if($fleet->ownerID != $fleet->ofiaraID && $fleet->ofiaraID > 0) {
			$data['passage'] = 'flight';
			$impactOfiaraOvent = OventEditor::create(self::OVENT_TYPE_ID, $fleet->impactTime, $fleet->impactEventID, $fleet->fleetID, $ofiaraFields, 0, array($data));
			
			if(stripos(get_class($fleet), "attack") !== false || stripos(get_class($fleet), "destroy") !== false) {
				$impactOfiaraOvent->setHighlighted(true);
			}
		}
		
		// TODO: integrate this in wcf eventhandler
		if($fleet->missionID == 11) {
			$formation = $fleet->getNavalFormation();
			$fleets = $formation->fleets;
			$leaderFleet = $formation->getLeaderFleet();
			$leaderFleetID = $leaderFleet->fleetID;
			
			if(count($fleets) > 1 || $forceNfsUpdate) {
				$impactOwnerOvent->getEditor()->delete();
				$impactOfiaraOvent->getEditor()->delete();
				
				$ovents = Ovent::getByConditions(array('relationalID' => $leaderFleetID, 'oventTypeID' => self::OVENT_TYPE_ID));
				foreach($ovents as $ovent) {
					$oventData = $ovent->getPoolData();
					if($oventData[0]['passage'] == 'flight') {
						$ovent->getEditor()->delete();
					}
				}
				
				$data['passage'] = 'flight';
				
				$odata = array($data);
				
				foreach($fleets as $fleetID => $fleetObj) {
					if($fleetID != $fleet->fleetID) {
						$odata[] = self::getData($fleetObj, array('passage' => 'flight'));
					}
				}
				
				$impactOwnerOvent = OventEditor::create(self::OVENT_TYPE_ID, $fleet->impactTime, $leaderFleet->impactEventID, $leaderFleetID, $ownerFields, 0, $odata);
				
				foreach($formation->users as $userID => $user) {
					if($userID != $fleet->ownerID) {
						$ownerFields['userID'] = $userID;
						OventEditor::create(self::OVENT_TYPE_ID, $fleet->impactTime, $leaderFleet->impactEventID, $leaderFleetID, $ownerFields, 0, $odata);
					}
				}
				$ownerFields['userID'] = $fleet->ownerID;
				
				$impactOfiaraOvent = OventEditor::create(self::OVENT_TYPE_ID, $fleet->impactTime, $leaderFleet->impactEventID, $leaderFleetID, $ofiaraFields, 0, $odata);
				$impactOfiaraOvent->setHighlighted(true);
			}
		}
		else if($fleet->missionID == 12) {
			$data['passage'] = 'restart';
			
			$standByOwnerOvent = OventEditor::create(self::OVENT_TYPE_ID, $fleet->wakeUpTime, $fleet->wakeUpEventID, $fleet->fleetID, $ownerFields, 0, array($data));
			$standByOfiaraOvent = OventEditor::create(self::OVENT_TYPE_ID, $fleet->wakeUpTime, $fleet->wakeUpEventID, $fleet->fleetID, $ofiaraFields, 0, array($data));
		}
		
		if($transact) {
			WCF::getDB()->sendQuery("COMMIT");
			WCF::getDB()->sendQuery("SET AUTOCOMMIT = 1");
		}
	}
	
	/**
	 * Updates the fleet data of the return events.
	 */
	public static function update(Fleet $fleet) {
		$ovents = Ovent::getByConditions(array('oventTypeID' => self::OVENT_TYPE_ID, 'relationalID' => $fleet->fleetID), true);
		
		foreach($ovents as $ovent) {
			$ovent->getEditor()->update();
		}
	}
	
	/**
	 * Builds the data object of a fleet.
	 *
	 * @param	Fleet	fleet
	 * @param	array	additional fields
	 */
	protected static function getData($fleet, $additional = array()) {
		$data = array('ownerID' => $fleet->ownerID, 'ofiaraID' => $fleet->ofiaraID, 'startPlanetID' => $fleet->startPlanetID,
			'targetPlanetID' => $fleet->targetPlanetID, 'resources' => array('metal' => $fleet->metal, 'crystal' => $fleet->crystal, 'deuterium' => $fleet->deuterium),
			'startCoords' => array($fleet->getStartPlanet()->galaxy, $fleet->getStartPlanet()->system, $fleet->getStartPlanet()->planet, $fleet->getStartPlanet()->planetKind),
			'targetCoords' => array($fleet->galaxy, $fleet->system, $fleet->planet, $fleet->getTargetPlanet()->planetKind),
			'spec' => $fleet->fleet, 'cssClass' => self::getCssClassName($fleet), 'missionID' => $fleet->missionID,
			'startPlanetName' => $fleet->getStartPlanet()->name, 'targetPlanetName' => $fleet->getTargetPlanet()->name, 'fleetID' => $fleet->fleetID
		);
		$data += $additional;
		
		return $data;
	}
	
	/**
	 * Returns the css class for a fleet.
	 *
	 * @return	string	css class
	 */
	protected static function getCssClassName($fleet) {
		$phpClass = get_class($fleet);
		$cssClass = str_replace("Fleet", "", $phpClass);
		$cssClass[0] = strtolower($cssClass[0]);
		
		return $cssClass;
	}
	
	/**
	 * @see Ovent::getEditor()
	 *
	 * @return	FleetOventEditor
	 */
	public function getEditor() {
		return new FleetOventEditor($this);
	}
	
	/**
	 * @see Ovent::getTemplateName()
	 */
	public function getTemplateName() {
		return "oventFleet";
	}
}
?>