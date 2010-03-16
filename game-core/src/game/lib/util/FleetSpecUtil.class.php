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
 * Calculates the speed, consumption etc.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetSpecUtil {
	/**
	 * Decides which drives is used.
	 * 
	 * @param	int		spec id
	 * @param	LWUser	drive
	 */
	public static function getDriveSpecID($specID, $user = null) {
		if($user === null) $user = WCF::getUser();
	
		$specs = WCF::getCache()->get('spec-'.PACKAGE_ID, 'bySpecID');
		$spec = $specs[$specID];
		
		$drives = array();
				
		foreach($spec['drive'] as $driveSpecID => $drive) {
			$driveColName = BasicSpecUtil::getColName($driveSpecID);
			$driveLevel = $user->$driveColName;
			
			// check
			if(LWUtil::checkInt($driveLevel, $drive['min'], $drive['max']) != $driveLevel) continue;
		
			// we must use intval here, because array_flip can only flip strings and integers
			$drives[$driveSpecID] = intval($drive['speed'] + $drive['speed'] * $user->$driveColName * $drive['factor']);
		}
		
		if(!count($drives)) return null;
		
		asort($drives, SORT_NUMERIC);
		
		//var_dump($drives);
		
		$drives = array_flip($drives);
		
		return array_pop($drives);
	}

	/**
	 * Calculates the speed
	 * 
	 * @param	int		spec id
	 * @param	LWUser	user
	 */
	public static function getSpeed($specID, $user = null) {
		if($user === null) $user = WCF::getUser();
	
		$driveSpecID = self::getDriveSpecID($specID, $user);
		if($driveSpecID === null) return 0;
		
		$specs = WCF::getCache()->get('spec-'.PACKAGE_ID, 'bySpecID');
		
		$drive = $specs[$specID]['drive'][$driveSpecID];
		$driveColName = BasicSpecUtil::getColName($driveSpecID);
		
		$speed = $drive['speed'] + $drive['speed'] * $user->$driveColName * $drive['factor'];
		return $speed;
	}
	
	/**
	 * Calculates the consumption
	 * 
	 * @param	int		spec id
	 * @param	LWUser	user
	 */
	public static function getConsumption($specID, $user = null) {
		if($user === null) $user = WCF::getUser();
	
		$driveSpecID = self::getDriveSpecID($specID, $user);
		if($driveSpecID === null) return 0;
		
		$specs = WCF::getCache()->get('spec-'.PACKAGE_ID);
		
		$consumption = $specs['bySpecID'][$specID]['drive'][$driveSpecID]['consumption'];
		
		//echo '->';
		//var_dump($specs['bySpecID'][$specID]);
		
		return $consumption;
	}
}
?>