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
 * This class holds functions for connecting with providers of memory caches (memcached, ...)
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class MemCacheHandler {
	protected static $handler = null;
	// TODO: fix that
	protected $module = '';
	protected $settings = array('servers' => 'localhost');
	
	protected $moduleObj = null;
	
	/**
	 * Creates a new mem cache handler object.
	 */
	public function __construct() {
		$this->moduleObj = $this->loadModule($this->module, $this->settings);
	}
	
	/**
	 * Loads a new cache module.
	 * 
	 * @param	string	name
	 * @param	array	settings
	 */
	public function loadModule($name, $settings = array()) {
		require_once(LW_DIR.'lib/system/cache/mem/'.$name.'.class.php');
		return new $name($settings);
	}
	
	/**
	 * Returns an instance of this handler.
	 * 
	 * @return	MemCacheHandler
	 */
	public static function getInstance() {
		if(self::$handler === null) {
			self::$handler = new MemCacheHandler();
		}
		return self::$handler;
	}
	
	/**
	 * Returns the currently loaded module.
	 * 
	 * @return	MemCacheInterface
	 */
	public function getModule() {
		return $this->moduleObj;
	}
	
	/**
	 * Returns the current cache interface.
	 * 
	 * @return	MemCacheInterface
	 */
	public function getCache() {
		return self::getInstance()->getModule();
	}
}
?>