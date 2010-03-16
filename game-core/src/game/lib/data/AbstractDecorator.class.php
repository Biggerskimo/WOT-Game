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
 * Provides functions to wrapp other objects.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
abstract class AbstractDecorator {
	/**
	 * Returns the object that should be decorated.
	 *
	 * @return	Object
	 */
	abstract protected function getObject();
	
	/**
	 * Checks the name of a attribute.
	 * 
	 * @param	string
	 */
	private function checkValid($name) {
		if(empty($name)) {
			require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
			throw new SystemException('can not access empty property');
		}		
	}
	
	/**
	 * @param	string	$name
	 * @param	mixed	$value
	 */
	public function __set($name, $value) {
		$this->checkValid($name);
		
		$this->getObject()->$name = $value;
	}
	
	/**
	 * @param	string	$name
	 * @return	mixed
	 */
	public function __get($name) {
		$this->checkValid($name);
		
		return $this->getObject()->$name;
	}
	
	/**
	 * @param	string	$name
	 * @return	boolean
	 */
	public function __isset($name) {
		$this->checkValid($name);
		
		return isset($this->getObject()->$name);
	}
	
	/**
	 * @param	string	$name
	 */
	public function __unset($name) {
		$this->checkValid($name);
		
		unset($this->getObject()->$name);
	}
	
	/**
	 * @param	string	$name
	 * @param	array	$arguments
	 * @return	mixed
	 */
	public function __call($name, $arguments) {
		$this->checkValid($name);
		
		$var = call_user_func_array(array($this->getObject(), $name), $arguments);
		return $var;		
	}
}
?>