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
 * Contains game-specific functions.
 */
class LWUtil {
	protected static $dimension = 0;
	protected static $arrayCount = 0;
	protected static $savedArrayStrs = array();

	protected static $encodeMethods = array(0 => 'base64_encode',
											1 => 'gzcompress',
											2 => 'gzdeflate');
	protected static $decodeMethods = array(0 => 'base64_decode',
											1 => 'gzuncompress',
											2 => 'gzinflate');
	protected static $varTypes = array( 0 => 'boolean',
										1 => 'integer',
										2 => 'double',
										3 => 'string',
										4 => 'array',
										5 => 'object',
										6 => 'resource',
										7 => 'NULL',
										8 => 'unknown type');
	
	/**
	 * Serializes a value
	 *
	 * @param	mixed
	 * @param	int		method to use; see encodemethods
	 * @param	int		typesafe serializing; needs 2 bytes more per value
	 * @return	string
	 */
	public static function serialize($param, $encodeMethod = null, $typeSafe = true) {
		++self::$dimension;
		
		@$functionName = self::$encodeMethods[$encodeMethod];
		
		$serialized = '';
		// array
		if(is_array($param)) {
			foreach($param as $key => $row) {
				// serialize multi-dimensional array
				if(is_array($row)) $row = str_repeat('(',self::$dimension).self::serialize($row, $encodeMethod, $typeSafe).str_repeat(')',self::$dimension);

				// key
				if(function_exists($functionName)) {
					$kvalue = $functionName($key);
					// methods like gzcompress will produce control chars
					// in their outputs (like',' or ';'); so we have to
					// encode them again
					if($encodeMethod > 0) $kvalue = base64_encode($kvalue);
					
					$kvalue = $encodeMethod.'~'.$kvalue;
				} else $kvalue = $key;
				
				// row
				if(function_exists($functionName) && !self::isSerializedArray($row, true)) {
					$value = $functionName($row);
					if($encodeMethod > 0) $value = base64_encode($value);
					$value = $encodeMethod.'~'.$value;
				} else $value = $row;
				
				// add
				if($typeSafe) {
					$ktype = array_search(gettype($key), self::$varTypes);
					$type = array_search(gettype($row), self::$varTypes);
					$serialized .= $ktype.':'.$kvalue.','.$type.':'.$value.';';
				} else $serialized .= $kvalue.','.$value.';';
			}
		} else {
			if(function_exists($functionName)) {
				$value = $functionName($param);
				if($encodeMethod > 0) $value = base64_encode($value);
				$value = $encodeMethod.'~'.$value;
			} else $value = $param;
		
			if($typeSafe) {
				$type = array_search(gettype($param), self::$varTypes);
				$serialized = $type.':'.$value;
			} else $serialized = $value;
		}

		--self::$dimension;

		return $serialized;
	}

	/**
	 * Unserializes a value
	 *
	 * @param	string
	 *
	 * @return	mixed
	 */
	public static function unserialize($param) {
		$param = self::replaceArrays($param);
		
		if(self::isSerializedArray($param, false)) {
			$orig = array();

			// get parts
			$array1 = explode(';', substr($param, 0, -1));
			
			foreach($array1 as $row) {
			
				$array2 = explode(',', $row);
				
				$orig[self::checkString($array2[0])] = self::checkForArray($array2[1]);
			}
		} else $orig = self::checkString($param);
		
		return $orig;
	}
	
