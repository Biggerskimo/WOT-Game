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
require_once(LW_DIR.'lib/data/ovent/FleetOventFleet.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOverview.class.php');
require_once(LW_DIR.'lib/data/ovent/OventEditor.class.php');

/**
 * This class shows a fleet overview event.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds
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
		
		if(!in_array($this->fleetID, self::$registeredFleetIDs)) {
			self::$fleetOverview->add($this->missionID, $this->resources['metal'], $this->resources['crystal'], $this->resources['deuterium']);
		
			self::$registeredFleetIDs[] = $this->fleetID;
		}
	}
	
	/**
	 * Creates the ovents for a fleet.
	 * 
	 * @param	Fleet	fleet
	 * @param	bool	delete old ovents (= update)
	 * @param	bool	wrap in transaction
	 */
	public static function create($fleet, $deleteOld = true, $transact = false) {
		if($transact) {
			WCF::getDB()->sendQuery("SET AUTOCOMMIT = 0");
			WCF::getDB()->sendQuery("START TRANSACTION");
		}			
		if($deleteOld) {
			$sql = "DELETE FROM ugml_ovent
					WHERE oventTypeID = ".self::OVENT_TYPE_ID."
						AND relationalID = ".$fleet->fleetID;
		}
		$data = array('ownerID' => $fleet->ownerID, 'ofiaraID' => $fleet->ofiaraID, 'startPlanetID' => $fleet->startPlanetID,
			'targetPlanetID' => $fleet->targetPlanetID, 'resources' => array('metal' => $fleet->metal, 'crystal' => $fleet->crystal, 'deuterium' => $fleet->deuterium),
			'startCoords' => array($fleet->getStartPlanet()->galaxy, $fleet->getStartPlanet()->system, $fleet->getStartPlanet()->planet, $fleet->getStartPlanet()->planetKind),
			'targetCoords' => array($fleet->getTargetPlanet()->galaxy, $fleet->getTargetPlanet()->system, $fleet->getTargetPlanet()->planet, $fleet->getTargetPlanet()->planetKind),
			'spec' => $fleet->fleet, 'cssClass' => $fleet->getClassName(true), 'missionID' => $fleet->missionID,
			'startPlanetName' => $fleet->getStartPlanet()->name, 'targetPlanetName' => $fleet->getTargetPlanet()->name, 'fleetID' => $fleet->fleetID
		);		
		$ownerFields = array('userID' => $fleet->ownerID, 'planetID' => $fleet->startPlanetID);
		$ofiaraFields = array('userID' => $fleet->ofiaraID, 'planetID' => $fleet->targetPlanetID);
		
		$data['passage'] = 'flight';
		OventEditor::create(self::OVENT_TYPE_ID, $fleet->impactTime, $fleet->impactEventID, $fleet->fleetID, $ownerFields, 0, $data);
		
		$data['passage'] = 'return';			
		OventEditor::create(self::OVENT_TYPE_ID, $fleet->returnTime, $fleet->returnEventID, $fleet->fleetID, $ownerFields, 0, $data);
	
		if($ownerID != $ofiaraID && $ofiaraID > 0) {
			$data['cssClass'] = $fleet->getClassName(false);
			$data['passage'] = 'flight';
			OventEditor::create(self::OVENT_TYPE_ID, $fleet->impactTime, $fleet->impactEventID, $fleet->fleetID, $ofiaraFields, 0, $data);
		}
		
		// TODO: integrate this in wcf eventhandler
		if($fleet->missionID == 11) {
			// what NAO ?!
		}
		else if($fleet->missionID == 12) {
			$data['cssClass'] = $fleet->getClassName(true);
			$data['passage'] = 'standBy';
			
			OventEditor::create(self::OVENT_TYPE_ID, $fleet->wakeUpTime, $fleet->wakeUpEventID, $fleet->fleetID, $ownerFields, 0, $data);
		
			$data['cssClass'] = $fleet->getClassName(false);
			
			OventEditor::create(self::OVENT_TYPE_ID, $fleet->wakeUpTime, $fleet->wakeUpEventID, $fleet->fleetID, $ofiaraFields, 0, $data);
		}
		
		if($transact) {			
			WCF::getDB()->sendQuery("COMMIT");
			WCF::getDB()->sendQuery("SET AUTOCOMMIT = 1");
		}
	}
	
	
	/**
	 * @see Ovent::getTemplateName()
	 */
	public function getTemplateName() {		
		return "oventFleet";
	}
}
?>