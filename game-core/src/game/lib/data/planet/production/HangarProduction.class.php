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

require_once(LW_DIR.'lib/data/planet/production/PlanetProduction.class.php');
require_once(LW_DIR.'lib/system/spec/ProductionSpec.class.php');

/**
 * This class is able the calculate and cache the ship-production of the hanger.
 * 
 * @copyright	2007-2010 Lost Worlds <http://lost-worlds.net>
 * @author		Biggerskimo
 */
class HangarProduction implements PlanetProduction {	
	protected $planet = null;
	
	protected $changes = array();
	protected $changed = false;
	
	private $lastBHangarID = "";
	
	/**
	 * @see PlanetProduction::__construct()
	 */
	public function __construct(Planet $planet) {
		$this->setPlanetObj($planet);
	}
	
	/**
	 * @see PlanetProduction::setPlanet()
	 */
	public function setPlanetObj(Planet $planet) {
		$this->planet = $planet;
	}
	
	/**
	 * Returns the current planet.
	 * 
	 * @return	Planet
	 */
	public function getPlanet() {
		return $this->planet;
	}
	
	/**
	 * Returns the overall time to finish all the work to do.
	 * 
	 * @return	int	seconds
	 */
	public function getOverallTime() {
		global $game_config;
		
		$time = 0;
		
		$jobs = explode(';', substr($this->getPlanet()->b_hangar_id, 0, -1));
		
		$stopProduction = false;
		$remainingShips = '';
		
		foreach($jobs as $jobStr) {
			if(empty($jobStr)) {
				continue;
			}
			
			list($specID, $count) = explode(',', $jobStr);
			
			$costs = Spec::getSpecVar($specID, 'costsMetal') + Spec::getSpecVar($specID, 'costsCrystal');
			
			$timePerUnit = ($costs / $game_config['game_speed'])
				* (1 / ($this->getPlanet()->hangar + 1 )) * pow(0.5, $this->getPlanet()->nano_factory)
				* 60 * 60;
				
			$time += $timePerUnit * $count;
		}
		
		return $time - $this->getPlanet()->b_hangar;
	}
	
	/**
	 * @see PlanetProduction::produce()
	 */
	public function produce($time = null) {
		global $game_config;
		
		if($time === null) {
			$time = time();
		}
		
		$timeDiff = $time - $this->getPlanet()->last_update;
		if(is_numeric($this->getPlanet()->b_hangar)) $timeDiff += $this->getPlanet()->b_hangar; // add time of the current produced ship
		
		if($timeDiff || $this->getPlanet()->b_hangar_id != $this->lastBHangarID) {
			$this->changed = true;
		}
		$this->lastBHangarID = $this->getPlanet()->b_hangar_id;
		
		$jobs = explode(';', substr($this->getPlanet()->b_hangar_id, 0, -1));
		
		$stopProduction = false;
		$remainingShips = '';
		
		foreach($jobs as $jobStr) {
			if(empty($jobStr)) {
				continue;
			}
			
			if($stopProduction) {
				$remainingShips .= $jobStr.';';
				
				continue;
			}
			
			list($specID, $count) = explode(',', $jobStr);
			
			// calc built ships
			$costs = Spec::getSpecVar($specID, 'costsMetal') + Spec::getSpecVar($specID, 'costsCrystal');
			
			$timePerUnit = ($costs / $game_config['game_speed'])
				* (1 / ($this->getPlanet()->hangar + 1 )) * pow(0.5, $this->getPlanet()->nano_factory)
				* 60 * 60;
				
			$maxShips = floor($timeDiff / $timePerUnit);
			$builtShips = min($maxShips, $count);
			
			// write built and remaining ships
			if(isset($this->changes[$specID])) {
				$change = $this->checkShipCount($specID, $builtShips);	
				$this->changes[$specID] += $change;				
				//$this->getPlanet()->{Spec::getSpecVar($specID, 'colName')} += $change;
			}
			else {
				$change = $this->checkShipCount($specID, $builtShips);
				$this->changes[$specID] = $change;
				//$this->getPlanet()->{Spec::getSpecVar($specID, 'colName')} += $change;
			}
			
			if($change > 0)
			{
				$this->changed = true;
			}
			$timeDiff -= ($builtShips * $timePerUnit);
			
			// not all ships of this job could be produced; so write remaining ships
			//  and stop following jobs
			if($count > $builtShips) {
				$remainingShips .= $specID.",".($count - $builtShips).";";
				
				$this->getPlanet()->b_hangar = $timeDiff;
				
				$stopProduction = true;
			}
		}
		
		$this->getPlanet()->b_hangar_id = $remainingShips;
		
		if(empty($remainingShips) && $this->getPlanet()->b_hangar) {
			$this->changed = true;
			$this->getPlanet()->b_hangar = 0;
		}
	}

	/**
	 * Checks for missile limits
	 *
	 * @param	int		specid
	 * @param	int		shipcount
	 * @return	int		corrected shipcount
	 */
	protected function checkShipCount($specID, $shipCount) {
		if($specID != 502 && $specID != 503) {
			return $shipCount;
		}

		$maxSlots = $this->getPlanet()->silo * 10;
		$usedSlots = $this->getPlanet()->interceptor_misil + $this->getPlanet()->interplanetary_misil * 2;

		if($specID == 502) {
			$maxShips = $maxSlots - $usedSlots;
			return min($maxShips, $shipCount);
		}
		else {
			$maxShips = ($maxSlots - $usedSlots) / 2;
			return min($maxShips, $shipCount);
		}

	}
	
	/**
	 * @see PlanetProduction::checkChanges()
	 */
	public function checkChanges() {
		if($this->changed) {
		//	echo "ja";
			return true;
		}
		//echo "nein";
		return false;
	}
	
	/**
	 * @see PlanetProduction::getChanges()
	 */
	public function getChanges() {
		$return = array('b_hangar' => array(1), 'b_hangar_id' => array(1));
		foreach($this->changes as $specID => $count) {
			$colName = Spec::getSpecVar($specID, 'colName');
			$return[$colName] = array(2, $count);
			unset($this->changes[$specID]);
		}
		//var_dump($return);
		return $return;
	}
	
	/**
	 * @see PlanetProduction::resetChanges()
	 */
	public function resetChanges() {
		$this->changes = array();
	}
}
?>