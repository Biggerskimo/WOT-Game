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

/**
 * Harvestes a debris.
 *
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds  <http://lost-worlds.net>
 */
class HarvestFleet extends AbstractFleetEventHandler implements Mission {
	protected $missionID = 8;
	
	protected $harvested = array('metal' => 0, 'crystal' => 0);
	
	/**
	 * @see AbstractFleetEventHandler::executeImpact()
	 */
    public function executeImpact() {
		$this->calculateHarvestedResources();

		$this->getTargetPlanet()->getEditor()->changeResources(-$this->harvested['metal'], -$this->harvested['crystal']);
		$this->getEditor()->changeResources($this->harvested['metal'], $this->harvested['crystal']);	

		// fix resources
		$this->searches = $this->replaces = array();
		$this->initArrays();
    }
	
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		return isset($fleetQueue->ships[209]);
	}
    
    /**
     * Calculates the capacity which can be used for harvest.
     *
     * @return	float	capacity
     */
    protected function getCapacity() {
    	$fullCapacity = $otherCapacity = 0;
    	
    	foreach($this->fleet as $specID => $shipCount) {
    		if(!$shipCount) {
    			continue;
    		}
    		
    		$shipCapacity = Spec::getSpecVar($specID, 'capacity');

    		if($specID == 209) {
    			$fullCapacity += $shipCount * $shipCapacity;
    		}
    		else {
    			$otherCapacity += $shipCount * $shipCapacity;
    		}
    	}
    	
    	$free = ($otherCapacity + $fullCapacity) - $this->getRessources('all');

    	$capacity = min($fullCapacity, $free);

    	return $capacity;
    }
    
    /**
     * Calculates the resources that are harvested by this fleet.
     */
    protected function calculateHarvestedResources() {    
		$capacity = $this->getCapacity();

		// capacity is higher than the ressources in the debris
		if($capacity > ($this->getTargetPlanet()->metal + $this->getTargetPlanet()->crystal)) {
			$this->harvested['metal'] = $this->getTargetPlanet()->metal;
			$this->harvested['crystal'] = $this->getTargetPlanet()->crystal;
		}
		// half capacity is smaller than metal
		// !and!
		// half capacity is smaller than crystal
		else if($this->getTargetPlanet()->metal >= ($capacity / 2) && $this->getTargetPlanet()->crystal >= ($capacity / 2)) {
			$this->harvested['metal'] = ($capacity / 2);
			$this->harvested['crystal'] = ($capacity / 2);
		}
		// half capacity is smaller than metal
		// !and!
		// half capacity is higher than crystal
		else if($this->getTargetPlanet()->metal > ($capacity / 2)) {
			$this->harvested['metal'] = ($capacity - $this->getTargetPlanet()->crystal);
			$this->harvested['crystal'] = $this->getTargetPlanet()->crystal;
		}
		// half capacity is higher than metal
		// !and!
		// half capacity is smaller than crystal
		else {
			$this->harvested['metal'] = $this->getTargetPlanet()->metal;
			$this->harvested['crystal'] = ($capacity - $this->getTargetPlanet()->metal);
		}
    }
	
	/**
	 * @see AbstractFleetEventHandler::getImpactOfiaraMessageData()
	 */
	protected function getImpactOfiaraMessageData() {
		return null;
	}
}
?>