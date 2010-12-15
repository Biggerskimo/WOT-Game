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
require_once(LW_DIR.'lib/data/fleet/log/FleetLog.class.php');
require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');
require_once(LW_DIR.'lib/data/AbstractDecorator.class.php');
require_once(LW_DIR.'lib/system/event/WOTEventEditor.class.php');

/**
 * This class provides some functions for creating, modifying and deleting fleets.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2010 Lost Worlds <http://lost-worlds.net>
 */
class FleetEditor extends AbstractDecorator {
	protected $fleetID = 0;
	
	/**
	 * Creates a new FleetEditor object.
	 * 
	 * @param	int		fleet id
	 */
	public function __construct($fleetID) {
		$this->fleetID = $fleetID;
	}
	
	/**
	 * Creates a new fleet.
	 *
	 * @param	int		start planet id
	 * @param	int		end planet id
	 * @param	array	ships
	 * @param	int		galaxy
	 * @param	int		system
	 * @param	int		planet
	 * @param	float	metal
	 * @param	float	crystal
	 * @param	float	deuterium
	 * @param	float	duration
	 * @param	int		mission id
	 * @param	float	time
	 * @param	int		package id
	 * @return	FleetEditor
	 */
	public static function create($startPlanetID, $endPlanetID, $ships, $galaxy, $system, $planet, $metal, $crystal, $deuterium, $duration, $missionID, $time = null, $packageID = PACKAGE_ID) {
		if($time === null) {
			$time = round(microtime(true), 2);
		}
		$startPlanet = Planet::getInstance($startPlanetID);
		$targetPlanet = Planet::getInstance($endPlanetID);
		
		// init vars
		FleetQueue::readCache();
		$classPath = FleetQueue::$cache[$missionID]['classPath'];
		$ownerID = Planet::getInstance($startPlanetID)->id_owner;
		$ofiaraID = Planet::getInstance($endPlanetID)->id_owner;
		$impactTime = $time + $duration;
		$returnTime = $time + $duration * 2;
		
		// insert fleet
		$fleetID = self::insert($startPlanetID, $endPlanetID, $ownerID, $ofiaraID, $galaxy, $system, $planet, $metal, $crystal, $deuterium, $time, $impactTime, $returnTime, $missionID, $packageID);
		
		// create events
		$impactEvent = WOTEventEditor::create(1, $fleetID, array('state' => 0), $impactTime);
		$returnEvent = WOTEventEditor::create(1, $fleetID, array('state' => 1), $returnTime);
		
		// register events
		$fleetEditor = new FleetEditor($fleetID);
		$fleetEditor->update(array('impactEventID' => $impactEvent->eventID, 'returnEventID' => $returnEvent->eventID));

		// inserts ships
		$fleetEditor->updateShips($ships);
		
		// add to log
		$fleet = Fleet::getInstance($fleetID);
		FleetLog::create($fleet);
				
		return $fleetEditor;
	}

	/**
	 * Inserts a database entry.
	 *
	 * @param	int		start planet id
	 * @param	int		end planet id
	 * @param	int		owner id
	 * @param	int		ofiara id
	 * @param	int		galaxy
	 * @param	int		system
	 * @param	int		planet
	 * @param	float	metal
	 * @param	float	crystal
	 * @param	float	deuterium
	 * @param	float	start time
	 * @param	float	impact time
	 * @param	float	return time
	 * @param	int		mission id
	 * @return	int		fleet id
	 */
	public static function insert($startPlanetID, $endPlanetID, $ownerID, $ofiaraID, $galaxy, $system, $planet, $metal, $crystal, $deuterium, $startTime, $impactTime, $returnTime, $missionID, $packageID = PACKAGE_ID) {
		if(!$ofiaraID)
		{
			$ofiaraID = "NULL";
			
			if(!$endPlanetID)
				$endPlanetID = "NULL";
		}
		
		$sql = "INSERT INTO ugml_fleet
				(startPlanetID, targetPlanetID, packageID,
				 ownerID, ofiaraID, missionID,
				 galaxy, system, planet,
				 metal, crystal, deuterium,
				 returnTime, impactTime, startTime)
				VALUES
				(".$startPlanetID.", ".$endPlanetID.", ".$packageID.",
				 ".$ownerID.", ".$ofiaraID.", ".$missionID.",
				 ".intval($galaxy).", ".intval($system).", ".intval($planet).",
				 ".$metal.", ".$crystal.", ".$deuterium.",
				 ".$returnTime.", ".$impactTime.", ".$startTime.")";
		WCF::getDB()->sendQuery($sql);
		
		$fleetID = WCF::getDB()->getInsertID();
		
		return $fleetID;
	}
	
