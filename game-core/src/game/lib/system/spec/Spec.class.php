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

require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Holds basic functions to administrate the specifications.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class Spec extends DatabaseObject {
	protected static $cache = array();
	protected static $specObjs = array();
	
	protected static $planet = false;
	protected static $user = false;
	protected static $fleet = false;
	
	/**
	 * Creates a new Spec object.
	 * 
	 * @param	spec id
	 */
	public function __construct($specID, $row = null) {
		if($row === null)
			$row = self::$cache['bySpecID'][$specID];
		
		parent::__construct($row);
	}
	
	/**
	 * Stores the planet, user and fleet objects
	 * 
	 * @param	Planet
	 * @param	LWUser
	 * @param	Fleet
	 */
	public static function storeData($planet = null, $user = null, $fleet = false) {		
		if($planet !== null) {
			self::$planet = $planet;
		}
		if($user !== null) {
			self::$user = $user;
		}
		if($fleet !== null) {
			self::$fleet = $fleet;
		}
	}
	
	/**
	 * Cleans the object cache.
	 */
	public static function cleanObjCache() {
		self::$specObjs = array();
	}
	
	/**
	 * Searches for the level of a spec and adds it to the object.
	 * 
	 * @param	Spec	object
	 * @return	Spec	object
	 */
	protected static function addLevel(Spec $specObj) {
		$specID = $specObj->specID;
		$colName = $specObj->colName;
									
		// search in planet obj
		if(self::$planet !== false && self::$planet->$colName != '') {
			$level = self::$planet->$colName;
		}
		// search in user obj
		else if(self::$user !== false && self::$user->$colName != '') {
			$level = self::$user->$colName;
		}
		// search in normalized fleet obj
		else if(self::$fleet !== false && !empty(self::$fleet->fleet[$specID])) {
			$level = self::$fleet->fleet[$specID];
		}
		// not found
		else {
			$level = 0;
		}
		
		$specObj->level = $level;
		return $specObj;
	}
	
	/**
	 * Returns a array with the objects of the available specs.
	 *
	 * @param	mixed	array or integer with the spectype(s)
	 * @param	bool	return specs with level 0
	 * @return	array
	 */	
	public static function getBySpecType($specTypes, $returnZero = false) {
		self::readCache();
		
		if(!is_array($specTypes)) {
			$specTypes = array($specTypes);
		}
		
		$return = array();
				
		foreach($specTypes as $specType) {			
			$specTypeArray = self::$cache['bySpecType'][$specType];
									
			foreach($specTypeArray as $specID => $specArray) {
				$specObj = self::getSpecObj($specID);
				
				if(($returnZero && !$specObj->level) || $specObj->level) {
					$return[$specID] = $specObj;					
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Returns all specs that have an attribute set.
	 * 
	 * @param	str		attribute/field
	 * @return	array
	 */
	public static function getByAttr($attr)
	{
		$sql = "SELECT *
				FROM ugml_spec
				WHERE `".escapeString($attr)."` = 1";
		$result = WCF::getDB()->sendQuery($sql);
		
		$return = array();
		
		while($row = WCF::getDB()->fetchArray($result))
		{
			$return[$row['specID']] = self::createSpecObj($row);
		}
		
		return $return;
	}
	
	/**
	 * Checks all specs for their flags.
	 * 
	 * @param	int		checkflag
	 * @return	array
	 */
	public static function getByFlag($flag) {
		self::readCache();
		
		$return = array();
		
		foreach(self::$cache['bySpecID'] as $specID => $specArray) {
			if($flag & self::getFlag($specArray['flag'])) {
				$return[$specID] = self::getSpecObj($specID);
			}
		}
		return $return;
	}
	
	/**
	 * Returns an integer flag of a string.
	 * 
	 * @param	string
	 * @return	int
	 */
	public static function getFlag($str) {
		$len = strlen($str);
		$int = 0;
		for($i = 0; $i < $len; $i++) {
			$int |= ord($str[$i]) << (($len - $i - 1) << 3);
		}
		return $int;
	}
	
	/**
	 * Reads the cache.
	 */
	public static function readCache() {
		if(!count(self::$cache)) {
			self::$cache = WCF::getCache()->get('spec-'.PACKAGE_ID);
		}
	}
	
	/**
	 * Returns a variable of a specification.
	 * 
	 * @param	int		specification id
	 * @param	string	variable name
	 * @return	mixed
	 */
	public static function getSpecVar($specID, $variable) {
		self::readCache();
		
		return self::$cache['bySpecID'][$specID][$variable];
	}
	
	/**
	 * Returns the specification object.
	 * 
	 * @param	int		specification id
	 * @return	Spec
	 */
	public static function getSpecObj($specID) {
		self::readCache();
		
		$data = self::$cache['bySpecID'][$specID];
		
		return self::createSpecObj($data);
	}
	
	/**
	 * Creates a specification object with the given data.
	 * 
	 * @param	int		specification id
	 * @return	Spec
	 */
	public static function createSpecObj($data) {
		$className = $data['specClass'];
			
		if(empty($className)) {
			return null;
		}
		
		require_once(LW_DIR.'lib/system/spec/'.$className.'.class.php');
		self::$specObjs[$specID] = new $className(null, $data);
		
		$cachedObj = self::$specObjs[$specID];
		
		return self::addLevel($cachedObj);
	}
	
	/**
	 * Adds two spec arrays
	 * 
	 * @param	array	array1
	 * @param	array	array2
	 * @return	array	summary
	 */
	public static function add($array1, $array2) {
		$return = array();
		
		foreach($array1 as $specID => $value) {
			if(!isset($array2[$specID])) {
				$return[$specID] = $value;
			}
			else if($value instanceof Spec) {
				$return[$specID] = clone $value;
				$return[$specID]->level += $array2[$specID]->level;
			}
			else {
				$return[$specID] = $value + $array2[$specID];
			}
		}
		foreach($array2 as $specID => $value) {
			if(isset($array1[$specID])) {
				continue;
			}
			$return[$specID] = $value;
		}
		
		return $return;
	}
	
	/**
	 * Merges two arrays.
	 * 
	 * @param	array	array1
	 * @param	array	array2
	 * @return	array
	 */
	public static function merge($array1, $array2) {
		return array_merge($array1, $array2);
	}
	
	/**
	 * Creates a array with the differences of two arrays
	 * 
	 * @param	array	orig spec
	 * @param	array	new spec
	 * @return	array	difference
	 */
	public static function diff($orig, $new) {
		// get changed or added specs
		$diff = array_diff_assoc($new, $orig);
		
		// get deleted
		foreach($orig as $specID => $level) {
			if(!isset($new[$specID])) {
				$diff[$specID] = 0;
			}
		}
		
		return $diff;
	}
	
	/**
	 * Creates a new spec string from a array.
	 * 
	 * @param	array
	 * @param	string
	 */
	public static function arrayToStr($array) {
		$specs = array();
		
		ksort($array);
		
		foreach($array as $key => $row) {
			if($row instanceof Spec) {
				$specs[] = $row->specID.','.$row->level;
			}
			else {
				$specs[] = $key.','.$row;
			}
		}
		
		return implode(';', $specs);
	}
	
	/**
	 * Creates a new spec array from a string.
	 * 
	 * @param	string
	 * @return	array
	 */
	public static function strToArray($string) {
		$array = array();
		
		$specs = explode(';', $string);
		
		foreach($specs as $spec) {
			list($specID, $level) = explode(',', $spec);
			
			if($specID) {
				$array[$specID] = $level;
			}
		}
		
		return $array;
	}
}
?>