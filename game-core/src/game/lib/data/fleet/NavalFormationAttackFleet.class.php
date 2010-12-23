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
require_once(LW_DIR.'lib/data/fleet/CombatReport.class.php');
require_once(LW_DIR.'lib/data/fleet/NavalFormation.class.php');
require_once(LW_DIR.'lib/data/fleet/PhantomFleet.class.php');
require_once(LW_DIR.'lib/data/planet/Debris.class.php');
require_once(LW_DIR.'lib/data/stat/generator/StatGeneratorFactory.class.php');
require_once(LW_DIR.'lib/data/system/System.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');

/**
 * Naval Formation System
 *
 * All ships are summed up to groups, so it's faster than the 'normal'
 *  combat system. Because of this we need a lot of mechanisms to correct
 *  the result of the combat.
 * 
 * This class is divided in four logical groups:
 *  - preparation/aggregation
 *  - calculation/simulation
 *  - summary/disaggregation
 *  - data saving
 *  
 * The first three parts are automatically executed by the simulate() method.
 * The fourth part is optionally executed by saveData()
 * 
 * @author		Biggerskimo
 * @copyright	2007-2009 Lost Worlds <http://lost-worlds.net>
 */
class NavalFormationAttackFleet extends AbstractFleetEventHandler implements Mission {
	protected $missionID = 11;
	
	const ROUNDS = 6;
	const RECREATE_DEFENSE = 0.7;
	const MAX_EXEC_TIME = 0;
	const RAPID_FIRE = true;
	
	const DEBRIS_OWNER = 0;
	const DEBRIS_PLANET_TYPE_ID = 2;
	const MOON_PLANET_TYPE_ID = 3;
	
	protected $navalFormation = null;
	
	public $templateName = 'combat';
	protected $navalFormationFleets = array();
	protected $navalFormationUsers = array();
	protected $standByFleets = array();
	protected $standByUsers = array();
	protected $roundData = array();
	protected $lastRoundData = array();
	protected $attackerShipTypes = array();
	protected $defenderShipTypes = array('2020' => 0); // the 2020 is needed for displaying the block in the combat report correctly if there is no fleet/defense on the defender planet
	protected $attackerShipCount = 0;
	protected $defenderShipCount = 0;
	protected $executeAttackerMessage = null;
	protected $executeDefenderMessage = null;
	protected $attackerShipTypePercents = array();
	protected $defenderShipTypePercents = array();
	protected $shipTypeCombinations = array('attacker' => array(), 'defender' => array());
	protected $attackerRfShotCount = array();
	protected $defenderRfShotCount = array();
	
	protected $preCombatAttackerUnits = 0;
	protected $preCombatDefenderUnits = 0;
	protected $averageAttackerAttackStatPoints = 1000;
	protected $averageDefenderAttackStatPoints = 1000;
	protected $attackerExpectation = 0.5;
	protected $defenderExpectation = 0.5;
	
