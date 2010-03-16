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

require_once(LW_DIR.'lib/data/planet/production/ResourceProduction.class.php');

/**
 * All specs that are able to produce resources should implement this interface.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
interface ProductionSpec {
	const SPEC_FLAG = 0x100;
	
	/**
	 * Calculates the production per hour for this planet when production factors are 1.
	 * It should return a array with floats in this style:
	 * Array
	 * (
	 *  [metal] => 17862.214
	 *  ...
	 * )
	 * 
	 * @param	Planet
	 * @return	array
	 */
	public function getProduction(ResourceProduction $prodObj);
}
?>