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
 * Destroys a moon and redirects the fleets. Before that, the will be
 * 	combat simulated.
 * 
 * @author	Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class DestroyFleet extends NavalFormationAttackFleet {
	protected $missionID = 5;
	public $eventName = 'Nothing';
	
	public $destroyMoon = false;
	public $destroyFleet = false;
	
	protected $afterFightFleet = array();
	
	const RIP = 214;
	
	/**
	 * @see AbstractFleetEventHandler::execute()
	 */
    public function executeImpact() {
    	// combat
    	$this->simulate();
    	$this->generateReport();
    	
    	// destroy
    	if($this->lastRoundData['attackerData'][$this->fleetID][self::RIP]['count'] > 0) {
    		$this->tryDestroy();
    	}
    	else {
    		$this->eventName = 'NoTry';
    	}
    	
    	$this->saveData(true);
    }
	
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		$rips = isset($fleetQueue->ships[self::RIP]);
		$foreignPlanet = ($fleetQueue->getTargetPlanet()->id_owner != WCF::getUser()->userID);
		
		if($rips && $foreignPlanet) {
			return true;
		}
		
		return false;
	}

    /**
     * Calc the results of the try to destroy the moon
     */
    protected function tryDestroy() {
    	$deathStars = $this->navalFormationFleets[$this->fleetID]->fleet[self::RIP];
    	$diameter = $this->getTargetPlanet()->diameter;
    	
    	// destroy moon
    	// (100 - sqrt(<DIAMETER>)) * sqrt(<DEATH_START_COUNT>)
    	$destroyMoonChance = (100 - sqrt($diameter)) * sqrt($deathStars);
    	$rand = rand(0, 100);
    	if($destroyMoonChance > $rand) {
    		$this->destroyMoon = true;
    		$this->eventName = 'Moon';
    	}
    	
    	// destroy fleet
    	// sqrt(<DIAMETER>)
    	$destroyFleetChance = sqrt($diameter);
    	$rand = rand(0, 100);
    	
    	if($destroyFleetChance > $rand) {
    		$this->destroyFleet = true;
    		$this->eventName = 'Fleet';
    	}
    	
    	if($this->destroyFleet) {
    		$this->destroyMoon = false;
    	}
    }
    
	/**
	 * @see NavalFormationAttackFleet::saveData()
	 */
	protected function saveData($saveReport = false) {
		$this->saveDataPlanet();
		$this->saveDataStandByFleets();
		$this->saveDataAttackFleets();
		$this->saveDataDebris();
		$this->saveDataPoints();
		if($saveReport) {
			$this->saveDataReport();
		}
		$this->saveDataLog();
	}
	
	/**
	 * @see NavalFormationAttackFleet::saveDataPlanet()
	 */
	protected function saveDataPlanet() {
		if($this->destroyMoon) {
			$this->redirectFleets();
			
			$this->getTargetPlanet()->getEditor()->delete();
    		return;
		}
		
		parent::saveDataPlanet();
	}
	
	/**
	 * @see NavalFormationAttackFleet::saveDataAttackFleets()
	 */
	protected function saveDataAttackFleets() {
		global $resource;
		
		if($this->destroyFleet) {
			$this->afterFightFleet = $this->navalFormationFleets[$this->fleetID]->fleet;
			$this->getEditor()->delete();
			
			return;
		}
		parent::saveDataAttackFleets();
	}
	
	/**
	 * @see NavalFormationAttackFleet::saveDataDebris()
	 */
	protected function saveDataDebris() {
		if($this->destroyFleet) {
			$this->calculateDebrisOfDestroyedFleet();
		}
		
		parent::saveDataDebris();
	}
	
	/**
	 * Calculates the debris of the destroyed fleet
	 */
	protected function calculateDebrisOfDestroyedFleet() {
		//TODO: dont use game_config
		global $game_config;
		
		foreach($this->afterFightFleet as $specID => $shipCount) {
			$this->debris['metal'] += Spec::getSpecVar($specID, 'costsMetal') * $shipCount * $game_config['flota_na_zlom'] / 100;
			$this->debris['crystal'] += Spec::getSpecVar($specID, 'costsCrystal') * $shipCount * $game_config['flota_na_zlom'] / 100;
		}
	}
	
	/**
	 * Save Data: Redirects the fleets
	 */
	protected function redirectFleets() {
    	$sql = "SELECT id
    			FROM ugml_planets
    			WHERE galaxy = ".$this->galaxy."
    				AND system = ".$this->system."
    				AND planet = ".$this->planet."
    				AND planetKind = 1";
    	$row = WCF::getDB()->getFirstRow($sql);
    	$planetPlanetID = $row['id'];
    	
    	$sql = "UPDATE ugml_fleet
    			SET startPlanetID = ".$planetPlanetID."
    			WHERE startPlanetID = ".$this->targetPlanetID;
    	WCF::getDB()->sendQuery($sql);
    	
    	$sql = "UPDATE ugml_fleet
    			SET targetPlanetID = ".$planetPlanetID."
    			WHERE targetPlanetID = ".$this->targetPlanetID;
    	WCF::getDB()->sendQuery($sql);
    	
    	$sql = "SELECT ugml_fleet.*,
					GROUP_CONCAT(
						CONCAT(specID, ',', shipCount) 
						SEPARATOR ';')
					AS fleet
				FROM ugml_fleet
	    		LEFT JOIN ugml_fleet_spec
	    			ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
	    		WHERE ugml_fleet.targetPlanetID = ".$planetPlanetID."
	    			AND missionID = ".$this->missionID."
	    			AND impactTime > ".$this->impactTime."
	    		GROUP BY ugml_fleet.fleetID";
    	$result = WCF::getDB()->sendQuery($sql);
    	
    	while($row = WCF::getDB()->fetchArray($result)) {
    		$fleet = Fleet::getInstance(null, $row);
    		
    		$fleet->getEditor()->cancel();
    	}
	}	

    /**
     * @see AbstractFleetEventHandler::getImpactOfiaraMessageData()
     */
	protected function getImpactOwnerMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.owner'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.destroy'.$this->eventName.'.owner.subject'),
				'text' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.destroy'.$this->eventName.'.owner.text'),
			);
		
		return $messageData;    	
    }
    
    /**
     * @see AbstractFleetEventHandler::getImpactOfiaraMessageData()
     */
	protected function getImpactOfiaraMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.ofiara'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.destroy'.$this->eventName.'.ofiara.subject'),
				'text' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.destroy'.$this->eventName.'.ofiara.text'),
			);
		
		return $messageData;    	
    }
	
	/**
	 * Save Data: Saves the debug log data
	 */
	protected function saveDataLog() {
		$objStr = escapeString(print_r((array)$this, true));
		$sql = "REPLACE INTO ugml_log_biggerattack
				(fleetID, obj)
				VALUES
				(".$this->fleetID.", '".$objStr."')";
		WCF::getDB()->sendQuery($sql);
	}
}
?>