	/**
	 * Changes the time of a event.
	 * 
	 * @param	mixed	can be array or integer. if integer, to every event this timediff will be added
	 */
	public function changeTime($events) {
		$updates = "";
		
		// change all
		if(is_numeric($events)) {			
			$data = $this->getData();
		
			foreach($data as $key => $eventID) {
				if(strpos($key, 'EventID') !== false && $eventID != 0) {
					$eventName = substr($key, 0, -7);
					$event = $this->getEvent($eventName);
					
					$oldTime = $this->{$eventName.'Time'};
					$newTime = $oldTime + $events;
					
					$event->changeTime($newTime);
					$this->{$eventName.'Time'} = $newTime;
									
					if(!empty($updates)) {
						$updates .= ", ";
					}
					$updates .= $eventName."Time = ".$newTime;
				}
			}
		}
		// change only some events
		else {
			foreach($events as $eventName => $time) {
				$event = $this->getEvent($eventName);
				
				$event->changeTime($time);
				
				if(!empty($updates)) {
					$updates .= ", ";
				}
				$this->{$eventName."Time"} = $time;
				$updates .= $eventName."Time = ".$time;
			}
		}
		
		if(empty($updates)) {
			return;
		}
		
		$sql = "UPDATE ugml_fleet
				SET ".$updates."
				WHERE fleetID = ".$this->fleetID;
		WCF::getDB()->sendQuery($sql);
		
		FleetLog::update($this->getObject());
	}
	
	/**
	 * Returns the event editor for a event
	 * 
	 * @param	string	event name
	 * @return	WOTEventEditor
	 */
	public function getEvent($eventName) {
		$eventIDField = $eventName.'EventID';
		$eventID = $this->$eventIDField;
		$event = new WOTEventEditor($eventID);
		
		return $event;
	}
	
	/**
	 * Updates a fleet.
	 * 
	 * @param	mixed	string (key) or a associative
	 * @param	mixed	value for key
	 */
	public function update($array, $value = null) {
		if(!is_array($array)) $array = array($array => $value);
		
		$updates = "";
		
		foreach($array as $key => $value) {
			if(!empty($updates)) {
				$updates .= ",";
			}
			if(is_int($value) || $value == "NULL")
				$updates .= " `".$key."` = ".$value." ";
			else
				$updates .= " `".$key."` = '".escapeString($value)."' ";
			
			$this->$key = $value;
		}
		
		$sql = "UPDATE ugml_fleet
				SET ".$updates."
				WHERE fleetID = ".$this->fleetID;
		WCF::getDB()->sendQuery($sql);
		
		FleetLog::update($this->getObject());
	}
	
	/**
	 * Changes the resources.
	 * 
	 * @param	float	metal
	 * @param	float	crystal
	 * @param	float	deuterium
	 * @param	bool	absolute
	 */
	public function changeResources($metal = null, $crystal = null, $deuterium = null, $absolute = false) {
		$metal = intval($metal);
		$crystal = intval($crystal);
		$deuterium = intval($deuterium);
		
		// calc relative values
		if($absolute) {
			$metal -= $this->metal;
			$crystal -= $this->crystal;
			$deuterium -= $this->deuterium;
		}
		
		$this->metal += $metal;
		$this->crystal += $crystal;
		$this->deuterium += $deuterium;
		
		// mysql accepts constructs like "metal = metal + -500"
		$sql = "UPDATE ugml_fleet
				SET metal = metal + ".$metal.",
					crystal = crystal + ".$crystal.",
					deuterium = deuterium + ".$deuterium."
				WHERE fleetID = ".$this->fleetID;
		WCF::getDB()->sendQuery($sql);
		
		FleetLog::update($this->getObject());
	}
	
