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

require_once(LW_DIR.'lib/data/stat/StatException.class.php');

/**
 * This class looks for the right class and returns an object of it.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class StatGeneratorFactory {	
	/**
	 * Reads the cache data.
	 */
	public static function init() {
		WCF::getCache()->addResource('statTypes-'.PACKAGE_ID, WCF_DIR.'cache/cache.statTypes-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderStatTypes.class.php');
	}
	
	/**
	 * This method returns a object when the type and the name are known.
	 * 
	 * @param	string	type
	 * @param	string	name
	 * @return	StatGenerator
	 */
	public static function getByTypeName($type, $name) {
		$typeName = StringUtil::firstCharToUpperCase($type).StringUtil::firstCharToUpperCase($name);
		
		// guard from lfi/rfi
		$cacheEntry = self::checkClassName($typeName);
		
		require_once(LW_DIR.'lib/data/stat/generator/'.$typeName.'StatGenerator.class.php');
		
		$className = $typeName.'StatGenerator';
		
		return new $className($cacheEntry['statTypeID']);
	}
	
	/**
	 * This method returns a object when only the stat type id is known.
	 * 
	 * @param	int		stat type id
	 * @return	StatGenerator
	 */
	public static function getByStatTypeID($statTypeID) {
		self::init();
		
		$cache = WCF::getCache()->get('statTypes-'.PACKAGE_ID, 'byStatTypeID');
		
		return self::getByTypeName($cache[$statTypeID]['type'], $cache[$statTypeID]['name']);
	}
	
	/**
	 * Checks if a configuration is valid.
	 * 
	 * @param	string	type
	 * @param	string	name
	 * @param	bool	check selectable flag
	 * @return	bool
	 */
	public static function checkTypeName($type, $name, $checkSelectable = true) {
		$className = StringUtil::firstCharToUpperCase($type).StringUtil::firstCharToUpperCase($name);
		
		try {
			$cache = self::checkClassName($className);
			if(!$checkSelectable || $cache['selectable']) {
				return true;
			}
			return false;
		}
		catch(StatException $e) {
			return false;
		}
	}
	
	/**
	 * Checks whether a classname is valid or net.
	 * 
	 * @param	string	classname (type + name, e.g. UserPoints)
	 * @return	array	cache entry
	 */
	protected static function checkClassName($className) {
		self::init();
		
		$cache = WCF::getCache()->get('statTypes-'.PACKAGE_ID, 'byTypeName');
		
		if(isset($cache[$className])) {
			return $cache[$className];
		}
		
		throw new StatException('invalid classname given');
	}
}
?>