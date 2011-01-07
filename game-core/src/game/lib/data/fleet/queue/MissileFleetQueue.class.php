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

require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');

/**
 * Adds functions for handling missile fleet queue.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class MissileFleetQueue extends FleetQueue {
	/**
	 * @see FleetQueue::validate()
	 */
	public function validate() {
		parent::validate();
		
		$maxSystems = WCF::getUser()->impulse_motor_tech * 4;
		$systemsDistance = abs($this->getStartPlanet()->system - $this->getTargetPlanet()->system);
		
		if($maxSystems < $systemsDistance || $this->getStartPlanet()->galaxy != $this->getTargetPlanet()->galaxy) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.fleet.tooFarAway'));			
		}		
	}
	
	/**
	 * @see FleetQueue::fire()
	 */
	public function fire() {
		parent::fire();

		$this->fleetEditor->update('primaryDestination', $this->primaryDestination);
	}
	
	/**
	 * @see	FleetQueue::getDuration()
	 */
	public function getDuration() {
		global $game_config;
		
		$systemsDistance = abs($this->getStartPlanet()->system - $this->getTargetPlanet()->system);
		$speedFactor = $game_config['fleet_speed'] / 2500;
		
		$time = 30 + 60 * $systemsDistance;
		$realTime = round($time / $speedFactor);
		
		return $realTime;
	}
	
	/**
	 * @see FleetQueue::getConsumption()
	 */
	public function getConsumption() {
		return 0;
	}
	
	/**
	 * @see FleetQueue::getMissions()
	 */
	public function getMissions() {
		self::readCache();
		
		return array(20 => 'MissileAttackFleet');
	}
}
?>