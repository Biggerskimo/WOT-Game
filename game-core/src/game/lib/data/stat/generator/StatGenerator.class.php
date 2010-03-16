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
 * All Stat-Generators should implement this interface.
 *  The first rank gets the number 1, not 0!
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
interface StatGenerator {
	/**
	 * Creates a new StatGenerator object.
	 * 
	 * @param	int		stat type id
	 */
	public function __construct($statTypeID);
	
	/**
	 * This method generates new stats.
	 * 
	 * @param	array	parameters
	 */
	public function generate($param = array());
	
	/**
	 * This method should return all rows in the given range:
	 * Array
	 * (
	 * 	[$rank] => Array
	 * 	(
	 * 	 [rank] => <int>,
	 *   [points] => <int>,
	 *   ...
	 * 	)
	 * )
	 * 
	 * If $start == 0, the range of a specific relational entry should be shown:
	 *  - If $showRank === false, search the current relational id and use it;
	 *  - If $showRank !== false, use $showRank as relational id
	 * 
	 * @return	array
	 */
	public function getRows($start = 1, $rowCount = 100, $showRank = false);
}
?>