	public $capacity = 0;
	public $totalCapacity = 0;
	public $winner = null;
	public $moon = null;
	public $roundNo = 0;
	public $booty = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);
	public $debris = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);
	public $units = array('attacker' => 0, 'defender' => 0);
	public $entireUnits = 0;
	public $recreatedDefense = array();
	
	public $report = null;
	protected $log = "";
		
	/**
	 * @see AbstractFleetEventHandler::executeImpact()
	 */
	protected function executeImpact() {
		if($this->fleetID != $this->getNavalFormation()->leaderFleetID) {
			return;
		}
		
		$this->executeAttack();
	}
	
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		$foreignPlanet = ($fleetQueue->getTargetPlanet()->id_owner != WCF::getUser()->userID);
		$formations = NavalFormation::getByTargetPlanetID($fleetQueue->getTargetPlanet()->planetID, WCF::getUser()->userID);
		
		if($foreignPlanet && count($formations)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see AbstractFleetEventHandler::getImpactOwnerMessageData()
	 */
	protected function getImpactOwnerMessageData() {
		return null;
	}
	
	/**
	 * @see AbstractFleetEventHandler::getImpactOfiaraMessageData()
	 */
	protected function getImpactOfiaraMessageData() {
		return null;
	}

	/**
	 * Returns the naval formation object for this fleet.
	 * 
	 * @return	NavalFormation
	 */
	public function getNavalFormation() {
		if($this->navalFormation === null) {
			$this->navalFormation = new NavalFormation($this->formationID);
		}
		
		return $this->navalFormation;
	}
	
	/**
	 * Executes the attack.
	 */
	protected function executeAttack() {
		$this->simulate();
		$this->generateReport();
		$this->saveData(true);		
	}
	

	/**
	 * Loads the information of the fleets involved in this naval formation
	 */
	protected function loadNavalFormationData() {
		// formation exists
		if($this->getNavalFormation()->formationID) {
			$this->navalFormationFleets = $this->getNavalFormation()->fleets;
			$this->navalFormationUsers = $this->getNavalFormation()->users;
			
			return;
		}
		// ... or not, so we've to wrap this fleet as a naval formation			
		$this->navalFormationFleets[$this->fleetID] = $this;
		$this->navalFormationUsers[$this->ownerID] = $this->getOwner();
	}

	/**
	 * Loads the information of the fleets that are in stand by at the defender planet
	 */
	protected function getDefendingFleets() {
		// add planet
		$this->standByFleets[0] = new PhantomFleet(null, array('galaxy' => $this->getTargetPlanet()->galaxy,
				'system' => $this->getTargetPlanet()->system,
				'planet' => $this->getTargetPlanet()->planet,
				'fleet_owner' => $this->ofiaraID,
				'ownerID' => $this->ofiaraID));
		$this->standByUsers[$this->ofiaraID] = $this->standByFleets[0]->getOwner();
		
		// add stand-by fleets
		$sql = "SELECT ugml_fleet.*,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
				FROM ugml_fleet
		    	LEFT JOIN ugml_fleet_spec
		    		ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
				WHERE ".$this->impactTime." BETWEEN impactTime AND wakeUpTime
					AND impactTime > 0
					AND targetPlanetID = ".$this->targetPlanetID."
		    	GROUP BY ugml_fleet.fleetID";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleet = Fleet::getInstance(null, $row);
			$user = $fleet->getOwner();

			$this->standByFleets[$fleet->fleetID] = $fleet;
			$this->standByUsers[$fleet->ownerID] = $user;
		}
	}
	
	/**
	 * Simulates the combat
	 */
	public function simulate() {
		$this->initData();

		$startTime = microtime(true);

		do {
			$this->roundData[$this->roundNo]['attackerData'] = $this->attackerShipTypes;
			$this->roundData[$this->roundNo]['defenderData'] = $this->defenderShipTypes;

			if(count($this->defenderShipTypes) == 0 && $this->roundNo == 0) {
				break;
			}

			$this->roundNo++;

			$newRound = $this->simulateRound();
		} while($newRound);

		$endTime = microtime(true);
		$this->log .= 'time:'.($endTime - $startTime);

		$this->roundData[$this->roundNo]['attackerData'] = $this->attackerShipTypes;
		$this->roundData[$this->roundNo]['defenderData'] = $this->defenderShipTypes;

		$this->collectCombatData();
	}
	
	/**
	 * Collects the needed data before the combat is simulated
	 */
	protected function initData() {
		$this->loadNavalFormationData();
		$this->getDefendingFleets();
		
		$this->getExpectations();

		$this->createShipTypeArrays();
	}
	
	/**
	 * Calculates the expectations for this fight.
	 */
	protected function getExpectations() {
		extract($this->getAllAverageAttackStatPoints());
		
		//$this->log .= print_r($this->getAllAverageAttackStatPoints(), true);
		
		$this->attackerExpectation = 1 / (1 + pow(10, (($defender - $attacker) / 1000)));
		$this->defenderExpectation = 1 - $this->attackerExpectation;
	}
	
	/**
	 * Calculates the units of all involved fleets.
	 */
	protected function calculatePreCombatUnits() {
		$this->calculatePreCombatUnitsFleet($this->navalFormationFleets, $this->preCombatAttackerUnits);
		$this->calculatePreCombatUnitsFleet($this->standByFleets, $this->preCombatDefenderUnits);
	}
	
	/**
	 * Calculates the units of every fleet in the given fleet array.
	 * 
	 * @param	array	fleet array
	 * @param	int		overall units
	 */
	protected function calculatePreCombatUnitsFleet(&$fleets, &$overallUnits) {
		foreach($fleets as $fleet) {
			foreach($fleet->fleet as $specID => $shipCount) {
				$costs = Spec::getSpecVar($specID, 'costsMetal') + Spec::getSpecVar($specID, 'costsCrystal');
				$fleet->preCombatUnits = $costs * $shipCount;
				$overallUnits += $fleet->preCombatUnits;
			}
		}
	}
	
	/**
	 * Returns the attack points averages.
	 * 
	 * @return	array	attacker, defender
	 */
	protected function getAllAverageAttackStatPoints() {
		$array = array();
		
		$this->calculatePreCombatUnits();
		
		$array['attacker'] = $this->getAverageAttackStatPoints($this->navalFormationFleets);
		$array['defender'] = $this->getAverageAttackStatPoints($this->standByFleets);
		
		return $array;
	}
	
	/**
	 * Returns the attack points average.
	 * 
	 * @param	array	fleets
	 * @return	int		points
	 */
	protected function getAverageAttackStatPoints(&$fleets) {		
		$userData = array();
		$overallUnits = 0;
		$points = $pointsI = 0;
		
		foreach($fleets as $fleetID => $fleet) {
			if(!isset($userData[$fleet->ownerID])) {
				$pointsI = StatGeneratorFactory::getByTypeName('user', 'attack')->getPoints($fleet->ownerID);
				
				$userData[$fleet->ownerID] = array(
					'points' => $pointsI,
					'units' => 0				
				);
			}
			
			$userData[$fleet->ownerID]['units'] += $fleet->preCombatUnits;
			$overallUnits += $fleet->preCombatUnits;
		}
		
		//$this->log .= print_r($userData, true);
		//$this->log .= '..-'.$overallUnits.'-..';
		
		foreach($userData as $userID => $array) {
			if(!$overallUnits) {
				return $array['points'];
			}
			$points += $array['points'] * ($array['units'] / $overallUnits);
		}
		
		//$this->log .= '->'.$points.'<-';
		return $points;
	}
	
	/**
	 * Simulates a round of the combat
	 */
	protected function simulateRound() {
		// prepare
		$this->prepareRound();

		// execute
		$this->executeShipTypeCombinations();
		
		// finish
		$this->finishRound();

		// new round
		switch(true) {
			// this was the last round
			case ($this->roundNo == self::ROUNDS):
			case ($this->attackerShipCount == 0):
			case ($this->defenderShipCount == 0):
				return false;
			// another round
			default:
				return true;
		}
	}
	
	/**
	 * Creates a normalized ship type array.
	 * 
	 * @param	Spec	specObj
	 * @param	LWUser	userObj
	 * @return	array
	 */
	protected static function createShipTypeArray(Spec $specObj, LWUser $userObj) {
		$shipTypeArray = array();
		
		$shipTypeArray['count'] = $specObj->level;
		$shipTypeArray['weapon'] = $specObj->weapon * (1 + 0.1 * $userObj->military_tech);
		$shipTypeArray['shield'] = $specObj->shield * (1 + 0.1 * $userObj->defence_tech);
		$shipTypeArray['units'] = $specObj->costsMetal + $specObj->costsCrystal;	
		$shipTypeArray['hullPlating'] = $shipTypeArray['units'] /10 * (1 + 0.1 * $userObj->shield_tech);
		$shipTypeArray['origShield'] = $shipTypeArray['shield'];
		$shipTypeArray['origHullPlating'] = $shipTypeArray['hullPlating'];
		$shipTypeArray['shots'] = 0;
		$shipTypeArray['imbibed'] = 0;
		$shipTypeArray['strafed'] = 0;
		$shipTypeArray['destroyed'] = 0;
		
		return $shipTypeArray;
	}

	/**
	 * Collects the data for the shiptype arrays
	 */
	protected function createShipTypeArrays() {		
		// attacker
		foreach($this->navalFormationFleets as $fleetID => $fleet) {			
			$userObj = $this->navalFormationUsers[$fleet->ownerID];
			
			Spec::storeData(false, false, $fleet);		
			$specs = Spec::getBySpecType(3, false);
			
			foreach($specs as $specID => $specObj) {
				$this->attackerShipTypes[$specID.$fleetID] = self::createShipTypeArray($specObj, $userObj);
			}
		}

		// defender (planet)
		Spec::storeData($this->getTargetPlanet());		
		$specs = Spec::getBySpecType(array(3, 4), false);
		
		foreach($specs as $specID => $specObj) {
			$userObj = $this->standByUsers[$this->ofiaraID];
			
			$this->defenderShipTypes[$specID.'0'] = self::createShipTypeArray($specObj, $userObj);
		}
		
		// defender (stand-by)
		foreach($this->standByFleets as $fleetID => $fleet) {			
			$userObj = $this->standByUsers[$fleet->ownerID];
			
			Spec::storeData(false, false, $fleet);		
			$specs = Spec::getBySpecType(3, false);
			
			foreach($specs as $specID => $specObj) {
				$this->defenderShipTypes[$specID.$fleetID] = self::createShipTypeArray($specObj, $userObj);
			}
		}
	}
	
	/**
	 * Unsets the shots etc.
	 */
	protected function prepareRound() {
		$this->attackerShipCount = $this->defenderShipCount = 0;

		$this->createShipTypeCombinations();
	}

	/**
	 * Calculates the shiptypepercents (used for combinations) and counts the ships
	 */
	protected function calcShipTypePercents() {
		// count ships
		foreach($this->attackerShipTypes as $shipTypeID => $shipArray) {
			$this->attackerShipCount += $shipArray['count'];
		}

		foreach($this->defenderShipTypes as $shipTypeID => $shipArray) {
			$this->defenderShipCount += $shipArray['count'];
		}

		// calc percents
		foreach($this->attackerShipTypes as $shipTypeID => $shipArray) {
			$this->attackerShipTypePercents[$shipTypeID] = $shipArray['count'] / $this->attackerShipCount * 100;
		}

		foreach($this->defenderShipTypes as $shipTypeID => $shipArray) {
			$this->defenderShipTypePercents[$shipTypeID] = $shipArray['count'] / $this->defenderShipCount * 100;
		}
	}
	
	/**
	 * Creates the shiptypecombinations
	 */
	protected function createShipTypeCombinations() {
		$this->calcShipTypePercents();

		foreach($this->attackerShipTypes as $shipGroupID1 => $shipArray1) {
			$shipCount1 = $shipArray1['count'];

			$this->attackerShipTypes[$shipGroupID1]['shots'] = 0;
			$this->attackerShipTypes[$shipGroupID1]['imbibed'] = 0;
			
			$this->insertShots($this->attackerShipTypes[$shipGroupID1],
								$this->defenderShipTypes,
								$this->defenderShipTypePercents,
								$this->shipTypeCombinations['attacker'][$shipGroupID1],
								$shipCount1);
		}

		foreach($this->defenderShipTypes as $shipGroupID1 => $shipArray1) {
			$shipCount1 = $shipArray1['count'];

			$this->defenderShipTypes[$shipGroupID1]['shots'] = 0;
			$this->defenderShipTypes[$shipGroupID1]['imbibed'] = 0;
			
			$this->insertShots($this->defenderShipTypes[$shipGroupID1],
								$this->attackerShipTypes,
								$this->attackerShipTypePercents,
								$this->shipTypeCombinations['defender'][$shipGroupID1],
								$shipCount1);
		}
		
		$this->addRapidFireShots();
	}
	
	/**
	 * executes the shotcombinations (the core of the new combat system)
	 *
	 * to prevent integer overflows, we must use floats in this part
	 */
	protected function executeShipTypeCombinations() {
		// attacker
		foreach($this->shipTypeCombinations['attacker'] as $shipGroupID1 => $shotArray1) {
			foreach($shotArray1 as $shipGroupID2 => $shotCount) {
				$this->mainAlgorithm($this->attackerShipTypes[$shipGroupID1],
									 $this->defenderShipTypes[$shipGroupID2],
									 $shotCount,
									 $shipGroupID2);
			}
		}

		// defender
		foreach($this->shipTypeCombinations['defender'] as $shipGroupID1 => $shotArray1) {
			foreach($shotArray1 as $shipGroupID2 => $shotCount) {
				$this->mainAlgorithm($this->defenderShipTypes[$shipGroupID1],
									 $this->attackerShipTypes[$shipGroupID2],
									 $shotCount,
									 $shipGroupID2);
			}
		}
	}

	/**
	 * Adds rapid fire.
	 * 
	 * @param	array	shooter ship types array
	 * @param	array	shootee ship types array
	 * @param	array	shooter ship type combinations
	 * @param	array	shootee ship type percents
	 * @param	array	shooter	rf shot counts array
	 */
	protected function rfAlgorithm(&$shipTypes1, &$shipTypes2, &$shipTypeCombinations1, &$shipTypePercents2, &$rfShotCounts) {
		global $pricelist;
		
		foreach($shipTypeCombinations1 as $key1 => $shotArray1) {
			$shipTypeID1 = substr($key1, 0, 3);
			foreach($shotArray1 as $key2 => $shotCount) {
				$shipTypeID2 = substr($key2, 0, 3);
				
				// check for rf against first ship
				if(!isset($pricelist[$shipTypeID1]['sd'][$shipTypeID2])) {
					continue;
				}
				// r{1}/(x*r{2}+r{1}-r{2})
				
				// simulate rf 10 times
				$rfArray = array();
				for($i = 0; $i < 10; ++$i) {
					$shipTypeID3 = $shipTypeID2;
					
					$moreShots = 0;
					while($this->rapidFire($shipTypeID1, $shipTypeID3)) {
						$shipTypeID3 = $this->getShipTypeIdByShipTypePercents($shipTypePercents2);
										
						++$moreShots;
					}
					
					$rfArray[] = $moreShots;
				}
				
				// get median
				$moreShots = array_sum($rfArray) / count($rfArray);
				$moreShots *= $shotCount;
				
				// insert
				$this->insertShots($shipTypes1[$key1],
									$shipTypes2,
									$shipTypePercents2,
									$shipTypeCombinations1[$key1],
									$moreShots,
									false);
			}
		}
	}
	
	/**
	 * Executes the main algorithm
	 * 
	 * @param	array	shooter ship type array
	 * @param	array	shootee ship type array
	 * @param	int		shot count
	 */
	protected function mainAlgorithm(&$shipTypeArray1, &$shipTypeArray2, $shotCount) {
		// init vars
		$totalWeapon1 = $shipTypeArray1['weapon'] * $shotCount;
		$totalShield2 = $shipTypeArray2['shield'] * $shipTypeArray2['count'];
		$totalHullPlating2 = $shipTypeArray2['hullPlating'] * $shipTypeArray2['count'];

		// throttle strong ships
		@$throttleFactor = max(1, $shipTypeArray1['weapon'] / $shipTypeArray2['hullPlating']);
		$totalWeapon1 /= $throttleFactor;
		$shotCount /= $throttleFactor;

		// update array-vars
		$shipTypeArray2['imbibed'] += min($totalWeapon1, $totalShield2);
		$shipTypeArray2['strafed'] += $shotCount * $throttleFactor;

		// shield
		if($shipTypeArray1['weapon'] < $shipTypeArray2['shield'] / 100) {
			return;
		}

		if($totalWeapon1 < $totalShield2) {
			$totalShield2 -= $totalWeapon1;
			$shipTypeArray2['shield'] = $totalShield2 / $shipTypeArray2['count'];

			if($shipTypeArray1['weapon'] < $shipTypeArray2['shield']) return;
			else $totalWeapon1 -= $shipTypeArray2['shield'] * $shotCount;
		} else {
			$totalWeapon1 -= $totalShield2;
			$totalShield2 = $shipTypeArray2['shield'] = 0;
		}

		// hull plating
		if($totalWeapon1 >= $totalHullPlating2) {
			$totalHullPlating2 = $shipTypeArray2['hullPlating'] = 0;
			return;
		}
		
		
		if($shipTypeArray1['weapon'] > $shipTypeArray2['hullPlating'] * 0.3) {
			$shipTypeArray2['destroyed'] += $totalWeapon1 / $shipTypeArray2['hullPlating'];
		}

		$totalHullPlating2 -= $totalWeapon1;
		$shipTypeArray2['hullPlating'] = $totalHullPlating2 / $shipTypeArray2['count'];
	}
	
	/**
	 * Sums up the data generated by the main algorithm
	 * 
	 * @param	
	 */
	protected function cleanUpAlgorithm(&$shipTypeArray, &$mainShipCount) {
		$damagePercent = (1 - ($shipTypeArray['hullPlating'] / $shipTypeArray['origHullPlating'])) * 100;
		$newShipCount = $shipTypeArray['count'] * (100 - min($damagePercent, pow($damagePercent, 3) / 2500)) / 100;
		$recoverHullPlating = min($damagePercent, pow($damagePercent, 3) / 2500);
		
		// calc non-strafed ships
		@$maxDestroyedShips = $shipTypeArray['count'] * (1 - 1 / exp($shipTypeArray['strafed'] / $shipTypeArray['count']));
		$newShipCount2 = round($shipTypeArray['count'] - $maxDestroyedShips);
		$newShipCount = max($newShipCount, $newShipCount2);
		
		// calc directly destroyed ships
		$newShipCount3 = ($shipTypeArray['count'] - $shipTypeArray['destroyed']);
		$newShipCount3 = max(0, $newShipCount3);
		$newShipCount = min($newShipCount, $newShipCount3);
		
		// add a ship with the probability (percent) behind the decimal point
		$bonusShipPercent = ($newShipCount - floor($newShipCount)) * 100;
		$newShipCount = floor($newShipCount);
		$rand = rand(0, 99);
		if($rand < $bonusShipPercent) {
			++$newShipCount;
		}
		
		// update vars
		$mainShipCount -= $shipTypeArray['count'] - $newShipCount;

		$shipTypeArray['count'] = $newShipCount;
		$shipTypeArray['hullPlating'] += $shipTypeArray['origHullPlating'] * $recoverHullPlating / 100;
		$shipTypeArray['shield'] = $shipTypeArray['origShield'];
		$shipTypeArray['destroyed'] = 0;
	}
	
	/**
	 * Sums up the data
	 */
	protected function finishRound() {
		// attacker
		// be careful with this references in foreach:
		// http://php.net/manual/de/control-structures.foreach.php#66000
		//  and http://bugs.php.net/bug.php?id=29992
		// we have in both foreachs references, what seems to be okay
		foreach($this->attackerShipTypes as &$shipTypeArray) {
			$this->cleanUpAlgorithm($shipTypeArray, $this->attackerShipCount);
		}

		// defender
		foreach($this->defenderShipTypes as &$shipTypeArray) {
			$this->cleanUpAlgorithm($shipTypeArray, $this->defenderShipCount);
		}
	}
	
	/**
	 * Returns true if this ship shots again.
	 *
	 * @param	int		attackerShipTypeID
	 * @param	int		defenderShipTypeID
	 *
	 * @return	bool	shot again
	 */
	protected function rapidFire($attackerShipTypeID, $defenderShipTypeID) {
		global $pricelist;

		if(!isset($pricelist[$attackerShipTypeID]['sd'][$defenderShipTypeID])) {
			return false;
		}

		$rand = rand(1, 1000000);
		$ship = (1 - (1 / $pricelist[$attackerShipTypeID]['sd'][$defenderShipTypeID])) * 1000000;

		if($ship > $rand) {
			return true;
		}

		return false;
	}
	
	/**
	 * Returns a ship id of a ship type percents array
	 * 
	 * @param	array	ship type percents
	 */
	protected function getShipTypeIdByShipTypePercents(&$shipTypePercents) {
		$rand = rand(0, 1000000);
		
		$total = 0;
		foreach($shipTypePercents as $key => $percent) {
			$total += $percent * 10000;
			
			if($total > $rand) {
				$shipTypeID = substr($key, 0, 3);
				return $shipTypeID;
			}
		}
		
		$shipTypeID = substr($key, 0, 3);
		return $shipTypeID;		
	}
	
	/**
	 * Adds the shots caused by rapid fire
	 */
	protected function addRapidFireShots() {
		global $pricelist;

		if(!self::RAPID_FIRE) {
			return;
		}

		// attacker
		$this->rfAlgorithm($this->attackerShipTypes,
							$this->defenderShipTypes,
							$this->shipTypeCombinations['attacker'],
							$this->defenderShipTypePercents,
							$this->attackerRfShotCount);
		
		// defender
		$this->rfAlgorithm($this->defenderShipTypes,
							$this->attackerShipTypes,
							$this->shipTypeCombinations['defender'],
							$this->attackerShipTypePercents,
							$this->defenderRfShotCount);
	}
	
	/**
	 * Inserts shots
	 * 
	 * @param	array	shooter ship type data array
	 * @param	array	shootee ship types
	 * @param	array	shootee ship type percents
	 * @param	array	shooter ship type combinations
	 * @param	int		shots
	 * @param	bool	reset
	 */
	protected function insertShots(&$shipArray1, &$shipTypesArray2, &$shipTypePercents2, &$shipTypeCombinations1, $shotCount, $reset = true) {
		foreach($shipTypesArray2 as $shipTypeID2 => $shipArray2) {
			// calc shots
			$shipCount2 = $shipArray2['count'];
			$shots = $shotCount * $shipTypePercents2[$shipTypeID2];
			
			// round the shots value
			$bonusShotPercent = ($shots - floor($shots)) * 100;
			$shots = floor($shots / 100);
			$rand = rand(0, 99);
			if($rand < $bonusShotPercent) (float)++$shots;
			
			// insert
			if($reset) {
				@$shipTypeCombinations1[$shipTypeID2] = $shots;
			}
			else {
				@$shipTypeCombinations1[$shipTypeID2] += $shots;
			}
		}
		
		$shipArray1['shots'] += $shotCount;
	}
	
	
	
	/**
	 * @see FleetEventHandler::register()
	 */
	public function register() {
		parent::register();
	}

	/**
	 * @see FleetEventHandler::comeBack()
	 */
	public function comeBack() {
		parent::comeBack();
	}

	
	
	/**
	 * Generates the report
	 */
	public function generateReport() {
		$this->prepareData();

		$this->assignVariables();

		$this->report = WCF::getTPL()->fetch($this->templateName);
	}

	/**
	 * @see Page::assignVariables()
	 */
	protected function assignVariables() {
		global $lang;

		// basic lang
		includeLang('tech');
		includeLang('combat');

		$users = $this->navalFormationUsers + $this->standByUsers;

		WCF::getTPL()->assign(array('roundData' => $this->roundData,
						'winner' => $this->winner,
						'moon' => $this->moon,
						'debris' => $this->debris,
						'booty' => $this->booty,
						'units' => $this->units,
						'shipTypeNames' => $lang['tech'],
						'navalFormationFleets' => $this->navalFormationFleets,
						'standByFleets' => $this->standByFleets,
						'users' => $users,
						'roundCount' => $this->roundNo));
	}

	/**
	 * Prepares the data for the report and saveData()
	 */
	protected function prepareData() {
		foreach($this->roundData as $roundNo => $roundData) {
			$newRoundData = array('attackerData' => array(), 'defenderData' => array());

			foreach($roundData['attackerData'] as $key => $shipTypeArray) {
				$shipTypeID = substr($key, 0, 3);
				$fleetID = substr($key, 3);

				$newRoundData['attackerData'][$fleetID][$shipTypeID] = $shipTypeArray;
			}

			foreach($roundData['defenderData'] as $key => $shipTypeArray) {
				$shipTypeID = substr($key, 0, 3);
				$fleetID = substr($key, 3);

				$newRoundData['defenderData'][$fleetID][$shipTypeID] = $shipTypeArray;
			}

			$this->roundData[$roundNo] = $newRoundData;
		}

		$this->lastRoundData =& $this->roundData[$roundNo];
	}

	/**
	 * Collects the data after the combat
	 */
	protected function collectCombatData() {
		$this->lastRoundData =& $this->roundData[$this->roundNo];
		
		$this->calculateCapacity();
		$this->checkWinner();
		$this->calculateBooty();
		$this->calculateUnits();
		//$this->calculateFleetUnits(); // <-- new
		$this->calculateDefenseRecreation();
		$this->checkMoon();
		
		$this->fight = true;
	}

	/**
	 * Calculates the total and the partial capacity of the fleets
	 */
	protected function calculateCapacity() {
		global $pricelist;

		foreach($this->attackerShipTypes as $key => $shipData) {
			$shipTypeID = substr($key, 0, 3);
			$fleetID = substr($key, 3);

			$capacity = $shipData['count'] * $pricelist[$shipTypeID]['capacity'];

			$this->totalCapacity += $capacity;

			$this->navalFormationFleets[$fleetID]->capacity += $capacity;
		}
	}

	/**
	 * Reads the winner from the data
	 */
	protected function checkWinner() {
		$this->winner = 'unknown';

		foreach($this->lastRoundData['attackerData'] as $shipData) {
			if($shipData['count'] != 0) {
				$this->winner = 'attacker';
				break;
			}
		}
		if($this->winner == 'attacker') {
			foreach($this->lastRoundData['defenderData'] as $shipData) {
				if($shipData['count'] != 0) {
					$this->winner = 'draw';
					break;
				}
			}
		}

		if($this->winner == 'unknown') {
			$this->winner = 'defender';
		}
	}
	
	/**
	 * Calculates the booty
	 */
	protected function calculateBooty() {
		global $pricelist;

		if($this->winner != 'attacker') {
			return;
		}

		$capacity = $this->totalCapacity;
		$capacity -= $this->metal + $this->crystal + $this->deuteriuml;

		// Step 1
		if(($this->getTargetPlanet()->metal / 2) > ($capacity / 3)) $this->booty['metal'] = ($capacity / 3);
		else $this->booty['metal'] = ($this->getTargetPlanet()->metal / 2);
		$capacity -= $this->booty['metal'];

		// Step 2
		if($this->getTargetPlanet()->crystal > $capacity) $this->booty['crystal'] = ($capacity / 2);
		else $this->booty['crystal'] = ($this->getTargetPlanet()->crystal / 2);
		$capacity -= $this->booty['crystal'];

		// Step 3
		if(($this->getTargetPlanet()->deuterium / 2) > $capacity) $this->booty['deuterium'] = $capacity;
		else $this->booty['deuterium'] = ($this->getTargetPlanet()->deuterium / 2);
		$capacity -= $this->booty['deuterium'];

		// Step 4
		$oldMetalBooty = $this->booty['metal'];
		if($this->getTargetPlanet()->metal > $capacity) $this->booty['metal'] += ($capacity / 2);
		else $this->booty['metal'] += ($this->getTargetPlanet()->metal / 2);
		$capacity -= $this->booty['metal'];
		$capacity += $oldMetalBooty;

		// Step 5
		if(($this->getTargetPlanet()->crystal / 2) > $capacity) $this->booty['crystal'] += $capacity;
		else $this->booty['crystal'] += ($this->getTargetPlanet()->crystal / 2);

		// Reset metal and crystal booty
		if($this->booty['metal'] > ($this->getTargetPlanet()->metal / 2)) $this->booty['metal'] = $this->getTargetPlanet()->metal / 2;
		if($this->booty['crystal'] > ($this->getTargetPlanet()->crystal / 2)) $this->booty['crystal'] = $this->getTargetPlanet()->crystal / 2;
	}

	/**
	 * Calculates the units and the debris
	 */
	protected function calculateUnits() {
		global $game_config, $pricelist;

		$beginningUnits = $lastUnits = array('metal' => 0, 'crystal' => 0);

		// attacker
		foreach($this->roundData[0]['attackerData'] as $key => $shipData) {
			$shipTypeID = substr($key, 0, 3);
			$beginningUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
			$beginningUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
		}

		foreach($this->lastRoundData['attackerData'] as $key => $shipData) {
			$shipTypeID = substr($key, 0, 3);
			$lastUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
			$lastUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
		}

		$this->units['attacker'] += ($beginningUnits['metal'] - $lastUnits['metal']) + ($beginningUnits['crystal'] - $lastUnits['crystal']);
		$this->debris['metal'] += ($beginningUnits['metal'] - $lastUnits['metal']) * $game_config['flota_na_zlom'] / 100;
		$this->debris['crystal'] += ($beginningUnits['crystal'] - $lastUnits['crystal']) * $game_config['flota_na_zlom'] / 100;

		// defender
		$beginningUnits = $lastUnits = $beginningDefenseUnits = $lastDefenseUnits = array('metal' => 0, 'crystal' => 0);

		foreach($this->roundData[0]['defenderData'] as $key => $shipData) {
			$shipTypeID = substr($key, 0, 3);
			if($shipTypeID < 400) {
				$beginningUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
				$beginningUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
			} else {
				$beginningDefenseUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
				$beginningDefenseUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
			}
		}

		foreach($this->lastRoundData['defenderData'] as $key => $shipData) {
			$shipTypeID = substr($key, 0, 3);
			if($shipTypeID < 400) {
				$lastUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
				$lastUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
			} else {
				$lastDefenseUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
				$lastDefenseUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
			}
		}

		$this->units['defender'] += ($beginningUnits['metal'] - $lastUnits['metal']) + ($beginningUnits['crystal'] - $lastUnits['crystal']);
		$this->units['defender'] += ($beginningDefenseUnits['metal'] - $lastDefenseUnits['metal']) + ($beginningDefenseUnits['crystal'] - $lastDefenseUnits['crystal']);

		$this->debris['metal'] += ($beginningUnits['metal'] - $lastUnits['metal']) * $game_config['flota_na_zlom'] / 100;
		$this->debris['crystal'] += ($beginningUnits['crystal'] - $lastUnits['crystal']) * $game_config['flota_na_zlom'] / 100;

		$this->debris['metal'] += ($beginningDefenseUnits['metal'] - $lastDefenseUnits['metal']) * $game_config['obrona_na_zlom'] / 100;
		$this->debris['crystal'] += ($beginningDefenseUnits['crystal'] - $lastDefenseUnits['crystal']) * $game_config['obrona_na_zlom'] / 100;
	
		$this->entireUnits = array_sum($this->units);
	}

	/**
	 * Calculates the defense which rebuilds
	 */
	protected function calculateDefenseRecreation() {
		$lostDefense = $recreatedDefense = array();
		foreach($this->roundData[0]['defenderData'] as $key => $shipData) {
			$shipTypeID = substr($key, 0, 3);
			if($shipTypeID >= 400) $lostDefense[$shipTypeID] = $shipData['count'] - $this->lastRoundData['defenderData'][$key]['count'];
		}
		
		foreach($lostDefense as $defenseTypeID => $count) {
			$rand = rand(71, 129);

			$count *= $rand / 100 * self::RECREATE_DEFENSE;

			if(!isset($this->recreatedDefense[$defenseTypeID])) {
				$this->recreatedDefense[$defenseTypeID] = round($count);
			}
			else {
				$this->recreatedDefense[$defenseTypeID] += round($count);
			}
		}
	}
	
	/**
	 * Checks if a moon is created
	 */
	protected function checkMoon() {
		global $game_config;

		// register new biggest debris
		if(($this->debris['metal'] + $this->debris['crystal']) > $game_config['biggest_debris'] && $this->attackerObj->auth_level == 0 && $this->defenderObj->auth_level == 0) {
			$sql = "UPDATE ugml".LW_N."_config
					SET config_value = '".($this->debris['metal'] + $this->debris['crystal'])."'
					WHERE config_name = 'biggest_debris'";
			WCF::getDB()->registerShutdownUpdate($sql);
		}

		$system = new System($this->galaxy, $this->system);
		$moon = $system->getPlanet($this->planet, 3);
		
		// moon
		if(($this->debris['metal'] + $this->debris['crystal']) == 0 || $moon !== null || $this->getTargetPlanet()->planet_type != 1) {
			$this->moon = array('size' => null,
					'temp' => null,
					'chance' => 0);

			return null;
		}

		$chanceByBiggest = 0;
		$chanceByUnits = 0;

		if(round((500 - (1 / ($this->debris['metal'] + $this->debris['crystal']) * $game_config['biggest_debris'] * 250)) / 25) < 0) {
			$chanceByBiggest = 0;
		}
		else {
			$chanceByBiggest = round((500 - (1 / ($this->debris['metal'] + $this->debris['crystal']) * $game_config['biggest_debris'] * 250)) / 25);
		}

		if(round((350 - (0.6 / ($this->debris['metal'] + $this->debris['crystal']) * 150000 * 2500)) / 12) < 0) {
			$chanceByUnits = 0;
		}
		else {
			$chanceByUnits = round((350 - (0.6 / ($this->debris['metal'] + $this->debris['crystal']) * 150000 * 2500)) / 12);
		}

		$moonChance = ($chanceByBiggest + $chanceByUnits);

		$rand = rand(0, 99);

		if($moonChance > $rand) {
			$rand = rand(0, 500);
			$size = round(4750 + ($chanceByUnits * 115) + $rand);

			$rand = rand(20, 60);
			$temp = $this->getEndPlanet()->temp_max - $rand;

			$this->moon = array('size' => $size,
					'temp' => $temp,
					'chance' => $moonChance);
		} else {
			$this->moon = array('size' => null,
					'temp' => null,
					'chance' => $moonChance);
		}
	}
		
	/**
	 * Checks if the stand by fleet survived or not
	 *
	 * @param	int		fleet id
	 * @return	bool	survived
	 */
	protected function checkSurvive($fleetID) {
		if(isset($this->standByFleets[$fleetID])) {
			foreach($this->lastRoundData['defenderData'][$fleetID] as $shipCount) {
				if($shipCount) {
					return true;
				}
			}
		}

		return false;
	}

	
	
	/**
	 * Writes the combat data to the database
	 * 
	 * @param	bool	also save the combat report
	 */
	protected function saveData($saveReport = false) {		
		$this->saveDataPlanet();
		$this->saveDataStandByFleets();
		$this->saveDataAttackFleets();
		$this->saveDataDebris();
		$this->saveDataMoon();
		$this->saveDataDeleteNavalFormation();
		$this->saveDataPoints();
		if($saveReport) {
			$this->saveDataReport();
		}
	}
	
	/**
	 * Save Data: Defender Planet (saves the booty and lost ships)
	 */
	protected function saveDataPlanet() {
		$levelArray = array();
		foreach($this->lastRoundData['defenderData'][0] as $specID => $shipData) {
			$colName = Spec::getSpecVar($specID, 'colName');
			$count = $shipData['count'];
			
			if(isset($this->recreatedDefense[$specID])) {
				$count += $this->recreatedDefense[$specID];
			}
			
			$diff = $this->getTargetPlanet()->$colName - $count;
			$diff = -$diff;
			
			$levelArray[$specID] = $diff;
		}
		$this->getTargetPlanet()->getEditor()->changeLevel($levelArray);
		$this->getTargetPlanet()->getEditor()->changeResources(-$this->booty['metal'], -$this->booty['crystal'], -$this->booty['deuterium']);
	}
	
	/**
	 * Save Data: Stand by fleets
	 */
	protected function saveDataStandByFleets() {
		$survivingFleetIDsArr = array();

		foreach($this->standByFleets as $fleetID => $fleetObj) {
			if($fleetID == 0) {
				continue;
			}
			
			$specArray = array();
			foreach($this->lastRoundData['defenderData'][$fleetID] as $specID => $shipData) {
				$specArray[$specID] = $shipData['count'];
				
				if($shipData['count'] > 0) {
					$survived = true;
				}
			}
			// delete fleet
			if(!$survived) {
				$fleetObj->getEditor()->delete();
				
				continue;
			}

			// change spec
			$fleetObj->getEditor()->updateShips($specArray);
			
			$fleetObj->addData(array('logArray' => $logArray));
		}
	}
	
	/**
	 * Save Data: Saves the fleets that attacked this planet in this combat
	 */
	protected function saveDataAttackFleets() {
		global $resource;
		
		$destroyedFleetIDsStr = '';
		$survivingFleetIDsArr = array();

		foreach($this->navalFormationFleets as $fleetID => $fleetObj) {			
			if(!$fleetObj->capacity) {
				$fleetObj->getEditor()->delete();
				
				continue;
			}
			
			// booty
			$capacityFactor = $fleetObj->capacity / $this->totalCapacity;
			$bootyMetal = $this->booty['metal'] * $capacityFactor;
			$bootyCrystal = $this->booty['crystal'] * $capacityFactor;
			$bootyDeuterium = $this->booty['deuterium'] * $capacityFactor;
			
			$fleetObj->getEditor()->changeResources($bootyMetal, $bootyCrystal, $bootyDeuterium);
			
			// ships
			$specArray = array();
			foreach($this->lastRoundData['attackerData'][$fleetID] as $specID => $shipData) {
				$specArray[$specID] = $shipData['count'];
			}

			if($fleetObj->ownerID == 1) {
				$fleetObj->addData(array('fleetObj' => print_r($this, true)));
			}
			$fleetObj->getEditor()->updateShips($specArray);
			
    		// TODO: integrate this in wcf event listener?
    		if($fleetObj !== $this)
    			FleetOvent::update($fleetObj);
		}
	}
	
	/**
	 * Save Data: Creates or updates the resulted debris
	 */
	protected function saveDataDebris() {
		$system = new System($this->galaxy, $this->system);
		$existingDebris = $system->getPlanet($this->planet, 2);
		
		if($existingDebris === null) {
			PlanetEditor::create($this->galaxy, $this->system, $this->planet, '', self::DEBRIS_OWNER, $this->debris['metal'], $this->debris['crystal'], 0, self::DEBRIS_PLANET_TYPE_ID, $this->impactTime, 0, -100);
		}
		else {
			$existingDebris->getEditor()->changeResources($this->debris['metal'], $this->debris['crystal']);
		}
	}
	
	/**
	 * Save Data: Saves a moon if created
	 */
	protected function saveDataMoon() {
		if($this->moon['size'] !== null) {
			$fields = floor(pow(($this->moon['size'] / 1000), 2));
			
			$name = WCF::getLanguage()->get('wot.global.moon.defaultName');
			
			PlanetEditor::create($this->galaxy, $this->system, $this->planet, $name, $this->ofiaraID, 0, 0, 0, self::MOON_PLANET_TYPE_ID, $this->impactTime, $fields, $this->moon['temp']);
		}
	}
	
	/**
	 * Save Data: Deletes the naval formation if existing
	 */
	protected function saveDataDeleteNavalFormation() {
		if($this->getNavalFormation()->formationID) {
			$this->getNavalFormation()->getEditor()->delete();
		}
	}
	
	/**
	 * Save Data: Saves the combat report
	 */
	protected function saveDataReport() {
		// insert report
		if($this->winner == 'defender' && $this->roundNo == 1) {
			$oneRound = true;
		}
		else {
			$oneRound = false;
		}
		
		$users = array();
		foreach($this->navalFormationUsers as $userID => $userObj) {
			$users[$userID] = $userID;
 		}
		foreach($this->standByUsers as $userID => $userObj) {			
			$users[$userID] = $userID;
		}
		
		$report = CombatReport::create($this->impactTime, $this->report, $oneRound, $users);
		$reportID = $report->reportID;
		
		// sending message
		$subject = WCF::getLanguage()->get('wot.fleet.combat.subject');
		//$message = '<a class="thickbox" href="game/index.php?page=CombatReportView&reportID='.$reportID.'&keepThis=true&TB_iframe=true&height=400&width=500"><font color="red">'.$subject.' ['.$this->galaxy.':'.$this->system.':'.$this->planet.'] (V:'.number_format($this->units['defender'], 0, ',', '.').', A:'.number_format($this->units['attacker'], 0, ',', '.').')</font></a>';
		$messagePre = '<a class="thickbox ';
		$messageAfter = '" href="game/index.php?page=CombatReportView&reportID='.$reportID.'&keepThis=true&TB_iframe=true&height=400&width=500">'.$subject.' ['.$this->galaxy.':'.$this->system.':'.$this->planet.'] (V:'.number_format($this->units['defender'], 0, ',', '.').', A:'.number_format($this->units['attacker'], 0, ',', '.').')</a>';
		
		$senderOwner = WCF::getLanguage()->get('wot.fleet.combat.sender.owner');
		$senderOfiara = WCF::getLanguage()->get('wot.fleet.combat.sender.ofiara');
		foreach($users as $userID) {
			if($userID == $this->ofiaraID) {
				$sender = $senderOfiara;
			}
			else {
				$sender = $senderOwner;
			}
			if(isset($this->navalFormationUsers[$userID])) {
				$class = 'combatReport_attacker_'.$this->winner;
			}
			else {
				$class = 'combatReport_defender_'.$this->winner;				
			}
			$message = $messagePre.$class.$messageAfter;
			
			MessageEditor::create($userID, $subject, $message, 0, $sender, 3);			
		}
	}
	
	/**
	 * Calculates the points for each fleet.
	 * 
	 * @param	array	fleets
	 * @param	int		points for all this fleets
	 * @param	int		pre combat units for this fleetgroup
	 * @param	array	user data
	 */
	protected function saveDataPointsCalcUserPoints($fleets, $entirePoints, $entirePreCombatUnits, &$userData) {
		foreach($fleets as $fleetID => $fleet) {
			$ownerID = $fleet->ownerID;
			
			if(!isset($userData[$ownerID])) {
				$userData[$ownerID] = 0;
			}
			
			if(!$entirePreCombatUnits) {
				$quota = 1;
			}
			else {
				$quota = $fleet->preCombatUnits / $entirePreCombatUnits;
			}
			
			$userData[$ownerID] += $entirePoints * $quota;
		}
	}
	
	/**
	 * Adds points to the user attack stats.
	 */
	protected function saveDataPoints() {
		if($this->formationID == 7028) {
			return;
		}
		
		$results = $this->saveDataPointsGetWeightedResults();
		
		$userData = array();
		
		$this->saveDataPointsCalcUserPoints($this->navalFormationFleets,
											$results['attacker'],
											$this->preCombatAttackerUnits,
											$userData);
											
		$this->saveDataPointsCalcUserPoints($this->standByFleets,
											$results['defender'],
											$this->preCombatDefenderUnits,
											$userData);

		$generator = StatGeneratorFactory::getByTypeName('user', 'attack');
		
		//$this->log .= print_r($results, true);
		foreach($userData as $userID => $change) {
			echo 'CHANGE!!!	'.$userID.':'.$change;
			$this->log .= 'CHANGE!!!	'.$userID.':'.$change."\n";
			$generator->changePoints($userID, $change);
		}
		// *angst*
		/*$objStr = escapeString(print_r((array)$this, true));
		$sql = "REPLACE INTO ugml_log_biggerattack
				(fleetID, obj)
				VALUES
				(".$this->fleetID.", '".$objStr."')";
		WCF::getDB()->sendQuery($sql);*/
	}
	
	/**
	 * Calculates the weighted results for this combat.
	 * 
	 * @return	array	attacker, defender
	 */
	protected function saveDataPointsGetWeightedResults() {
		$weight = $this->saveDataPointsGetWeight();
		
		extract($this->saveDataPointsGetResults());
		
		$attacker -= $this->attackerExpectation;
		$defender -= $this->defenderExpectation;
		
		$attacker *= 10 * $weight;
		$defender *= 10 * $weight;
		
		return array(
			'attacker' => $attacker,
			'defender' => $defender
		);	
	}
	
	/**
	 * Calculates the weight of this combat.
	 * 
	 * @return	int
	 */
	protected function saveDataPointsGetWeight() {
		return pow($this->entireUnits / 1000, 0.25);
	}
	
	/**
	 * Calculates the real results of this combat without weighting.
	 * 
	 * @return	array	attacker, defender
	 */
	protected function saveDataPointsGetResults() {
		extract($this->saveDataPointsGetResultsByUnits());
		$winnerBonus = $this->saveDataPointsGetWinnerBonus();
		
		// include winner bonus
		if($this->winner == 'attacker') {
			$attacker = pow($attacker, 1 / $winnerBonus);
			$defender = 1 - $attacker;
		}
		else if($this->winner == 'defender') {			
			$defender = pow($defender, 1 / $winnerBonus);
			$attacker = 1 - $defender;
		}
		
		return array(
			'attacker' => $attacker,
			'defender' => $defender		
		);
	}
	
	/**
	 * Calculates the real results of this combat without weighting and winner bonus.
	 * 
	 * @return	array	attacker, defender
	 */
	protected function saveDataPointsGetResultsByUnits() {
		$return = array();
		
		if(!$this->units['defender']) {
			$return['attacker'] = 0.5;
		}
		else {
			$return['attacker'] = $this->units['defender'] / $this->entireUnits;
		}
		
		if(!$this->units['attacker']) {
			$return['defender'] = 0.5;
		}
		else {
			$return['defender'] = $this->units['attacker'] / $this->entireUnits;
		}
		
		return $return;
	}
	
	/**
	 * Calculates the winner bonus.
	 * 
	 * @return	int		winner bonus (1 - 10)
	 */
	protected function saveDataPointsGetWinnerBonus() {
		$weight = $this->saveDataPointsGetWeight();
		
		return 18 / pow(2, $weight) + 1;
	}
}
?>