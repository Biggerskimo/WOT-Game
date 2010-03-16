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
 * Contains some util functions used in the game.
 *
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class WOTUtil {
	private static $callsAfterInit = array();
	private static $initDone = false;
	
	public static function callAfterInit($callback) {
		if(self::$initDone) {
			call_user_func($callback, true);
		}
	
		$callsAfterInit[] = $callback;
	}
	
	public static function initDone() {
		foreach(self::$callsAfterInit as $call) {
			call_user_func($call, false);
		}
		
		$initDone = true;
	}
}
?>