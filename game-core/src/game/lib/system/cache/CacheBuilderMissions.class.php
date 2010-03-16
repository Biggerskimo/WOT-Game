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

require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Caches the missions.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class CacheBuilderMissions implements CacheBuilder {
	public $data = array();

	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$sql = "SELECT *
				FROM ugml_mission";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$this->data[$row['missionID']] = array(
				'packageID' => $row['packageID'],
				'classPath' => $row['classPath'],
				'route' => array()
			);			
		}
		
		if(count($this->data)) {
			$sql = "SELECT ugml_mission.missionID,
						ugml_mission_route.* 
					FROM ugml_mission
					LEFT JOIN ugml_mission_route
						ON ugml_mission.missionID = ugml_mission_route.missionID";
			$result = WCF::getDB()->sendQuery($sql);
					
			while($row = WCF::getDB()->fetchArray($result)) {
				$array = $row;
				unset($array['missionID'], $array['startPlanetTypeID'], $array['endPlanetTypeID']);
							
				$this->data[$row['missionID']]['route'][$row['startPlanetTypeID']][$row['endPlanetTypeID']] = $array;
			}
		}
		
		return $this->data;
	}
}
?>