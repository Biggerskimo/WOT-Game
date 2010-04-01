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
 * This class is able to access user settings without a session.
 *
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class UserSettings {
	public static $settings = array();
	
	/**
	 * Returns a setting.
	 *
	 * @param	int		userID
	 * @param	string	setting
	 */
	public static function getSetting($userID, $setting) {
		self::loadSettings($userID);
		
		if(isset(self::$settings[$userID][$setting])) {
			return unserialize(self::$settings[$userID][$setting]);
		}
		return null;
	}
	
	/**
	 * Sets a setting with an identifier and value.
	 *
	 * @param	int		userID
	 * @param	string	setting
	 * @param	mixed	value
	 * @param	int		expire time
	 */
	public static function setSetting($userID, $setting, $value, $expireTime = 0x7FFFFFFF) {
		$svalue = serialize($value);
		
		$sql = "REPLACE INTO ugml_user_setting
				(userID, setting, expireTime, value)
				VALUES
				(".$userID.", '".escapeString($setting)."', ".$expireTime.", '".escapeString($svalue)."')";
		WCF::getDB()->sendQuery($sql);
		
		self::$settings[$userID][$setting] = $svalue;
		
		Session::resetSessions($userID);
	}
	
	/**
	 * Loads the settings of a user.
	 *
	 * @param	int		userID
	 */
	public static function loadSettings($userID) {
		if(!isset(self::$settings[$userID])) {
			self::$settings[$userID] = array();
			
			$sql = "SELECT GROUP_CONCAT(CONCAT(setting, ',', value) SEPARATOR '|')
						AS settingsStr
					FROM ugml_user_setting
					WHERE userID = ".$userID;
			$row = WCF::getDB()->getFirstRow($sql);
			
			$parts = explode('|', $row['settingsStr']);
			foreach($parts as $part) {
				if(!empty($part) && strpos($part, ',')) {
					list($setting, $value) = explode(',', $part);
					
					self::$settings[$userID][$setting] = $value;
				}
			}
		}
	}
}
?>