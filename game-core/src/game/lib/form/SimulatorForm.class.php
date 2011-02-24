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

require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(LW_DIR.'lib/data/CombatSimulator.class.php');
require_once(LW_DIR.'lib/data/fleet/EspionageFleet.class.php');
require_once(LW_DIR.'lib/data/fleet/PhantomFleet.class.php');
require_once(LW_DIR.'lib/util/LWUtil.class.php');
/**
 * External Page for combat simulating.
 * 
 * The Post-Data have this format: shipDataRRFFFFFFFFIII or shipDataRRFFFFFFFFTechIII
 * ...............................^123456789012345678901^..^1234567890123456789012345^
 * ...............................^109876543210987654321^..^5432109876543210987654321^
 * RR = nfs slot
 * FFFFFFFF = front(attacker or defender)
 * III = id
 * 
 * @author	Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class SimulatorForm extends AbstractForm {
	const SIMULATIONS = 10;
	
	public $templateName = 'simulator';
	
	protected $shipDataStr = null;
	protected $shipDataArray = array();
	
	protected $attackerFleets = array();
	protected $defenderFleets = array();
	
	protected $attackerFleetObjs = array();
	protected $defenderFleetObjs = array();
	
	protected $planetID = 0;
	public $planetObj = null;
	protected $defenderPlanet = null;
	
	protected $report = null;
	protected $unitsAttackerArray = array();
	protected $unitsDefenderArray = array();
	protected $debrisMetalArray = array();
	protected $debrisCrystalArray = array();
	protected $roundNoArray = array();
	protected $winnerArray = array();
	protected $bootyArray = array();
	protected $debris = array('metal' => 0, 'crystal' => 0);
	protected $units = array('attacker' => 0, 'defender' => 0);
	protected $booty = null;
	protected $minDebris = array('metal' => 0, 'crystal' => 0);
	protected $minUnits = array('attacker' => 0, 'defender' => 0);
	protected $minBooty = null;
	protected $maxDebris = array('metal' => 0, 'crystal' => 0);
	protected $maxUnits = array('attacker' => 0, 'defender' => 0);
	protected $maxBooty = null;
	protected $roundNo = null;
	protected $winner = array();
	
	protected $minSimulator = null;
	protected $maxSimulator = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_GET['planetID'])) $this->planetID = intval($_GET['planetID']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if(isset($_POST['shipData'])) {
			$this->shipDataStr = StringUtil::trim($_POST['shipData']);
			
			$this->shipDataArray = (array)LWUtil::unserialize($this->shipDataStr);
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		if(WCF::getUser()->sim_uses < 500) {
			$sql = "UPDATE ugml_users
					SET sim_uses = sim_uses +1
					WHERE id = ".WCF::getUser()->userID."";
			WCF::getDB()->sendQuery($sql);
			WCF::getUser()->sim_uses += 1;
			WCF::getSession()->resetUserData();
			
			parent::validate();
		
		}
		else {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException("Du hast die erlaubte Simulationsanzahl �berschritten.");
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$this->simulate();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->loadPlanetData();
	}
	
	/**
	 * Reads a report from the database and saves it
	 * 
	 * @return	str		file name
	 */
	protected function getReport() {
		if(empty($this->planetID)) {
			return '';
		}
		
		$this->planetObj = Planet::getInstance($this->planetID);
		
		$reports = EspionageFleet::searchReports(WCF::getUser()->userID, $this->planetID, 1);
		
		$report = end($reports);
		$report = $report['report'];
		$report = str_replace(array('&auml;', '&ouml;', '&uuml;', '&szlig;', '�', '&'), array('ae', 'oe', 'ue', 'ss', 'ss', '&amp;'), $report);
		$report = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<div>".$report.'</div>';
		
		$fileName = FileUtil::getTemporaryFilename('report_');
		$file = new File($fileName);
		$file->write($report);
		$file->close();
		
		return $fileName;
	}
	
	/**
	 * Loads the planet data
	 */
	protected function loadPlanetData() {
		global $resource;
		
		$fileName = $this->getReport();
		
		if($fileName == '') return;
		
		$xmlObj = new XML($fileName);
		
		// fleet
		for($i = 200; $i < 500; ++$i) {
			if(!isset($resource[$i])) continue;
			
			$array = $xmlObj->xpath('//*[@id="'.$i.'"]');
			if(!isset($array[0][0])) continue;
			
			$this->defenderFleets[1]['fleet'][$i] = intval(str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $array[0][0]));
		}
		
		// tech
		for($i = 109; $i <= 111; ++$i) {
			if(!isset($resource[$i])) continue;
			
			$array = $xmlObj->xpath('//*[@id="'.$i.'"]');
			if(!isset($array[0][0])) continue;
			
			$this->defenderFleets[1]['tech'][$i] = intval(str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $array[0][0]));
		}
		
		// own techs
		$this->attackerFleets[1]['tech'][109] = LWCore::getUser()->military_tech;
		$this->attackerFleets[1]['tech'][110] = LWCore::getUser()->defence_tech;
		$this->attackerFleets[1]['tech'][111] = LWCore::getUser()->shield_tech;
	}
	
	/**
	 * Prepares the data for simulation
	 */
	protected function simulate() {
		// organize data
		foreach($this->shipDataArray as $key => $value) {
			if(empty($value)) continue;
			
			$nfsSlotNo = intval(substr($key, 8, 2));
			$front = strtolower(substr($key, 10, 8));
			$isTech = strlen($key) > 21;
			$arrayName = $front.'Fleets';
			
			if($isTech) {
				$techID = substr($key, -3);
				
				$this->{$arrayName}[$nfsSlotNo]['tech'][$techID] = intval($value);
			} else {
				$techID = substr($key, -3);
				
				$this->{$arrayName}[$nfsSlotNo]['fleet'][$techID] = intval($value);
			}
		}
		if(!count($this->attackerFleets) || !count($this->defenderFleets)) return;
				
		// create objs
		foreach($this->attackerFleets as $nfsSlotNo => $fleetData) {
			$fleetStr = LWUtil::serialize($fleetData['fleet']);
			$array = array('fleet' => Spec::arrayToStr($fleetData['fleet']),
					'galaxy' => 0,
					'system' => 0,
					'planet' => 0,
					'ownerID' => 0,
					'targetPlanetID' => 0,
					'fleetID' => 0,
					'weaponTech' => $fleetData['tech'][109],
					'shieldTech' => $fleetData['tech'][110],
					'hullPlatingTech' => $fleetData['tech'][111]);
			

			$this->attackerFleetObjs[$nfsSlotNo] = new PhantomFleet(null, $array);
		}
		$simulatingFleetArray = $array;
		
		foreach($this->defenderFleets as $nfsSlotNo => $fleetData) {
			$fleetStr = LWUtil::serialize($fleetData['fleet']);
			$array = array('fleet' => Spec::arrayToStr($fleetData['fleet']),
					'galaxy' => 0,
					'system' => 0,
					'planet' => 0,
					'ownerID' => 0,
					'targetPlanetID' => 0,
					'fleetID' => 0,
					'weaponTech' => $fleetData['tech'][109],
					'shieldTech' => $fleetData['tech'][110],
					'hullPlatingTech' => $fleetData['tech'][111]);

			$this->defenderFleetObjs[$nfsSlotNo] = new PhantomFleet(null, $array);
		}

		// simulate
		set_time_limit(60);
		for($i = 0; $i < self::SIMULATIONS; ++$i) {
			try {
				ob_start();
				
				$simulator = new CombatSimulator($this->attackerFleetObjs, $this->defenderFleetObjs, $simulatingFleetArray);
				
				$simulator->simulate();
				
				ob_clean();
			} catch(Exception $e) {
				message('Es ist ein fehler bei der Simulation aufgetreten!');
			}
			
			$this->unitsAttackerArray[$i] = $simulator->units['attacker'];
			$this->unitsDefenderArray[$i] = $simulator->units['defender'];
			$this->debrisMetalArray[$i] = $simulator->debris['metal'];
			$this->debrisCrystalArray[$i] = $simulator->debris['crystal'];
			$this->bootyArray[$i] = $simulator->totalCapacity;
			$this->roundNoArray[$i] = $simulator->roundNo;
			$this->winnerArray[$i] = $simulator->winner;
			
			// check extrema
			if($i == 0) {
				//var_dump($simulator);
				$this->minSimulator = $this->maxSimulator = $simulator;
				
				continue;
			}
			
			// real best case: difference in attacker units
			$byAttackerUnits = ($simulator->units['attacker'] < $this->minSimulator->units['attacker']);
			// unreal best case: no difference in attacker units, but more defender units
			$byDefenderUnits = ($simulator->units['attacker'] == $this->minSimulator->units['attacker']) && ($simulator->units['defender'] > $this->minSimulator->units['defender']);
			
			if($byAttackerUnits || $byDefenderUnits) {
				$this->minSimulator = $simulator;
			}
			
			// real worst case: difference in attacker units
			$byAttackerUnits = ($simulator->units['attacker'] > $this->maxSimulator->units['attacker']);
			// unreal worst case: no difference in attacker units, but less defender units
			$byDefenderUnits = ($simulator->units['attacker'] == $this->maxSimulator->units['attacker']) && ($simulator->units['defender'] < $this->maxSimulator->units['defender']);
			
			if($byAttackerUnits || $byDefenderUnits) {
				$this->maxSimulator = $simulator;
			}
		}
		
		$simulator->generateReport();
		$report = CombatReport::create(time(), $simulator->report, false, array(WCF::getUser()->userID => WCF::getUser()->userID));
		$this->reportID = $report->reportID;
		
		// sum up (avg)
		$this->units['attacker'] = array_sum($this->unitsAttackerArray) / self::SIMULATIONS;
		$this->units['defender'] = array_sum($this->unitsDefenderArray) / self::SIMULATIONS;
		$this->debris['metal'] = array_sum($this->debrisMetalArray) / self::SIMULATIONS;
		$this->debris['crystal'] = array_sum($this->debrisCrystalArray) / self::SIMULATIONS;
		$this->booty = array_sum($this->bootyArray) / self::SIMULATIONS;
		$this->roundNo = array_sum($this->roundNoArray) / self::SIMULATIONS;
		
		// sum up (min)
		$this->minUnits['attacker'] = $this->minSimulator->units['attacker'];
		$this->minUnits['defender'] = $this->minSimulator->units['defender'];
		$this->minDebris['metal'] = $this->minSimulator->debris['metal'];
		$this->minDebris['crystal'] = $this->minSimulator->debris['crystal'];
		$this->minBooty = $this->minSimulator->totalCapacity;
		
		// sum up (max)
		$this->maxUnits['attacker'] = $this->maxSimulator->units['attacker'];
		$this->maxUnits['defender'] = $this->maxSimulator->units['defender'];
		$this->maxDebris['metal'] = $this->maxSimulator->debris['metal'];
		$this->maxDebris['crystal'] = $this->maxSimulator->debris['crystal'];
		$this->maxBooty = $this->maxSimulator->totalCapacity;
		
		$this->winner = array_count_values($this->winnerArray);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		global $lang, $resource;
		
		parent::assignVariables();
				
		includeLang('tech');
		includeLang('combat');
		
		WCF::getTPL()->assign(array('resource' => $resource,
				'shipTypeNames' => $lang['tech'],
				'attackerFleets' => $this->attackerFleets,
				'defenderFleets' => $this->defenderFleets,
				'reportID' => $this->reportID,
				'units' => $this->units,
				'debris' => $this->debris,
				'booty' => $this->booty,
				'minUnits' => $this->minUnits,
				'minDebris' => $this->minDebris,
				'minBooty' => $this->minBooty,
				'maxUnits' => $this->maxUnits,
				'maxDebris' => $this->maxDebris,
				'maxBooty' => $this->maxBooty,
				'roundNo' => $this->roundNo,
				'winner' => $this->winner));
	}
}
?>