	/**
	 * Checks a single string if it contains a var type in the
	 *  m: format, strips it and returns the correct value;
	 *  also decodes encoded strings n~...
	 * Examples (with the string 'foo'):
	 *  With vartype and encoding: 3:0~Zm9v
	 *  Only with vartype: 3:foo
	 * 
	 * @param	string
	 * @return	mixed
	 */
	protected static function checkString($param) {
		// we can not use a bigger regex for the hole strings,
		// because the pcre is not able to match strings compressed
		// by the gz compression method; so we will have to match only
		// the first part (sliced with substr)
		$array = array();
		$pattern = '/^(?<type>[^:]+):((?<encr>[^~])~)?/';
		$matches = preg_match($pattern, substr($param, 0, 4), $array);
		
		if(!$matches) return $param;
		
		if(isset($array['encr'])) $start = 4;
		else $start = 2;
		
		$value = substr($param, $start);
		
		// decode
		@$functionName = self::$decodeMethods[$array['encr']];
		if(function_exists($functionName)) {
			if($array['encr'] > 0) $value = base64_decode($value);
		
			$value = $functionName($value);
		}
		
		
		settype($value, self::$varTypes[$array['type']]);
		return $value;
	}

	/**
	 * Checks if the given string is a serialized array.
	 *
	 * @return	mixed
	 */
	protected static function isSerializedArray($string, $inner = false) {
		// strip braces
		if($inner) {
			$checked = false;
			while(strpos($string, '(') === 0 && strrpos($string, ')') === strlen($string) - 1) {
				$checked = true;
				
				$string = substr($string, 1, -1);
			}
			
			if(!$checked) return false;
		}
		
		if(strpos($string, ',') === false || strrpos($string, ';') !== strlen($string) - 1) return false;
		
		return true;
	}

	/**
	 * explode() can not handle serialized correctly, so we save
	 * them before they can be destroyed by explode()
	 */
	protected static function replaceArrays($string) {
		$arrayStrs = array();
		$pattern = '/,(?:(?:string|3):)?\((.*[^\)])\);/U';
		$matches = preg_match_all($pattern, $string, $arrayStrs);

		if(!$matches) return $string;

		$searches = $replaces = array();
		foreach($arrayStrs[1] as $no => $arrayStr) {
			// replace main string
			$no = StringUtil::getRandomID();
			$searches[] = '('.$arrayStr.')';
			$replaces[] = '<~-~|'.$no.'|~-~>';

			// replaces in array string ,(( -> ,( | )); -> ); | ,((( -> ,(( | ))); -> )); etc.
			$replace = ',3:$2;';
			$dimension = 0;
			do {
				$count = 0;
				++$dimension;

				$pattern = '/,((?:string|3):)?\((\({'.$dimension.'}.*\){'.$dimension.'})\);/';
				$arrayStr = preg_replace($pattern, $replace, $arrayStr, -1, $count);
			} while($count > 0);


			// save
			self::$savedArrayStrs[$no] = $arrayStr;
			++self::$arrayCount;
		}
		$cnt = 0;
		$string = str_replace($searches, $replaces, $string, $cnt);
				
		return $string;
	}

	/**
	 * Inserts the arrays which get saved by replaceArrays()
	 */
	protected static function checkForArray($string) {
		$arrayNos = array();
		$pattern = '/^(?:(?:string|3):)?<~-~\|([[:xdigit:]]{40})\|~-~>$/';
		$matches = preg_match_all($pattern, $string, $arrayNos);
		
		if(!$matches || !isset(self::$savedArrayStrs[$arrayNos[1][0]])) return self::checkString($string);

		$arrayNo = $arrayNos[1][0];
		$arrayStr = self::$savedArrayStrs[$arrayNo];
		$array = self::unserialize($arrayStr);
		
		return $array;
	}

	/**
	 * Returns the requested file.
	 *
	 * @return	string	filename
	 */
	public static function getFileName() {
		// remove path
		$parts = explode('/', $_SERVER['PHP_SELF']);
		$file = array_pop($parts);

		// remove extension
		$parts = explode('.', $file);
		$file = array_shift($parts);

		return $file;

	}

	/**
	 * Returns the request get args as array
	 * 
	 * @return	array	args
	 */
	public static function getArgsArray() {
		$array = array();
		$args = array_merge($_GET, $_POST);
		foreach($args as $key => $value) {
			if($key == 'cp') continue;
			
			$array[$key] = $value;
		}
		
		return $array;
	}
	
