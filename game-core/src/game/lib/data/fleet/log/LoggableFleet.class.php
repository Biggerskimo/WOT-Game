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

/**
 * All fleets that want to be logged should implement this interface.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
interface LoggableFleet {
	/**
	 * This method should return the database row.
	 * 
	 * The returned array MUST include:
	 * ['fleetID']
	 * ['impactTime']
	 * ['returnTime']
	 * ['startPlanetID']
	 * ['targetPlanetID']
	 * ['missionID']
	 * 
	 * @return	array
	 */
	public function getData();
	
	/**
	 * This method provides access to the internal data array.
	 * 
	 * @param	array
	 */
	public function addData($array);
	
	/**
	 * This method should return the fleet array ($specID => $count)
	 * 
	 * @return	array
	 */
	public function getFleetArray();
}
?>