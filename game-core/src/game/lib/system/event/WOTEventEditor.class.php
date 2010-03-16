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
 * Provides functions to add, edit and delete events.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class WOTEventEditor extends DatabaseObject {
	public static $cache = array();
	
	/**
	 * Creates a new WOTEventEditor object.
	 * 
	 * @param	int		event id
	 * @param	array	event row
	 */
	public function __construct($eventID = null, $row = null) {
		if($row === null) {
			$sql = "SELECT ugml_event.*,
					GROUP_CONCAT(
						CONCAT(name, ',', value) 
						SEPARATOR ';')
						AS eventData
					FROM ugml_event
					LEFT JOIN ugml_event_data
						ON ugml_event.eventID = ugml_event_data.eventID
					WHERE ugml_event.eventID = ".$eventID."
					GROUP BY ugml_event.eventID";
			$row = WCF::getDB()->getFirstRow($sql); 
		}
		
		parent::__construct($row);
		
		$parts = explode(';', $this->eventData);
		$this->eventData = array();
		
		foreach($parts as $part) {
			list($name, $value) = explode(',', $part);
			
			$this->eventData[$name] = $value;
		}
	}
	
	/**
	 * Reads the missions cache.
	 */
	public static function readCache() {
		if(!count(self::$cache)) {
			WCF::getCache()->addResource('eventTypes-'.PACKAGE_ID, WCF_DIR.'cache/cache.eventTypes-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderEventTypes.class.php');
			self::$cache = WCF::getCache()->get('eventTypes-'.PACKAGE_ID);
		}
	}
	
	/**
	 * Creates a new wot event.
	 * 
	 * @param	int		event type id
	 * @param	array	data
	 * @param	float	timestamp
	 * @return	WOTEventEditor
	 */
	public static function create($eventTypeID, $specificID, $data = array(), $time = null) {
		if($time === null) {
			$time = round(microtime(true), 2);
		}
		
		$eventID = self::insert($eventTypeID, $specificID, $time);
		
		$editor = new WOTEventEditor($eventID);
		
		$editor->changeData($data);
		
		return $editor;
	}
	
	/**
	 * Inserts a wot event db entry.
	 * 
	 * @param	int		event type id
	 * @param	int		specific id
	 * @param	float	timestamp
	 * @return	int		event id
	 */
	public static function insert($eventTypeID, $specificID, $time) {
		$sql = "INSERT INTO ugml_event
				(eventTypeID, `time`, specificID)
				VALUES
				(".$eventTypeID.", ".$time.", ".$specificID.")";
		WCF::getDB()->sendQuery($sql);
		
		$eventID = WCF::getDB()->getInsertID();
		
		return $eventID;
	}
	
	/**
	 * Deletes events.
	 * 
	 * @param	string	event ids
	 */
	public static function deleteEvents($eventIDsStr) {	
		$sql = "DELETE ugml_event, ugml_event_data
				FROM ugml_event, ugml_event_data
				WHERE ugml_event.eventID = ugml_event_data.eventID
					AND ugml_event.eventID IN (".$eventIDsStr.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Changes the data of a event.
	 * 
	 * @param	mixed	array or string (name)
	 * @param	mixed	value
	 */
	public function changeData($array, $value = null) {
		if(!is_array($array)) {
			$array = array($array => $value);
		}
		
		$replaces = "";
		
		self::readCache();
		
		foreach(self::$cache[$this->eventTypeID]['data'] as $name => $value) {
			if(array_key_exists($name, $array)) {
				$value = $array[$name];
			}
			
			if(!empty($replaces)) {
				$replaces .= ",";
			}
			$replaces .= "(".$this->eventID.", '".escapeString($name)."', '".escapeString($value)."')";
		}
		
		if(!empty($replaces)) {
			$sql = "REPLACE INTO ugml_event_data
					(eventID, name, value)
					VALUES
					".$replaces;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Changes the time of this event.
	 * 
	 * @param	float	time
	 */
	public function changeTime($time) {
		$this->time = $time;
		
		$sql = "UPDATE ugml_event
				SET `time` = ".$time."
				WHERE eventID = ".$this->eventID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>