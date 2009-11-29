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

require_once(LW_DIR.'lib/data/fleet/AbstractFleetEventHandler.class.php');
require_once(LW_DIR.'lib/data/fleet/mission/Mission.class.php');
require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');

/**
 * Handles all actions of stand-by-fleets
 *
 * in execute() function we must switch in to parts:
 * 1) start of the stand by (sets start_time in the db to end of stand by; fleet mess to 2)
 * 2) end of the stand by (as a normal execute funcion)
 */
class StandByFleet extends AbstractFleetEventHandler implements Mission {
	protected $missionID = 12;
	
	public static $availableTimes = array(0, 3600, 7200, 14400, 28800, 57600, 115200);
	
	/**
	 * Does nothing.
	 */
    public function executeImpact() {
    	return;
    }

	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		$alliance = Alliance::getByUserID($fleetQueue->getTargetPlanet()->id_owner);
		
		$foreignPlanet = ($fleetQueue->getTargetPlanet()->id_owner != WCF::getUser()->userID);
		$isBuddy = WCF::getUser()->hasBuddy($fleetQueue->getTargetPlanet()->id_owner);
		$isInAlliance = ($alliance !== null && LWCore::getAlliance() !== null && $alliance->allianceID == LWCore::getAlliance()->allianceID);
		
		if($fleetQueue->pageNo == 2) {
			WCF::getTPL()->assign('availableTimes', self::$availableTimes);
		}
		if($fleetQueue->pageNo == 3) {
			$selectedTime = intval(@$_REQUEST['standByTime']);
			
			if(!in_array($selectedTime, self::$availableTimes)) {
				return false;
			}
		}
		
		if($foreignPlanet && ($isBuddy || $isInAlliance)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	AbstractFleetEventHandler::getImpactOwnerMessageData()
	 */
	public function getImpactOwnerMessageData() {
		return null;
	}
	
	/**
	 * @see	AbstractFleetEventHandler::getImpactOfiaraMessageData()
	 */
	public function getImpactOfiaraMessageData() {
		return null;
	}
	
	/**
	 * @see Fleet::getCancelDuration()
	 */
	public function getCancelDuration() {
		if(isset($this->data['displayTime']) && $this->displayTime == $this->returnTime) {
			return false;
		}	
		$parent = parent::getCancelDuration();
		
		if($parent !== false) {
			return $parent;
		}
		if($this->wakeUpTime < microtime(true) || $this->impactTime == 0) {
			return false;
		}
		
		return ($this->impactTime - $this->startTime);
	}
	
	/**
	 * @see Fleet::getFleetSet()
	 */
	public function getFleetSet($planetID = false) {
		$fleetArray = parent::getFleetSet($planetID);
		
		if($this->impactTime < time() && isset($fleetArray[$this->impactTime.$this->fleetID])) {
			unset($fleetArray[$this->impactTime.$this->fleetID]);
		}
		if($this->wakeUpTime >= time() && (!$planetID || $planetID == $this->targetPlanetID)) {
			$fleet = clone $this;
			$fleet->displayTime = $this->wakeUpTime;
			
			$fleetArray[$this->wakeUpTime.$this->fleetID] = $fleet;
		}
		return $fleetArray;
	}
}
?>