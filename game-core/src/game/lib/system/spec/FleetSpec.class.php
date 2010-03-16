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
 * Holds extended functions for fleet specifications.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetSpec extends Spec {
	/**
	 * Returns the spec object of the used drive.
	 * 
	 * @param	LWUser	user
	 * @return	Spec	drive
	 */
	public function getDrive() {		
		$drives = array();
		if($this->drive == null) {
			return null;
		}
		foreach($this->drive as $driveSpecID => $driveData) {
			$driveObj = self::getSpecObj($driveSpecID);
			
			$driveColName = $driveObj->colName;
			$driveLevel = self::$user->$driveColName;
			
			// check
			if(LWUtil::checkInt($driveLevel, $driveData['min'], $driveData['max']) != $driveLevel) continue;
		
			// we must use intval here, because array_flip can only flip strings and integers (no floats)
			$drives[$driveSpecID] = intval($driveData['speed'] + $driveData['speed'] * self::$user->$driveColName * $driveData['factor']);
		}
		
		if(!count($drives)) return null;
		
		asort($drives, SORT_NUMERIC);		
		$drives = array_flip($drives);		
		$driveSpecID =  array_pop($drives);
		
		return self::getSpecObj($driveSpecID);
	}

	/**
	 * Calculates the speed.
	 * 
	 * @param	LWUser	user
	 * @return	int		speed
	 */
	public function getSpeed() {
		self::readCache();
	
		$drive = $this->getDrive($user);
				
		if($drive === null) return 0;
		
		$driveData = self::$cache['bySpecID'][$this->specID]['drive'][$drive->specID];
		$driveColName = $drive->colName;
				
		$speed = $driveData['speed'] + $driveData['speed'] * self::$user->$driveColName * $driveData['factor'];
		
		return $speed;
	}
	
	/**
	 * Calculates the consumption.
	 * 
	 * @param	LWUser	user
	 * @return	int		consumption
	 */
	public function getConsumption($user = null) {
		self::readCache();
	
		$drive = $this->getDrive();
		
		if($drive === null) return null;
		
		$consumption = self::$cache['bySpecID'][$this->specID]['drive'][$drive->specID]['consumption'];
		
		return $consumption;
	}
}
?>