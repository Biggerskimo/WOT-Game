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

require_once(LW_DIR.'lib/system/event/WOTEventEditor.class.php');
require_once(LW_DIR.'lib/system/event/WOTEventExecuteException.class.php');
require_once(LW_DIR.'lib/system/event/WOTEventSingleton.class.php');

/**
 * Executes all upcoming events of the wot event system and provides functions to create or delete events.
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class WOTEventExecute {	
	protected $sqlConditions = "";
	
	protected $events = array();
	protected $eventIDsStr = "";
	
	protected $enableSafeMode = true;
	
	/**
	 * Reads the event data.
	 * 
	 * @param	int		timestamp
	 */
	public function __construct($time = null) {
		WOTEventEditor::readCache();
		
		$this->readData($time);
		
		$this->executeEvents();
		
		$this->deleteEvents();
	}
	
	/**
	 * Enables the safe mode.
	 * 
	 * @param	int		safe mode
	 */
	protected function enableSafeMode($safeMode = true) {
		$this->safeMode = $safeMode;
	}
	
	/**
	 * Reads the event data.
	 * 
	 * @param	int		timestamp
	 */
	protected function readData($time = null) {
		if($time === null) {
			$time = time();
		}
		
		$sql = "SELECT ugml_event.*,
					GROUP_CONCAT(
						CONCAT(name, ',', value) 
						SEPARATOR ';')
						AS eventData
				FROM ugml_event
				LEFT JOIN ugml_event_data
					ON ugml_event.eventID = ugml_event_data.eventID
				WHERE `time` < ".$time.$this->sqlConditions."
				GROUP BY ugml_event.eventID
				ORDER BY ugml_event.`time`";
		echo $sql;
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$this->events[$row['eventID']] = new WOTEventEditor(null, $row);
		}
	}
	
	/**
	 * Executes the events.
	 */
	protected function executeEvents() {
		WCF::getDB()->sendQuery("SET AUTOCOMMIT = 0");
		foreach($this->events as $eventID => $eventObj) {
			WCF::getDB()->sendQuery("START TRANSACTION");
			try {				
				// delete			
				if(!empty($this->eventIDsStr)) {
					$this->eventIDsStr .= ",";
				}
				$this->eventIDsStr .= $eventID;
				
				// execute
				$classPath = WOTEventEditor::$cache[$eventObj->eventTypeID]['classPath'];
				$className = StringUtil::getClassName($classPath);
				require_once(LW_DIR.$classPath);				
				
				// look for singleton
				if(in_array('WOTEventSingleton', class_implements($className))) {
					$h = call_user_func(array($className, 'getInstance'), $eventObj->specificID);
					echo "s";
				}
				// no singleton implemented, so create own instance of the class
				else {
					$h = new $className($eventObj->specificID);
					echo "n";
				}
				echo "b";
				$h->execute($eventObj->eventData);
				echo "a";
				// protect against fatal errors in following events
				if($this->enableSafeMode) {
					if(!empty($this->eventIDsStr)) {
						WOTEventEditor::deleteEvents($this->eventIDsStr);
					}
					$this->eventIDsStr = "";
				}
			}
			catch(Exception $e) {
				WCF::getDB()->sendQuery("ROLLBACK");
				WCF::getDB()->sendQuery("START TRANSACTION");
				$this->deleteEvents();
				
				throw new WOTEventExecuteException($e, $h, $eventID);
			}
			WCF::getDB()->sendQuery("COMMIT");
		}
		WCF::getDB()->sendQuery("SET AUTOCOMMIT = 1");
	}
	
	/**
	 * Deletes the executed events.
	 */
	public function deleteEvents() {
		if(!empty($this->eventIDsStr)) {
			WOTEventEditor::deleteEvents($this->eventIDsStr);
		}
		$this->eventIDsStr = "";
		
		// integrate this in wcf eventlistener
		require_once(LW_DIR.'lib/data/planet/Planet.class.php');
		Planet::clean();
		require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
		Fleet::clean();
	}
}
?>