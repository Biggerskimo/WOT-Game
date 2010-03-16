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

require_once(LW_DIR.'lib/data/fleet/mission/Mission.class.php');
require_once(LW_DIR.'lib/data/fleet/AbstractFleetEventHandler.class.php');

/**
 * A 'simple' fleet object for handling deployfleet events.
 *
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 */
class DeployFleet extends AbstractFleetEventHandler implements Mission {
	protected $missionID = 4;
    
    /**
     * Executes the impact event.
     * 
     * @param	array	data
     */
    protected function executeImpact() {
    	$this->getTargetPlanet()->getEditor()->changeResources($this->metal, $this->crystal, $this->deuterium);
		$this->getTargetPlanet()->getEditor()->changeLevel($this->fleet);
		$this->getEditor()->delete();
    }
	
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		if($fleetQueue->getTargetPlanet()->id_owner == $fleetQueue->getStartPlanet()->id_owner) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see AbstractFleetEventHandler::getImpactOfiaraMessageData()
	 */
	protected function getImpactOfiaraMessageData() {
		return null;
	}
	
	/**
	 * @see Fleet::getFleetSet()
	 */
	public function getFleetSet($planetID = false) {
		$fleetArray = parent::getFleetSet($planetID);
		
		if((isset($fleetArray[$this->returnTime.$this->fleetID]) && $this->getCancelDuration()) || $planetID) {
			unset($fleetArray[$this->returnTime.$this->fleetID]);
		}
		
		return $fleetArray;
	}
}
?>