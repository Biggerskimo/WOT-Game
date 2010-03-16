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
 * All classes that provide a connection to a cache should implement this class.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
interface MemCacheInterface {
	/**
	 * Creates a new MemCacheInterface object.
	 * 
	 * @param	array	settings
	 */
	public function __construct($settings = array());
	
	/**
	 * Sets a key to a value.
	 * 
	 * @param	string	key
	 * @param	string	mixed
	 * @param	int		duration
	 */
	public function set($key, $value, $duration = 86400);
	
	/**
	 * Deletes a key and its value.
	 * 
	 * @param	string	key
	 */
	public function delete($key);
	
	/**
	 * Return the value of a value.
	 * 
	 * @param	string	key
	 * @return	mixed	value
	 */
	public function get($key);
	
	/**
	 * Runs a garbage collection. Returns false, if not implemented.
	 */
	public function gc();
	
	/**
	 * Get the current stats. Returns false, if not implemented.
	 * 
	 * @return	mixed	stats
	 */
	public function getStats();
}
?>