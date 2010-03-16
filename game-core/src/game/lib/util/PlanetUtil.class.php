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
 * Provides some functions for planets.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class PlanetUtil {
	/**
	 * Calculates the diameter by fields.
	 * 
	 * @param	int		fields
	 * @param	int		diameter
	 */
	public static function getDiameter($fields) {
		return round(sqrt($fields) * 1000);
	}
	
	/**
	 * Checks whether this name is valid or not.
	 * 
	 * @param	string	name
	 * @return	bool
	 */
	public static function isValid($name) {
		return preg_match('/^[A-Za-z0-9ÄÖÜäöü _-]{4,25}$/', $name) == 1;
	}
	
	/**
	 * Returns some information based on the position.
	 * 
	 * @param	int		position
	 * @param	array	information
	 */
	public static function getLocationEnvironment($position) {
		$information = array();
		
		switch($position) {
			case 1:
			case 2:
			case 3:
				$information['image'] = 'trocken';
				$number = range(1, 10);
				$information['fields'] = rand(140, 250);
				$information['maxTemp'] = rand(40, 140);
				break;
				
			case 4:
			case 5:
			case 6:
				$information['image'] = 'dschjungel';
				$number = range(1, 10);
				$information['fields'] = rand(330, 670);
				$information['maxTemp'] = rand(15, 115);
				break;
				
			case 7:
			case 8:
			case 9:
				$information['image'] = 'normaltemp';
				$number = range(1, 7);
				$information['fields'] = rand(260, 440);
				$information['maxTemp'] = rand(-10, 90);
				break;
				
			case 10:
			case 11:
			case 12:
				$information['image'] = 'wasser';
				$number = range(1, 9);
				$information['fields'] = rand(140, 250);
				$information['maxTemp'] = rand(-35, 65);
				break;
				
			case 13:
			case 14:
			case 15:
				$information['image'] = 'eis';
				$number = range(1, 10);
				$information['fields'] = rand(200, 300);
				$information['maxTemp'] = rand(-60, 40);
				break;
				
			default:
				return array();
		}
		$imgNo = $number[array_rand($number)];
		if($imgNo < 10) {
			$imgNo = '0'.$imgNo;
		}
		$information['image'] .= 'planet'.$imgNo;
		
		return $information;
	}
}
?>