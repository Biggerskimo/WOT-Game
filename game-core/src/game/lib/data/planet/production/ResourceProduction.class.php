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
 * This class is able the calculate and cache the production.
 * 
 * @copyright	2007-2009 Lost Worlds <http://lost-worlds.net>
 * @author		Biggerskimo
 */
class ResourceProduction implements PlanetProduction {
	protected static $resourceTypes = array('metal' => true, 'crystal' => true, 'deuterium' => true, 'energy' => false);
	
	protected $planet = null;
	protected $changes = array();
	
	protected $producingSpecs = array();
	protected $potentialProduction = array();
	protected $realProduction = array();
	protected $factorialProduction = array();
	protected $limitAchievements = array();	
	protected $nonAssetFactors = array();
	protected $globalProductionFactor = 1;
	
	/**
	 * @see PlanetProduction::__construct()
	 */
	public function __construct(Planet $planet) {
		$this->setPlanetObj($planet);
		
		$this->initArrays();
		
		$this->calcPotentialProduction();
		$this->applyGlobalProductionFactor();
		$this->setLimitAchievements();
	}
	
	public function __destruct() {
	}

	/**
	 * Initializes the attribute arrays.
	 */
	protected function initArrays() {
		foreach(self::$resourceTypes as $resourceType => $isAsset) {
			$this->potentialProduction[$resourceType] = 0;
			$this->limitAchievements[$resourceType] = 0x7FFFFFFF; // maximum timestamp 2 ^ 31 - 1;
			$this->changes[$resourceType] = 0;
			
			if(!$isAsset) {
				$this->nonAssetFactors[$resourceType] = array(0, 0);
			}
		}
	}
	