	/**
	 * Deletes this fleet.
	 */
	public function delete() {
		$data = $this->getData();
		
		$sql = "DELETE ugml_fleet, ugml_fleet_spec
				FROM ugml_fleet, ugml_fleet_spec
				WHERE ugml_fleet.fleetID = ugml_fleet_spec.fleetID
					AND	ugml_fleet.fleetID = ".$this->fleetID;
		WCF::getDB()->sendQuery($sql);
		
		// hanging fleet fix
		if(WCF::getDB()->getAffectedRows() == 0) {
			$sql = "DELETE FROM ugml_fleet
					WHERE ugml_fleet.fleetID = ".$this->fleetID;
			WCF::getDB()->sendQuery($sql);
		}
		
		// get event ids
		$eventIDsStr = "";
		foreach($data as $key => $eventID) {
			if(strpos($key, 'EventID') !== false && $eventID != 0) {
				if(!empty($eventIDsStr)) {
					$eventIDsStr .= ",";
				}
				$eventIDsStr .= $eventID;
			}
		}
		if(!empty($eventIDsStr)) {
			WOTEventEditor::deleteEvents($eventIDsStr);
		}
		
		$this->fleet = array();
		
		FleetLog::update($this->getObject());
	}
	
	/**
	 * Cancels this flight.
	 */
	public function cancel() {
		// delete events
		$eventIDsStr = "";
		$data = $this->getData();
		foreach($data as $key => $eventID) {
			if(strpos($key, 'EventID') !== false && $key !== 'returnEventID' && $eventID != 0) {
				if(!empty($eventIDsStr)) {
					$eventIDsStr .= ",";
				}
				$eventIDsStr .= $eventID;
			}
		}
		if(empty($eventIDsStr)) {
			return;
		}
			
		WOTEventEditor::deleteEvents($eventIDsStr);
		
		// calc flown time
		$returnTime = $this->getCancelDuration() + microtime(true);
		
		$returnEvent = new WOTEventEditor($this->returnEventID);
		
		$returnEvent->changeTime($returnTime);
		
		$this->update(array('impactTime' => 0, 'returnTime' => $returnTime));
		
		EventHandler::fireAction($this, 'cancel');
		// TODO: integrate this in wcf eventhandler cancel@FleetEditor
		if($this->missionID == 11) {
			$leaderFleet = $this->getNavalFormation()->cancelFleet($this->fleetID);
			
			// update ovents
			if($leaderFleet !== null)
				FleetOvent::create($leaderFleet, true, false, true);
		}
		// TODO: integrate this in wcf eventhandler cancel@FleetEditor
		if($this->missionID == 12) {
			$this->update('wakeUpTime', 0);
		}
		
		
		FleetLog::update($this->getObject());
	}
	
	/**
	 * Changes the ships of a fleet.
	 * 
	 * @param	array	associative array
	 */
	public function updateShips($ships) {
		$fleet = $this->fleet;
		
		$changedShips = Spec::diff($fleet, $ships);
				
		$inserts = "";
		$deletes = "";
		foreach($changedShips as $specID => $shipCount) {
			// add/change
			if(!empty($specID)) {
				if($shipCount > 0) {
					if(!empty($inserts)) {
						$inserts .= ",";
					}
					$inserts .= "(".$this->fleetID.", ".$specID.", ".$shipCount.")";
				}
				// delete
				else {
					if(!empty($deletes)) {
						$deletes .= ",";
					}
					$deletes .= "(".$this->fleetID.", ".$specID.")";
				}
			}
			
			if(!$shipCount)	{
				unset($fleet[$specID]);
			}
			else {
				$fleet[$specID] = $shipCount;
			}
		}
		$this->fleet = $fleet;
		
		if(!empty($inserts)) {
			$sql = "REPLACE INTO ugml_fleet_spec
					(fleetID, specID, shipCount)
					VALUES ".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		if(!empty($deletes)) {
			$sql = "DELETE FROM ugml_fleet_spec
					WHERE (fleetID, specID) IN (".$deletes.")";
			WCF::getDB()->sendQuery($sql);
		}
		
		FleetLog::update($this->getObject());
	}
	
	/**
	 * @see AbstractDecorator::getObject()
	 */
	protected function getObject() {
		return Fleet::getInstance($this->fleetID);
	}
}
?>