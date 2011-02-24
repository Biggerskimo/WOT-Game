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
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
require_once(LW_DIR.'lib/data/fleet/FleetEditor.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOvent.class.php');
require_once(LW_DIR.'lib/util/LockUtil.class.php');

/**
 * Provides funtions for checking the start of a fleet.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class FleetQueue extends DatabaseObject {
	public static $cache = array();
	
	protected $missionsCache = array();
	
	public $pageNo;
	
	protected $newFleetQueue = array();
	
	protected $planetGalaxy = 0, $planetSystem = 0, $planetPlanet = 0, $planetPlanetType = 0, $planetPlanetID = 0;
	
	public $ships = array();
	protected $origShips = array();
	
	public $fleetEditor = null;
	
	public $formationID = 0;
	
	/**
	 * Creates a new FleetStartForm object.
	 */
	public function __construct($pageNo) {
		$this->pageNo = $pageNo;
	
		// create
		if($pageNo == 0) {
			$this->deleteFleetQueue();
			
			$sql = "INSERT INTO ugml_fleet_queue
					(time, userID, percent, state)
					VALUES
					(".WCF::getUser()->onlinetime.", ".WCF::getUser()->userID.", 1, ".$this->pageNo.")";
			WCF::getDB()->sendQuery($sql);
		}
		
		// load
		$sql = "SELECT ugml_fleet_queue.*,
					GROUP_CONCAT(
						CONCAT(specID, ',', shipCount) 
						SEPARATOR ';')
					AS fleet
				FROM ugml_fleet_queue
				LEFT JOIN ugml_fleet_queue_fleet ON ugml_fleet_queue.fleetQueueID = ugml_fleet_queue_fleet.fleetQueueID
				WHERE userID = ".WCF::getUser()->userID."
				GROUP BY fleetQueueID";
		$row = WCF::getDB()->getFirstRow($sql);
		
		parent::__construct($row);
		
		// check page
		if($this->state && $this->state + 1 != $pageNo) {
			$this->deleteFleetQueue();
			
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.error'));
		}
		
		$this->loadShips();
	}
	
	/**
	 * Saves the fleet queue
	 */
	public function __destruct() {
		$shipStr = '';
		
		$this->saveShips();
		$this->saveFleetQueue();
	}
	
	/**
	 * Saves fleet queue data.
	 * 
	 * @param	string		name
	 * @param	mixed		value
	 */
	public function __set($name, $value) {
		// update actualized fleet queue, if value found in old fleet queue
		if(isset($this->data[$name])) $this->newFleetQueue[$name] = $this->data[$name] = $value;
		// ignore
		return;
	}
	
	/**
	 * Updates the fleet queue.
	 */
	protected function saveFleetQueue() {
		$setters = '';
		foreach($this->newFleetQueue as $key => $value) {
			$setters .= $key." = '".$value."', ";
		}
	
		$sql = "UPDATE ugml_fleet_queue
				SET ".$setters."
					state = ".$this->pageNo.",
					`time` = ".time()."
				WHERE fleetQueueID = ".$this->fleetQueueID;
		WCF::getDB()->sendQuery($sql);
		
		Session::resetSessions(WCF::getUser()->userID);
	}
	
	/**
	 * Deletes the actual fleet queue.
	 */
	protected function deleteFleetQueue() {
		$sql = "DELETE FROM ugml_fleet_queue
				WHERE userID = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM ugml_fleet_queue_fleet
				WHERE fleetQueueID = ".intval($this->fleetQueueID);
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Reads the missions cache.
	 */
	public static function readCache() {
		if(!count(self::$cache)) {
			WCF::getCache()->addResource('missions-'.PACKAGE_ID, WCF_DIR.'cache/cache.missions-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderMissions.class.php');
			self::$cache = WCF::getCache()->get('missions-'.PACKAGE_ID);
		}
	}
	
	/**
	 * Inserts the fleet
	 */
	public function fire() {
		LockUtil::checkLock(WCF::getUser()->userID);
		LockUtil::setLock(WCF::getUser()->userID, 10);
		
		EventHandler::fireAction($this, 'shouldFire');
		
		$this->fleetEditor = FleetEditor::create($this->startPlanetID, $this->endPlanetID, $this->ships, $this->galaxy, $this->system, $this->planet, $this->metal, $this->crystal, $this->deuterium, $this->getDuration(), $this->missionID);
		
		$planet = Planet::getInstance($this->startPlanetID);
		$planet->getEditor()->changeResources(-$this->metal, -$this->crystal, -($this->deuterium + $this->getConsumption()));
		
		$ships = array();
		foreach($this->ships as $specID => $shipCount) {
			$ships[$specID] = -$shipCount;
		}
		$planet->getEditor()->changeLevel($ships);
		
		// TODO: integrate in wcf eventlistener didFire@FleetQueue
		if($this->missionID == 11) {
			$formation = new NavalFormation($this->formationID);
			$formation->getEditor()->addFleet($this->fleetEditor->fleetID);
		}
		if($this->missionID == 12) {
			$standByTime = intval(@$_REQUEST['standByTime']);
			
			$wakeUpTime = $this->fleetEditor->impactTime + $standByTime;
			$newReturnTime = $this->fleetEditor->returnTime + $standByTime;
			
			$this->fleetEditor->changeTime(array('return' => $newReturnTime));
			
			$wakeUpEvent = WOTEventEditor::create(1, $this->fleetEditor->fleetID, array('state' => 2), $wakeUpTime);
		
			$this->fleetEditor->update(array('wakeUpEventID' => $wakeUpEvent->eventID, 'wakeUpTime' => $wakeUpTime));
		}
		
		//if(WCF::getUser()->userID ==1)
		$fleetObj = Fleet::getInstance($this->fleetEditor->fleetID);
		FleetOvent::create($fleetObj, false, true);
		
		EventHandler::fireAction($this, 'didFire');
		
		$this->deleteFleetQueue();
		
		LockUtil::removeLock(WCF::getUser()->userID);
	}
	
	/**
	 * Validates all values.
	 */
	public function validate() {
		// TODO: dont use game_config here
		global $game_config;
		
		EventHandler::fireAction($this, 'validate');
		
		// TODO: integrate in wcf eventlistener validate@FleetQueue
		if($this->missionID == 11) {
			require_once(LW_DIR.'lib/data/fleet/NavalFormation.class.php');
			$formation = new NavalFormation($this->formationID);
			$currentImpactDiff = $formation->impactTime - microtime(true);
			if($this->getDuration() > $currentImpactDiff * (100 + NavalFormation::MAX_TIME_MOVEMENT) / 100) {			
				require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
				throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.navalFormation.tooLate'));
			}
		}
		
		if(!count($this->ships)) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.noShips'));
		}
		
		if(WCF::getUser()->urlaubs_modus) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.global.vacation.error'));
		}
		
		if($this->galaxy > GALAXIES || $this->galaxy < 1 || $this->system > 499 || $this->system < 1 || $this->planet > 15 || $this->planet < 1) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.illegalPlanet'));
		}
		
		$availableMissions = $this->getMissions();
		if(!isset($availableMissions[$this->missionID])) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.mission.notAvailable'));
		}
		
		if($this->getTargetPlanet()->planetID) {
			$selectedMission = $availableMissions[$this->missionID];
			
			if($game_config['noobProtection'] && $selectedMission['noobProtection'] && WCF::getUser()->points > $this->getTargetPlanet()->noobProtectionLimit() && $this->getTargetPlanet()->getOwner()->onlinetime > (TIME_NOW - 60 * 60 * 24 * 7)) {
				require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
				throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.noobProtection'));
			}
			
			if($this->getTargetPlanet()->getOwner()->authlevel && !WCF::getUser()->authlevel && !($this->getTargetPlanet()->getOwner()->authlevel == 4 && $this->missionID == 3)) {
				require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
				throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.protectedOfiara'));
			}
			
			if($this->getTargetPlanet()->planetID == LWCore::getPlanet()->planetID) {
				require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
				throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.samePlanet'));
			}
			
			if($this->getTargetPlanet()->id_owner)
			{
				$sql = "SELECT urlaubs_modus
						FROM ugml_users
						WHERE id = ".$this->getTargetPlanet()->id_owner;
				$row = WCF::getDB()->getFirstRow($sql);
			}
			
			if($row['urlaubs_modus'] && $this->getTargetPlanet()->planetKind != 2) {
				require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
				throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.vacationError'));
			}
		}
		
		if($this->getConsumption() > LWCore::getPlanet()->deuterium) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.tooMuchResources'));				
		}
		
		if($this->metal > LWCore::getPlanet()->metal || $this->crystal > LWCore::getPlanet()->crystal || $this->deuterium > LWCore::getPlanet()->deuterium - $this->getConsumption()) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.tooMuchResources'));			
		}
		
		if($this->metal + $this->crystal + $this->deuterium + $this->getConsumption() > $this->getCapacity()) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.capacityError'));			
		}
		
		$fleets = Fleet::getByUserID(WCF::getUser()->userID);
		
		if(WCF::getUser()->computer_tech < count($fleets)) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.start.insufficientSlots'));			
		}
	}
	
	/**
	 * Stores the planet object in the fleet queue.
	 * 
	 * @param	Planet
	 */
	public function storePlanet($planet) {
		if($planet instanceof Planet) {
			$this->endPlanetID = $planet->planetID;
			
			$this->galaxy = $planet->galaxy;
			$this->system = $planet->system;
			$this->planet = $planet->planet;
			$this->planetType = $planet->planetKind;		
		}
		else
			$this->endPlanetID = 0;
	}

	/**
	 * Loads the ships.
	 */
	protected function loadShips() {
		$parts = explode(';', $this->fleet);
		$this->ships = array();
		
		foreach($parts as $part) {
			list($specID, $shipCount) = explode(',', $part);
			
			$this->ships[$specID] = $shipCount;
		}
		
		$this->origShips = $this->ships;		
	}
	
	/**
	 * Saves the ships.
	 */
	protected function saveShips() {
		$changedShips = array_diff_assoc($this->ships, $this->origShips);
		
		$inserts = "";
		foreach($changedShips as $specID => $shipCount) {
			if(!$shipCount) {
				$sql = "DELETE FROM ugml_fleet_queue_fleet
						WHERE fleetQueueID = ".$this->fleetQueueID."
							AND specID = ".$specID;
				WCF::getDB()->sendQuery($sql);
			}
			else {
				if(!empty($inserts)) {
					$inserts .= ", ";
				}
				
				$inserts .= "(".$this->fleetQueueID.", ".$specID.", ".$shipCount.")";
			}
		}
		
		if(!empty($inserts)) {
			$sql = "REPLACE INTO ugml_fleet_queue_fleet
					 (fleetQueueID, specID, shipCount)
					VALUES
					 ".$inserts;
			WCF::getDB()->sendQuery($sql);			
		}
		
		$this->ships = serialize($this->ships);
	}
	
	/**
	 * Calculates the max speed of the fleet.
	 * 
	 * @return	int		speed
	 */
	public function getSpeed() {
		$user = WCF::getUser();
		$speeds = array();
		foreach($this->ships as $specID => $shipCount) {
			$speeds[] = Spec::getSpecObj($specID)->getSpeed();
		}
		
		arsort($speeds, SORT_NUMERIC);
				
		return array_pop($speeds);
	}
	
	/**
	 * Calculates the capacity of the ships.
	 * 
	 * @return	int		capacity
	 */
	public function getCapacity() {
		$capacity = 0;
		
		foreach($this->ships as $specID => $shipCount) {
			$capacity += $shipCount * Spec::getSpecVar($specID, 'capacity');
		}
		
		return $capacity;
	}
	
	/**
	 * Calculates the distance for the flight.
	 * 
	 * @return	int		distance
	 */
	public function getDistance() {
		// galaxy
		if($this->galaxy != LWCore::getPlanet()->galaxy) {
			return abs($this->galaxy - LWCore::getPlanet()->galaxy) * 20000;
		}
		
		// system
		if($this->system != LWCore::getPlanet()->system) {
			return abs($this->system - LWCore::getPlanet()->system) * 95 + 2700;
		}
		
		// planet
		if($this->planet != LWCore::getPlanet()->planet) {
			return abs($this->planet - LWCore::getPlanet()->planet) * 5 + 1000;
		}
		
		// planet type
		return 5;
	}
	
	/**
	 * Calculates the duration for the flight.
	 * 
	 * @return	int		duration
	 */
	public function getDuration() {
		global $game_config;
	
		$speedPercent = $this->speedPercent;
		$speedFactor = $game_config['fleet_speed'] / 2500;
		$distance = $this->getDistance();
		$speed = $this->getSpeed();

		$duration = round((3500 / $speedPercent * sqrt($distance * 10 / $speed) + 10) / $speedFactor);

		return $duration;
	}
	
	/**
	 * Calculates the consumption for the flight.
	 * 
	 * @return	int		consumption
	 */
	public function getConsumption() {
		global $game_config;
		
		$consumption = 0;
		$distance = $this->getDistance();
		$duration = $this->getDuration();
		$speedFactor = $game_config['fleet_speed'] / 2500;
		$speedPercent = $this->speedPercent;
		
		foreach($this->ships as $specID => $shipCount) {
			$specObj = Spec::getSpecObj($specID);
					
			$specSpeed = $specObj->getSpeed();
			$specConsumption = $specObj->getConsumption();
			
			$spd = 35000 / ($duration * $speedFactor - 10) * sqrt($distance * 10 / $specSpeed);
		
			$consumption += $shipCount * $specConsumption * $distance / 35000 * pow(($spd / 10 + 1), 2);
		}
				
		return round($consumption) + 1;
	}
	
	/**
	 * Returns the available missions.
	 * 
	 * @return	array
	 */
	public function getMissions() {
		$missions = array();
		
		$startPlanetTypeID = $this->getStartPlanet()->planetTypeID;
		
		if(is_object($this->getTargetPlanet())) {
			$endPlanetTypeID = $this->getTargetPlanet()->planetTypeID;
		}
		else {
			$endPlanetTypeID = null;
		}
		
		self::readCache();
				
		foreach(self::$cache as $missionID => $mission) {
			// check route
			if(!isset($mission['route'][$startPlanetTypeID][$endPlanetTypeID])) {
				continue;
			}
			
			// execute method			
			require_once(LW_DIR.$mission['classPath']);
			
			$className = StringUtil::getClassName($mission['classPath']);
			
			if (!class_exists($className)) {
				require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
						
			$available = call_user_func(array($className, 'check'), $this);
			
			if($available) {
				$missions[$missionID] = $mission['route'][$startPlanetTypeID][$endPlanetTypeID];
			}
		}
		
		return $missions;
	}
	
	/**
	 * Returns the class path of the selected mission.
	 * 
	 * @return	string
	 */
	public function getClassPath() {
		self::readCache();
		
		return self::$cache[$this->missionID]['classPath'];
	}
	
	
	/**
	 * Returns the start planet.
	 * 
	 * @return	Planet
	 */
	public function getStartPlanet() {
		return Planet::getInstance($this->startPlanetID);
	}
	
	/**
	 * Returns the target planet.
	 * 
	 * @return	Planet
	 */
	public function getTargetPlanet() {
		return Planet::getInstance($this->endPlanetID);
	}
}
?>