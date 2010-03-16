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
 * Contains functions for the wotapi.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds  <http://lost-worlds.net>
 */
class WOTAPIUtil {
	public static $search = array ("\\",   ":"  , "\n"   , "\r"   );
	public static $replace = array("\\\\", "\\:", "\\\\n", "\\\\r");
	
	
	/**
	 * Escapes control characters. (binary safe)
	 * 
	 * @param	mixed
	 * @return	string
	 */
	public static function escape($subject) {
		// addcslashes sounds good, but is NOT binary safe
		return addcslashes($subject, ":\n\r\\");
		//return str_replace(self::$search, self::$replace, $subject);
	}
	
	/**
	 * Unescapes the escaped control characters. (binary safe)
	 * 
	 * @param 	string
	 * @return	string
	 */
	public static function unescape($subject) {
		// stripcslashes is not binary safe, too
		return stripcslashes($subject);
		//return str_replace(self::$replace, self::$search, $subject);
	}
}
?>