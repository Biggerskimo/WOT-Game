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

require_once(LW_DIR.'lib/data/fleet/log/LoggableFleet.class.php');

/**
 * Provides some functions for fleet logging.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetLog {
	private static $invalidFleetIDs = array();
	
	/**
	 * Inserts a new log row.
	 * 
	 * @param	LoggableFleet
	 */
	public static function create(LoggableFleet $fleet) {
		EventHandler::fireAction($fleet, 'logData');
		// TODO: integrate this in wcf eventlistener logData@Fleet
		self::addRelevantData($fleet);
		
		$data = $fleet->getData();
		
		$array = array();
		
		$array[] = array(
			'data' => $data,
			'fleet' => $fleet->getFleetArray(),
			'stack' => self::getStacktrace(),
			'time' => time()
		);
		
		$string = LWUtil::serialize(serialize($array), 1);
		
		if(!$data['targetPlanetID'])
			$data['targetPlanetID'] = "NULL";
		
		$sql = "INSERT INTO ugml_archive_fleet
				(fleetID, impactTime, returnTime,
				 startPlanetID, targetPlanetID, missionID,
				 data)
				VALUES
				(".$data['fleetID'].", ".$data['impactTime'].", ".$data['returnTime'].",
				 ".$data['startPlanetID'].", ".$data['targetPlanetID'].", ".$data['missionID'].",
				 '".escapeString($string)."')";
		WCF::getDB()->sendQuery($sql);
		
		if(isset(self::$invalidFleetIDs[$data['fleetID']])) {
			unset(self::$invalidFleetIDs[$data['fleetID']]);
		}
	}
	
	/**
	 * Reads the old data array of a fleet.
	 * 
	 * @param	int		fleet id
	 * @return	array
	 */
	private static function readArray($fleetID) {
		$sql = "SELECT data
				FROM ugml_archive_fleet
				WHERE fleetID = ".$fleetID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		if(!$row) {
			return false;
		}
		return unserialize(LWUtil::unserialize($row['data']));
	}
	
	/**
	 * Creates a small stacktrace. This method logs all calls in LoggableFleet and the call before.
	 * 
	 * @return	string
	 */
	private static function getStacktrace() {
		$stacktrace = debug_backtrace();
		
		$newStacktrace = array();
		
		$protectNext = false;
		
		$callerClass = '';
		foreach($stacktrace as $call) {
			$valid = ($call['class'] != 'FleetLog' && empty($callerClass));
			
			// this is not interesting
			if($call['class'] == 'FleetLog') {
			}
			// first row that is not in FleetLog; remember classname and log all
			else if(empty($callerClass)) {
				$callerClass = $call['class'];
				
				array_unshift($newStacktrace, $call['class'].'->'.$call['function']);
			}
			// other rows in the remembered class
			else if($call['class'] == $callerClass) {
				array_unshift($newStacktrace, $call['class'].'->'.$call['function']);
			}
			// another class, only log this row
			else {
				array_unshift($newStacktrace, $call['class'].'->'.$call['function']);

				break;
			}
		}
		
		return implode(',', $newStacktrace);
	}
	
	/**
	 * Adds additional relevant data.
	 * 
	 * @param	LoggableFleet $fleet
	 */
	private static function addRelevantData(LoggableFleet &$fleet) {
		$fleet->addData(array(
					'ownerName' => $fleet->getOwner()->username,
					'ofiaraName' => $fleet->getOfiara()->username,
					'startPlanetCoords' => $fleet->getStartPlanet()->galaxy.':'.$fleet->getStartPlanet()->system.':'.$fleet->getStartPlanet()->planet.'\''.$fleet->getStartPlanet()->planetKind,
					'targetPlanetCoords' => $fleet->galaxy.':'.$fleet->system.':'.$fleet->planet.'\''.$fleet->getTargetPlanet()->planetKind,
					'ownerIP' => $fleet->getOwner()->user_lastip,
					'ownerIpTime' => $fleet->getOwner()->onlinetime,
					'ofiaraIP' => $fleet->getOfiara()->user_lastip,
					'ofiaraIpTime' => $fleet->getOfiara()->onlinetime
			));
	}
	
	/**
	 * Updates the data of a logged fleet.
	 * 
	 * @param	LoggableFleet
	 */
	public static function update(LoggableFleet $fleet) {
		EventHandler::fireAction($fleet, 'logData');
		// TODO: integrate this in wcf eventlistener logData@Fleet
		self::addRelevantData($fleet);
		
		$data = $fleet->getData();
		
		if(isset(self::$invalidFleetIDs[$data['fleetID']])) {
			return;
		}
		
		$array = self::readArray($data['fleetID']);
		
		if($array === false) {
			self::$invalidFleetIDs[$data['fleetID']] = true;
			
			return;
		}
		
		$array[] = array(
			'data' => $data,
			'fleet' => $fleet->getFleetArray(),
			'stack' => self::getStacktrace(),
			'time' => time()
		);
		
		$string = LWUtil::serialize(serialize($array), 1);
		
		$sql = "UPDATE ugml_archive_fleet
				SET data = '".escapeString($string)."'
				WHERE fleetID = ".$data['fleetID'];
		WCF::getDB()->sendQuery($sql);
	}
}
?>