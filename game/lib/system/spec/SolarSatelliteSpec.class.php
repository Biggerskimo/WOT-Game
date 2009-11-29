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

require_once(LW_DIR.'lib/system/spec/FleetSpec.class.php');
require_once(LW_DIR.'lib/system/spec/ProductionSpec.class.php');
/**
 * Holds extended functions for fleet specifications.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds
 */
class SolarSatelliteSpec extends FleetSpec implements ProductionSpec {
	/**
	 * @see ProductionSpec::getProduction()
	 */
	public function getProduction(ResourceProduction $prodObj) {
		$planet = $prodObj->getPlanet();
		
		$energyPerSatellite = ($planet->temp_max >> 2) + 20;
		$energyPerSatellite = min($energyPerSatellite, 50);
		
		return array(
			'energy' => $planet->solar_satelit * $energyPerSatellite
		);
	}
}
?>