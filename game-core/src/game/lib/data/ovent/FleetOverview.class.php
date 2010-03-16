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

/**
 * Handles the resources overview.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class FleetOverview {	
	private $overall = array();
	private $overallCount = 0;
	private $missions = array();
	private $missionCounts = array();
	
	/**
	 * Creates a new FleetOverview object.
	 */
	public function __construct() {
		$this->overall = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);
		
		WCF::getTPL()->assign('fleetOverview', $this);
	}
	
	/**
	 * Adds the resources of a new fleet.
	 * 
	 * @param	int	missionID
	 * @param	int	metal
	 * @param	int	crystal
	 * @param	int	deuterium
	 */
	public function add($missionID, $metal, $crystal, $deuterium) {
		if(!isset($this->missions[$missionID])) {
			$this->missions[$missionID] = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);
			$this->missionCounts[$missionID] = 0;
		}
		
		$this->overall['metal'] += $metal;
		$this->overall['crystal'] += $crystal;
		$this->overall['deuterium'] += $deuterium;
		$this->overallCount++;
		
		$this->missions[$missionID]['metal'] += $metal;
		$this->missions[$missionID]['crystal'] += $crystal;
		$this->missions[$missionID]['deuterium'] += $deuterium;
		$this->missionCounts[$missionID]++;
	}
	
	/**
	 * Returns the overall resources.
	 * 
	 * @return	array
	 */
	public function getOverall() {
		return $this->overall;
	}
	
	/**
	 * Returns the count of fleets.
	 * 
	 * @return	int
	 */
	public function getOverallCount() {
		return $this->overallCount;
	}
	
	/**
	 * Returns the resources by missions.
	 * 
	 * @return	array
	 */
	public function getMissions() {
		return $this->missions;
	}
	
	/**
	 * Returns the count of fleets by a mission.
	 * 
	 * @return	int
	 */
	public function getMissionCount($missionID) {
		return $this->missionCounts[$missionID];
	}
}
?>