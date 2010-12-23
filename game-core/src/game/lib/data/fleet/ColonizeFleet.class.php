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
require_once(LW_DIR.'lib/data/planet/PlanetEditor.class.php');
require_once(LW_DIR.'lib/data/system/System.class.php');

/**
 * Includes all functions to colonize a new planet.
 * 
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 * @author		Biggerskimo
 */
class ColonizeFleet extends AbstractFleetEventHandler implements Mission {
	protected $missionID = 9;
	
	const COLONY_SHIP = 208;
	const MAX_PLANETS = 9;
	const DEFAULT_METAL = 500;
	const DEFAULT_CRYSTAL = 500;
	const DEFAULT_DEUTERIUM = 0;
	const DEFAULT_FIELDS = null;
	
	protected $message = 'success';
	
	/**
	 * @see AbstractFleetEventHandler::executeImpact()
	 */
    public function executeImpact() {

		// check colonies
		$sql = "SELECT COUNT(*) AS count
				FROM ugml_planets
				WHERE planetKind = 1
					AND id_owner = ".$this->ownerID;
		$count = WCF::getDB()->getFirstRow($sql);

		// get existing planet
		$system = new System($this->galaxy, $this->system);
		$planetObj = $system->getPlanet($this->planet);

		// restricted by planet limit
		if($count['count'] >= self::MAX_PLANETS) {
			$this->message = 'planetLimit';
			return;
		}
		// planet exists
		if($planetObj !== null) {
			$this->message = 'exists';
			return;
		}
		
		// create planet
		--$this->fleet[self::COLONY_SHIP];
		
		$name = WCF::getLanguage()->get('wot.planet.colony');
		$planet = PlanetEditor::create($this->galaxy, $this->system, $this->planet, $name, $this->ownerID, self::DEFAULT_METAL, self::DEFAULT_CRYSTAL, self::DEFAULT_DEUTERIUM, 1, time(), self::DEFAULT_FIELDS, null);
				
		$planet->getEditor()->changeResources($this->metal, $this->crystal, $this->deuterium);
		$planet->getEditor()->changeLevel($this->fleet);
		$this->getEditor()->delete();
    }
	
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		if(isset($fleetQueue->ships[self::COLONY_SHIP]) && $fleetQueue->planetType == 1) {
			return true;			
		}
		return false;
	}
    
    /**
     * @see AbstractFleetEventHandler::getImpactOwnerMessageData()
     */    
	protected function getImpactOwnerMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.owner'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.owner.subject'),
				'text' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.'.$this->message.'.owner.text'),
			);
		
		return $messageData;    	
    }
    
    /**
     * @see AbstractFleetEventHandler::getImpactOfiaraMessageData()
     */
    protected function getImpactOfiaraMessageData() {
    	return null;
    }
}
?>