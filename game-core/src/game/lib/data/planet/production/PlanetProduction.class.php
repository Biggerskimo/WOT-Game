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
 * All productors that produce things (resources, specs or something) on planets should implement this interface.
 * 
 * @author		Biggerskimo
 * @copyright	2007-2009 Lost Worlds <http://lost-worlds.net>
 */
interface PlanetProduction {
	/**
	 * Creates a new PlanetProduction object.
	 * 
	 * @param	Planet
	 */
	public function __construct(Planet $planet);
	
	/**
	 * Sets a new planet object.
	 * 
	 * @param	Planet
	 */
	public function setPlanetObj(Planet $planet);
	
	/**
	 * Produces something.
	 */
	public function produce();
	
	/**
	 * Returns if there were changes since last that must be saved in the database.
	 * 
	 * @return	bool	$changes
	 */
	public function checkChanges();
	
	/**
	 * Returns all changes since last visit.
	 * 
	 * @return	array	$changes
	 */
	public function getChanges();
}
?>