<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * All fleets that are able to be displayed as a FleetOvent should implement this class.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds
 */
interface FleetOventFleet {
	/**
	 * Returns the css class name. (attack, transport, ...)
	 * 
	 * @return	string
	 */
	public function getCssClass();
	
	/**
	 * Returns the current fleet state as css class name (flight, return, ...)
	 * 
	 * @return	string	css class
	 */
	public function getFleetState();
	
	/**
	 * Returns additional fleets that should be viewed in the current container.
	 * 
	 * @return	array	fleet ids
	 */
	public function getAdditionalFleets();
	
	/**
	 * Returns the start planet.
	 * 
	 * @return	Planet
	 */
	public function getStartPlanet($updateLastActivity = true);
	
	/**
	 * Returns the target planet.
	 * 
	 * @return	Planet
	 */
	public function getTargetPlanet($updateLastActivity = true);
}
?>