	/**
	 * Calculates the secondly production without the non-asset-limited factor.
	 */
	protected function calcPotentialProduction() {
		global $game_config;
		
		$this->searchProducingSpecs();
		
		foreach($this->producingSpecs as $specID => $specObj) {
			$production = $specObj->getProduction($this);
			$colName = $specObj->colName;
			
			foreach($production as $resourceType => $perHour) {
				if(is_numeric($perHour)) {
					$factor = $this->planet->{$colName.'_porcent'} / 10;
					$perHour *= $factor * $game_config['resource_multiplier'];
					$perSecond = $perHour / 3600;
					$this->potentialProduction[$resourceType] += $perSecond;
					
					if(isset($this->nonAssetFactors[$resourceType])) {
						if($perSecond > 0) {
							$this->nonAssetFactors[$resourceType][0] += $perSecond;
						}
						else {
							$this->nonAssetFactors[$resourceType][1] -= $perSecond;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Searches in all non-asset resources for their production factors and takes the lowest, or 0.
	 *  This factor will be applied to all asset factors.
	 */
	protected function applyGlobalProductionFactor() {
		// search factor ...
		$lowestFactor = 1;
		
		foreach($this->nonAssetFactors as $resourceType => $array) {
			if($array[1] != 0) {
				$factor = $array[0] / $array[1];
				
				$lowestFactor = min($lowestFactor, $factor);				
			}
			else {
				$lowestFactor = 0;
				break;
			}
		}
		
		$this->globalProductionFactor = $lowestFactor;
		
		// ... and apply it
		foreach(self::$resourceTypes as $resourceType => $isAsset) {
			if($isAsset) {
				$this->realProduction[$resourceType] = $lowestFactor * $this->potentialProduction[$resourceType];
			}
		}
	}
	
	/**
	 * Returns the limits for one resource type.
	 * 
	 * @param	string		$resourceType
	 * @return	array		lower and upper limit
	 */
	public function getLimits($resourceType) {
		return array(0, 1000000 + 500000 * ceil(pow($this->getPlanet()->{$resourceType.'_store'}, 1.6)));
	}
	
	/**
	 * Searches for the significant limit.
	 * 
	 * @param	string		$resourceType
	 * @return	int			lower or upper limit
	 */
	public function getSignificantLimit($resourceType) {
		$limits = $this->getLimits($resourceType);
		
		if($this->realProduction[$resourceType] > 0) {
			return $limits[1];
		}
		return $limits[0];
	}
	
	/**
	 * Calculates all limit achievements.
	 */
	protected function setLimitAchievements() {
		foreach(self::$resourceTypes as $resourceType => $isAsset) {
			if($isAsset) {
				$limits = $this->getLimits($resourceType);
				
				// no production, so no new limit archievement
				if($this->realProduction[$resourceType] == 0) {
					continue;
				}
				
				if($this->realProduction[$resourceType] > 0) {
					$limit = $limits[1];	
					$diff = $limit - $this->getPlanet()->$resourceType;
					$time = $diff / $this->realProduction[$resourceType];
				}
				else {
					$limit = $limits[0];
					$diff = $limit - $this->getPlanet()->$resourceType;					
					$time = $diff / $this->realProduction[$resourceType];
				}
				$this->limitAchievements[$resourceType] = (int)min($this->getPlanet()->last_update + $time, 0x7FFFFFFF);
			}
		}
	}
	
	/**
	 * Searches the specs that produce resources and sorts the list.
	 *  The result will be saved to $producingSpecs.
	 */
	protected function searchProducingSpecs() {
		$this->producingSpecs = Spec::getByAttr('producing');
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
	 * Returns the production until this point. This method can be used in ProductionSpecs to create
	 *  productions that base on others. (the col 'productionOrder' is good for this, too)
	 * If an asset value is request, a float will be returned;
	 *  if non-asset value is requested, an array of $nonAssetFactors will be returned.
	 *  
	 * @param	string		$resourceType
	 * @return	mixed
	 */
	public function getProduction($resourceType) {
		if(self::$resourceTypes[$resourceType] == true) {
			return $this->potentialProduction[$resourceType];
		}
		return $this->nonAssetFactors[$resourceType];
	}
	
	/**
	 * @see PlanetProduction::produce()
	 */
	public function produce($time = null) {		
		if($time === null) {
			$time = time();
		}
		
		foreach(self::$resourceTypes as $resourceType => $isAsset) {
			$this->changes[$resourceType] = 0;
			
			if(!$isAsset)
			{
				$this->planet->{$resourceType."_max"} = $this->nonAssetFactors[$resourceType][0] * 3600;
				$this->planet->{$resourceType."_used"} = $this->nonAssetFactors[$resourceType][1] * 3600;
			}
		}
		
		$goneTime = $time - $this->planet->last_update;
		
		foreach($this->realProduction as $resourceType => $perSecond) {
			if($this->getPlanet()->planetTypeID == 1 && $resourceType == 'metal') $perSecond += 80 / 3600;
			if($this->getPlanet()->planetTypeID == 1 && $resourceType == 'crystal') $perSecond += 40 / 3600;
			if($this->limitAchievements[$resourceType] < $this->planet->last_update) {
				continue;
			}			
			
			$produced = $goneTime * $perSecond;
			
			$this->changes[$resourceType] += $produced;
			
			$limits = $this->getLimits($resourceType);
			$significantLimit = $this->getSignificantLimit($resourceType);
			
			if($perSecond > 0) {
				if($this->getPlanet()->$resourceType + $this->changes[$resourceType] <= $significantLimit) {
					continue;
				}
				$diff = $this->getPlanet()->$resourceType + $this->changes[$resourceType] - $significantLimit;
				$adding = 0.1;
			}
			else {
				if($this->getPlanet()->$resourceType >= $significantLimit) {
					continue;
				}
				$diff = $significantLimit - ($this->getPlanet()->$resourceType + $this->changes[$resourceType]);
				$adding = -0.1;				
			}
			if($produced > $diff) {
				$this->changes[$resourceType] = $significantLimit + $adding - $this->getPlanet()->$resourceType;
			}
		}
	}
	
	/**
	 * @see PlanetProduction::checkChanges()
	 */
	public function checkChanges() {
		foreach(self::$resourceTypes as $resourceType => $isAsset) {
			if($isAsset && $this->changes[$resourceType]) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @see PlanetProduction::getChanges()
	 */
	public function getChanges() {
		$return = array();
		
		foreach(self::$resourceTypes as $resourceType => $isAsset) {
			if($isAsset) {
				$return[$resourceType] = array(2, $this->changes[$resourceType]);
			} else {
				$return[$resourceType."_used"] = array(1);
				$return[$resourceType."_max"] = array(1);
			}
		}
		return $return;
	}
	
	/**
	 * @see PlanetProduction::resetChanges()
	 */
	public function resetChanges() {
		$this->changes = array();
		$this->changed = false;
	}
}
?>