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

require_once(LW_DIR.'lib/system/cache/mem/MemCacheInterface.class.php');
/**
 * Provides access to a memcached cache.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class MemcachedModule implements MemCacheInterface {
	protected $memcached = null;
	
	protected $settings = array('persistID' => 'wot_memcached', 'servers' => 'localhost');
	
	/**
	 * @see MemCacheInterface::__construct()
	 */
	public function __construct($settings = array()) {
		$persistID = isset($settings['persistID']) ? $settings['persistID'] : $this->settings['persistID'];
		
		$this->memcached = new Memcached($persistID);
		
		$serverStr = isset($settings['servers']) ? $settings['servers'] : $this->settings['servers'];
		
		$serverArr = explode(',', $serverStr);
		
		foreach($serverArr as $server) {
			$parts = explode(':', $server);
			
			$host = $parts[0];
			$port = isset($parts[1]) ? $parts[1] : 11211;
			$weight = isset($parts[2]) ? $parts[2] : 50;
			
			$this->getMemcached()->addServer($host, $port, $weight);
		}
	}
	
	/**
	 * Returns the current memcached object.
	 * 
	 * @return	Memcached
	 */
	public function getMemcached() {
		return $this->memcached;
	}
	
	/**
	 * @see MemCacheInterface::set()
	 */
	public function set($key, $value, $duration = 86400) {
		$expiration = time() + $duration;
		return $this->getMemcached()->set($key, $value, $duration);		
	}
	
	/**
	 * @see MemCacheInterface::delete()
	 */
	public function delete($key) {
		return $this->getMemcached()->delete($key);
	}
	
	/**
	 * @see MemCacheInterface::get()
	 */
	public function get($key) {
		return $this->getMemcached()->get($key);		
	}
	
	/**
	 * @see MemCacheInterface::gc()
	 */
	public function gc() {
		return false;
	}
	
	/**
	 * @see MemCacheInterface::getStats()
	 */
	public function getStats() {
		return $this->getMemcached()->getStats();
	}
}
?>