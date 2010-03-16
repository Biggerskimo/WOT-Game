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

require_once(LW_DIR.'lib/data/fleet/NavalFormationAttackFleet.class.php');

/**
 * Simulates a combat with the given fleets
 */
class CombatSimulator extends NavalFormationAttackFleet {
	public $templateName = 'simulatorCombat';
	
	protected $attackerFleetObjs = array();
	protected $defenderFleetObjs = array();
	
	protected $correcture = false;
	
	/**
	 * Saves the fleets
	 * 
	 * @param	array	fleet objs of the attacker front
	 * @param	array	fleet objs of the defender front
	 */
	public function __construct($attackerFleetObjs, $defenderFleetObjs, $simulatingFleetArray) {
		$this->attackerFleetObjs = $attackerFleetObjs;
		$this->defenderFleetObjs = $defenderFleetObjs;
		
		parent::__construct(null, $simulatingFleetArray);
	}
	
	/**
	 * @see NavalFormationFleet::loadNavalFormationData()
	 */
	protected function loadNavalFormationData() {
		$this->navalFormationFleets = $this->attackerFleetObjs;
		
		$this->navalFormationUsers[0] = array('userName' => 'Angreifer');
	}
	
	/**
	 * @see NavalFormationFleet::getDefendingFleets()
	 */
	protected function getDefendingFleets() {
		$this->standByFleets = $this->defenderFleetObjs;
		
		$this->standByUsers[0] = array('userName' => 'Verteider');
	}

	/**
	 * @see NavalFormationFleet::createShipTypeArrays()
	 */
	protected function initData() {
		$this->loadNavalFormationData();
		$this->getDefendingFleets();

		$this->createShipTypeArrays();
	}
	
	/**
	 * @see NavalFormationAttackFleet::createShipTypeArray()
	 * 
	 * @param	Spec	spec
	 * @param	PhantomFleet	fleet (with weaponTech etc.)
	 */
	protected static function createShipTypeArray(Spec $specObj, PhantomFleet $fleet) {
		$shipTypeArray = array();
		
		$shipTypeArray['count'] = $specObj->level;
		$shipTypeArray['weapon'] = $specObj->weapon * (1 + 0.1 * $fleet->weaponTech);
		$shipTypeArray['shield'] = $specObj->shield * (1 + 0.1 * $fleet->shieldTech);
		$shipTypeArray['units'] = $specObj->costsMetal + $specObj->costsCrystal;	
		$shipTypeArray['hullPlating'] = $shipTypeArray['units'] /10 * (1 + 0.1 * $fleet->hullPlatingTech);
		$shipTypeArray['origShield'] = $shipTypeArray['shield'];
		$shipTypeArray['origHullPlating'] = $shipTypeArray['hullPlating'];
		$shipTypeArray['shots'] = 0;
		$shipTypeArray['imbibed'] = 0;
		$shipTypeArray['strafed'] = 0;
		$shipTypeArray['destroyed'] = 0;
		
		return $shipTypeArray;
	}
	
	/**
	 * @see NavalFormationFleet::createShipTypeArrays()
	 */
	protected function createShipTypeArrays() {		
		// attacker
		foreach($this->navalFormationFleets as $fleetID => $fleet) {			
			$userObj = $this->navalFormationUsers[$fleet->ownerID];
			
			Spec::storeData(false, false, $fleet);		
			$specs = Spec::getBySpecType(3, false);
			
			foreach($specs as $specID => $specObj) {
				$this->attackerShipTypes[$specID.$fleetID] = self::createShipTypeArray($specObj, $fleet);
			}
		}

		unset($this->defenderShipTypes['2020']);
		
		// defender (stand-by)
		foreach($this->standByFleets as $fleetID => $fleet) {			
			$userObj = $this->standByUsers[$fleet->ownerID];
			
			Spec::storeData(false, false, $fleet);		
			$specs = Spec::getBySpecType(array(3, 4), false);
			
			foreach($specs as $specID => $specObj) {
				$this->defenderShipTypes[$specID.$fleetID] = self::createShipTypeArray($specObj, $fleet);
			}
		}
	}
	

	/**
	 * @see NavalFormationFleet::checkMoon()
	 */
	protected function checkMoon() {
		global $game_config;

		// moon
		$chanceByBiggest = 0;
		$chanceByUnits = 0;

		if(round((500 - (1 / ($this->debris['metal'] + $this->debris['crystal']) * $game_config['biggest_debris'] * 250)) / 25) < 0) $chanceByBiggest = 0;
		else $chanceByBiggest = round((500 - (1 / ($this->debris['metal'] + $this->debris['crystal']) * $game_config['biggest_debris'] * 250)) / 25);

		if(round((350 - (0.6 / ($this->debris['metal'] + $this->debris['crystal']) * 150000 * 2500)) / 12) < 0) $chanceByUnits = 0;
		else $chanceByUnits = round((350 - (0.6 / ($this->debris['metal'] + $this->debris['crystal']) * 150000 * 2500)) / 12);

		$moonChance = ($chanceByBiggest + $chanceByUnits);

		/*$rand = rand(0, 99);

		if($moonChance > $rand) {
			$rand = rand(0, 500);
			$size = round(4750 + ($chanceByUnits * 115) + $rand);

			$rand = rand(20, 60);
			$temp = $this->getEndPlanet()->temp_max - $rand;

			$this->moon = array('size' => $size,
					'temp' => $temp,
					'chance' => $moonChance);
		} else {*/
			$this->moon = array('size' => null,
					'temp' => null,
					'chance' => $moonChance);
		//}
	}
}
?>