	/**
	 * Returns the request get args as string
	 * 
	 * @return	str		args
	 */
	public static function getArgsStr() {
		$array = self::getArgsArray();
		
		$newQueryStr = '';
		
		foreach($array as $key => $value) {
			$newQueryStr .= '&'.$key.'='.$value;
		}
		
		if(!empty($newQueryStr)) $newQueryStr = '?'.substr($newQueryStr, 1);
		
		return $newQueryStr;
	}
	
	/**
	 * Returns the encrypted value of the given arguments.
	 *
	 * @param	mixed	arg1
	 * ...
	 * @param 	mixed	arg5
	 * @return	string	hash
	 */
	public static function createHash($arg1, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null) {
		$time = substr(time(), 0, -2);

		$hash = StringUtil::encrypt($time.$arg1);

		for($i = 2; $i <= 5; ++$i) {
			eval('if($arg'.$i.' !== null) $hash = StringUtil::encrypt($hash.$arg'.$i.');');
			/*if($arg{$i} !== null) {
				$hash = StringUtil::encrypt($hash.$arg{$i});
			}*/

		}
		return $hash;
	}
	
	/**
	 * Counts the dimensions
	 * 
	 * @param	array
	 * @param	int		dimensions
	 */
	public static function getDimensionCount($array) {
		if(!is_array($array)) return 0;  
		if(empty($array)) return 1;
		
		return intval(array_map(array('LWUtil', 'getDimensionCount'), $array)) + 1;  
	}

	/**
	 * Returns the median of the values of a array
	 * 
	 * @param	array	values
	 * @return	float	median
	 */
	public static function getMedian($array) {
		sort($array, SORT_NUMERIC);
		
		$valuesCount = count($array);
		
		if($valuesCount & 1) {
			$key = floor($valuesCount / 2);
			return $array[$key];
		}
		$key1 = floor($valuesCount / 2);
		$key2 = $key1 + 1;
		
		return ($array[$key1] + $array[$key2]) / 2;
	}
	
	/**
	 * Checks a integer
	 * 
	 * @param	int
	 * @param 	mixed	if int the min value, else a array with the values to check
	 * @param	int		max
	 * @return 	int
	 */
	public static function checkInt($int, $min = 0, $max = null) {		
		$int = intval($int);
		
		if($int < $min && $min !== null) return $min;
		
		if($int > $max && $max !== null) return $max;
		
		return $int;
	}
	
	/**
	 * Locks a account for a specific time.
	 * 
	 * @param	int		user id
	 * @param	int		seconds to lock
	 */
	public static function lockAccount($userID, $lockTime) {
		require_once(WCF_DIR.'lib/system/io/File.class.php');
		$lockFile = new File(LW_DIR.'lock/lock'.$userID.'.lock.php');
		
		$lockTimeEnd = microtime(true) + $lockTime;
		
		$lockFile->write("<?php
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
\n/**\n * This file is automatically generated by wot.\n * Edit or delete may cause some inconsisty problems!\n */\n\$lockTimeEnd = ".$lockTimeEnd.";\n?>");
		$lockFile->close();
	}
	
	/**
	 * Removes a existing lock for a user.
	 * 
	 * @param	int		user id
	 */
	public static function removeLock($userID) {
		if(file_exists(LW_DIR.'lock/lock'.$userID.'.lock.php')) {
			unlink(LW_DIR.'lock/lock'.$userID.'.lock.php');
		}
	}
	
	/**
	 * Checks for a lock in the file system. If a old is found, it will be removed.
	 *  Otherwise, a SystemException will be thrown.
	 * 
	 * @param	int		user id
	 */
	public static function checkLock($userID) {
		if(file_exists(LW_DIR.'lock/lock'.$userID.'.lock.php')) {
			include(LW_DIR.'lock/lock'.$userID.'.lock.php');
			
			// throw exception
			if(@$lockTimeEnd > microtime(true)) {
				require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
				throw new SystemException('account is locked for next '.ceil($lockTimeEnd - microtime(true)).'s');
			}
			
			// remove lock
			self::removeLock($userID);
		}
	}
}
?>