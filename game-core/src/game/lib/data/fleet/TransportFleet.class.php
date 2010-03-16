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
 * Handles all transport fleet events.
 *
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 */
class TransportFleet extends AbstractFleetEventHandler implements Mission {
	protected $missionID = 3;

	/**
     * @see AbstractFleetEventHandler::executeImpact()
     */
    protected function executeImpact() {    	
    	$this->getTargetPlanet()->getEditor()->changeResources($this->metal, $this->crystal, $this->deuterium);
		$this->getEditor()->update(array('metal' => 0, 'crystal' => 0, 'deuterium' => 0));
    }
	
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		return true;
	}
	
	/**
	 * @see AbstractFleetEventHandler::getImpactOfiaraMessageData()
	 */
	protected function getImpactOfiaraMessageData() {
		if($this->ofiaraID == $this->ownerID) {
			return null;
		}
		
		return parent::getImpactOfiaraMessageData();
	}
}
?>