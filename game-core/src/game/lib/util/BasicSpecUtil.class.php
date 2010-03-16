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
 * Holds all functions to manage the specs.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class BasicSpecUtil {
	/**
	 * Checks the requirements of the spec
	 * 
	 * @param	int		spec id
	 * @param	Planet
	 * @param	LWUser	user object; if null actual LWUserSession
	 * @return	bool
	 */
	public static function checkRequirements($specID, Planet &$planet = null, LWUser &$user = null) {
		if($planet === null) $planet =& LWCore::getPlanet();
		if($user === null) $user =& WCF::getUser();
		
		$specs = WCF::getCache()->get('spec-'.PACKAGE_ID, 'bySpecID');
		$requirements = $specs[$specID]['requirement'];
		
		foreach($requirements as $specID2 => $requirement) {
			$requirementSpec = $specs[$specID2];
		
			// search in planet obj
			if($planet->{$requirementSpec['colName']} != '') {
				$level = $planet->{$requirementSpec['colName']};
			// search in user obj
			} else if($planet->{$requirementSpec['colName']} != '') {
				$level = $planet->{$requirementSpec['colName']};
			// not found -> requirement failed
			} else {
				return false;
			}
			
			// check level
			if($requirement['min'] != 0 && $requirement['min'] <= $level) {
				continue;
			}
			
			if($requirement['max'] != 0 && $level <= $requirement['max']) {
				continue;
			}
			
			// requirement failed
			return false;
		}
		
		// all requirements checked
		return true;
	}
	
	/**
	 * Checks the resources of the spec id on the given planet.
	 * 
	 * @param	int		spec id
	 * @param	Planet	planet
	 * @return	bool
	 */
	public static function checkResources($specID, Planet &$planet = null) {
		if($planet === null) $planet =& LWCore::getPlanet();
		
		$specs = WCF::getCache()->get('spec-'.PACKAGE_ID, 'bySpecID');
		$spec = $specs[$specID];
		
		if($spec['metal'] > $planet->metal) return false;
		if($spec['crystal'] > $planet->crystal) return false;
		if($spec['deuterium'] > $planet->deuterium) return false;

		return true;
	}
	
	/**
	 * Returns true if a spec is buildable
	 * 
	 * @param	int		spec id
	 * @param	Planet
	 * @param	LWUser	user object; if null actual LWUserSession
	 * @return	bool
	 */
	public static function buildable($specID, Planet &$planet = null, LWUser &$user = null) {
		$requirements = self::checkRequirements($specID, $planet, $user);
		$resources = self::checkResources($specID, $planet);
		
		return ($requirements && $resources);
	}
	
	/**
	 * Returns the colname for a spec
	 * 
	 * @param	int		spec id
	 * @return	string
	 */
	public static function getColName($specID) {
		$specs = WCF::getCache()->get('spec-'.PACKAGE_ID, 'bySpecID');
		$spec = $specs[$specID];
		
		return $spec['colName'];
	}
	
	/**
	 * Returns a array with all counts of specs
	 * 
	 * @param	int		spec type id
	 * @param	Planet
	 * @param	LWUser	user object; if null actual LWUserSession
	 * @return	array
	 */
	public static function getBySpecID($specTypeID, $planet = null, $user = null, $returnSpec = false) {
		if($planet === null) $planet =& LWCore::getPlanet();
		if($user === null) $user =& WCF::getUser();
		
		$specTypes = WCF::getCache()->get('spec-'.PACKAGE_ID, 'bySpecID');
		
		$spec = $specTypes[$specTypeID];
		
		if($returnSpec) return $spec;
				
		$colName = self::getColName($specID);
						
		// search in planet obj
		if($planet->$colName != '') {
			return $planet->$colName;
		// search in user obj
		} else if($planet->$colName != '') {
			return $planet->$colName;
		}
		// not found
		return 0;
	}
	
	/**
	 * Returns a array with all counts of specs
	 * 
	 * @param	int		spec type id
	 * @param	Planet
	 * @param	LWUser	user object; if null actual LWUserSession
	 * @return	array
	 */
	public static function getBySpecTypeID($specTypeID, Planet &$planet = null, LWUser &$user = null) {
		if($planet === null) $planet =& LWCore::getPlanet();
		if($user === null) $user =& WCF::getUser();
		
		$specTypes = WCF::getCache()->get('spec-'.PACKAGE_ID, 'bySpecType');
		
		$specType = $specTypes[$specTypeID];
		$array = array();
		
		foreach($specType as $specID => $spec) {
			$colName = self::getColName($specID);
						
			// search in planet obj
			if($planet->$colName != '') {
				$level = $planet->$colName;
			// search in user obj
			} else if($planet->$colName != '') {
				$level = $planet->$colName;
			// not found
			} else {
				continue;
			}
			
			if($level) $array[$specID] = $level;
		}
		
		return $array;
	}
}
?>