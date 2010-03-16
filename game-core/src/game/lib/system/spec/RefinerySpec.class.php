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

require_once(LW_DIR.'lib/system/spec/ProductionSpec.class.php');
/**
 * Holds extended functions for fleet specifications.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class RefinerySpec extends Spec implements ProductionSpec {	
	/**
	 * @see ProductionSpec::getProduction()
	 */
	public function getProduction(ResourceProduction $prodObj) {
		global $game_config;
		
		$planet = $prodObj->getPlanet();
		$energyProduction = $prodObj->getProduction('energy');		
		
		$unusedEnergy = $energyProduction[0] - $energyProduction[1];
		$unusedEnergy *= 3600;
		$unusedEnergy = min($unusedEnergy, 260000);
		
		if($unusedEnergy > 0) {			
			$bonus = pow(pow(1.03, $planet->refinery) * (1 / (15 * pow(1.01, ($unusedEnergy / 2600))) * $unusedEnergy), (0.5 + pow(1.005, $planet->refinery) - 1)) / (20 * pow(0.995, $planet->refinery)) + $planet->getOwner()->energy_tech * 0.15 * (1 - pow(0, $unusedEnergy));
			$bonus /= 100;
			
			$resourceType = $planet->refineryProduction;
			$currentProduction = $prodObj->getProduction($resourceType);
			
			return array(
				$resourceType => $bonus * $currentProduction / $game_config['resource_multiplier'] * 3600
			);
		}
		return array();
	}
}
?>