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
 * Applies the inteplanetary missiles.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class MissileAttackFleet extends AbstractFleetEventHandler implements Mission {
	protected $missionID = 20;
	
	protected $attackerMissilesCount = 0;
	protected $attackerMissileWeapon = 0;
	
	protected $destroyedDefense = array();
	protected $prototypes = array();
	protected $defenderShipCount = 0;
	
	public $report = '';
	
	protected $pdColName = '';
	
	const INTERCEPTOR_MISSILE = 502;
	const INTERPLANETARY_MISSILE = 503;
	
	/**
	 * Logs this object.
	 */
	public function execute($data) {
		parent::execute($data);
		
		$this->addData(array('thispr' => print_r($this, true)));
		FleetLog::update($this);
	}
	
	/**
	 * @see AbstractFleetEventHandler::executeImpact()
	 */
	public function executeImpact() {
		$this->simulate();
		$this->saveData();
		$this->generateReport();
		
		$this->getEditor()->delete();
	}

	/**
	 * Saves the missiles
	 */
	public function __construct($fleetID = null, $row = null) {
		parent::__construct($fleetID, $row);

		$this->attackerMissilesCount = $this->fleet[self::INTERPLANETARY_MISSILE];

	}
	
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		return false;
	}

    /**
     * Calculates the damage.
     */
    public function simulate() {
		$this->readData();
		
		if(count($this->prototypes)) {
			$this->destroyDefense();
		}
    }
    
    /**
     * Creates the data which is needed for simulating.
     */
    protected function readData() {
    	$this->applyInterceptorMissiles();
    	
    	$this->attackerMissileWeapon = Spec::getSpecVar(self::INTERPLANETARY_MISSILE, 'weapon') * (1 + 0.1 * $this->getOwner()->military_tech);
    	
    	$this->pdColName = Spec::getSpecVar($this->primaryDestination, 'colName');
    	
    	$this->createPrototypes();
    }

	/**
	 * Applies the interceptor missiles
	 */
	protected function applyInterceptorMissiles() {
		// interplanetarys eliminate all interceptors
		if($this->attackerMissilesCount >= $this->getTargetPlanet()->interceptor_misil) {
			$this->destroyedDefense[self::INTERCEPTOR_MISSILE] = $this->getTargetPlanet()->interceptor_misil;
			$this->attackerMissilesCount -= $this->getTargetPlanet()->interceptor_misil;
		}
		// or only some of them
		else {
			$this->destroyedDefense[self::INTERCEPTOR_MISSILE] = $this->attackerMissilesCount;
			$this->attackerMissilesCount = 0;
		}    	
	}

	/**
	 * Creates the ship data arrays for each defense type
	 */
	protected function createPrototypes() {
		Spec::storeData($this->getTargetPlanet());
		
		$specs = Spec::getBySpecType(4, false);
		
		foreach($specs as $specID => $specObj) {
			$this->prototypes[$specID] = array(
					'specID' => $specID,
					'count' => $specObj->level,
					'colName' => $specObj->colName,
					'hullPlating' => ($specObj->costsMetal + $specObj->costsMetal) / 10 * (1 + 0.1 * $this->getOfiara()->defence_tech));
			$this->defenderShipCount += $specObj->level;
		}
	}

	/**
	 * Uses the primary destination to get the correct shiptypeid
	 *
	 * @return	array	prototype
	 */
	protected function getNextPrototype() {
		// primary destination
		if(isset($this->prototypes[$this->primaryDestination])) {
			echo 'pdpt';
			return $this->prototypes[$this->primaryDestination];
		}

		// otherwise select another
		echo $this->defenderShipCount;
		$rand = rand(1, $this->defenderShipCount);
		$loopedThroughShips = 0;
		foreach($this->prototypes as $prototype) {
			$loopedThroughShips += $prototype['count'];

			if($loopedThroughShips >= $rand) {
				echo $rand.':'.$prototype['count'].':';
				var_Dump($prototype);
				return $prototype;
			}
		}
	}

	/**
	 * Destroys the defense
	 */
	protected function destroyDefense() {
		do {
			$prototype = $this->getNextPrototype();
			
    		$neededMissiles = $prototype['hullPlating'] / $this->attackerMissileWeapon;
			if($neededMissiles > $this->attackerMissilesCount) {
				return;
			}
			
    		$this->attackerMissilesCount -= $neededMissiles;
    		
			$specID = $prototype['specID'];
			$colName = $prototype['colName'];
			
			if(isset($this->destroyedDefense[$specID])) {
				++$this->destroyedDefense[$specID];
			}
			else {
				$this->destroyedDefense[$specID] = 1;
			}			
			
			var_dump($prototype);
			//--$this->getTargetPlanet()->$colName;
			--$this->defenderShipCount;
			if($this->defenderShipCount == 0) {
				return;
			}
			
			// delete prototype
			if((@$this->getTargetPlanet()->$colName - $this->destroyedDefense[$specID]) <= 0) {
				unset($this->prototypes[$specID]);
			}
		} while($this->attackerMissilesCount > 0);    	
	}

	/**
	 * Saves the data to the db
	 */
	protected function saveData() {
		// defender planet
		$array = array();
		foreach($this->destroyedDefense as $specID => $count) {
			$array[$specID] = -$count;
		}
		$this->getTargetPlanet()->getEditor()->changeLevel($array);
	}
	
	/**
	 * @see	AbstractFleetEventHandler::getImpactOwnerMessageData()
	 */
	public function getImpactOwnerMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.owner'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.destroy'.$this->eventName.'.owner.subject'),
				'text' => $this->report,
			);
		
		return $messageData;
	}
	
	/**
	 * @see	AbstractFleetEventHandler::getImpactOfiaraMessageData()
	 */
	public function getImpactOfiaraMessageData() {
		$messageData =
			array(
				'sender' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.sender.ofiara'),
				'subject' => WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.destroy'.$this->eventName.'.ofiara.subject'),
				'text' => $this->report,
			);
		
		return $messageData;
	}
	
	/**
	 * @see	AbstractFleetEventHandler::getReturnMessageData()
	 */
	public function getReturnMessageData() {
		return null;
	}

    /**
     * Generates the report
     */
    protected function generateReport() {
    	// header
    	$report = '<table>
	    		<tr>
	    			<td class="c" colspan="4">
	    				'.WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.report.header', array('$targetPlanet' => $this->getTargetPlanet(), '$attacker' => $this->getOwner(), '$time' => DateUtil::formatDate(WCF::getLanguage()->get('wot.global.timeFormat'), time()))).'
					</td>
				</tr>
				<tr>
					<td class="c" colspan="4">
						'.WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.report.lostDefense').'
					</td>
				</tr>';

		$startTR = true;
		
		$specs = Spec::getBySpecType(array(4, 51, 52), true);
		foreach($specs as $specID => $specObj) {
			if(empty($this->destroyedDefense[$specID]) && !isset($this->prototypes[$specID])) {
				continue;
			}

			if($startTR) {
				$report .= '<tr>';
			}

			$report .= '<td>'.WCF::getLanguage()->get('wot.spec.spec'.$specID).'</td><td>'.$specObj->level.' (-'.intval($this->destroyedDefense[$specID]).')</td>';

			if(!$startTR) {
				$report .= '</tr>';
			}
			$startTR = !$startTR;
		}
		if(!$startTR) {
			$report .= '<td></td></tr>';
		}

		$report .= '</table>';

		$this->report = $report;
    }
}
?>