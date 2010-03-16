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

require_once(LW_DIR.'lib/data/protection/BotDetectorClass.class.php');

/**
 * Detects many clicks on the fleet/floten pages.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class FastSaveDetector extends BotDetectorClass {
	const SAVE_SECONDS = 4;

	/**
	 * @see	BotDetectorInterface::checkBot()
	 */
	public function checkBot() {
		$sql = "SELECT *
				FROM ugml_request
				WHERE userID = ".WCF::getUser()->userID."
				ORDER BY requestID DESC
				LIMIT 4"; // (fleet, floten1, floten2, floten3)
		$result = WCF::getDB()->sendQuery($sql);
		
		$fleetPages = array();
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleetPages[$row['site']] = $row['time'];
		}
		
		if((time() - $fleetPages['fleet']) <= self::SAVE_SECONDS) {
			$this->information = 'fleet saved in '.(time() - $fleetPages['fleet']).'s';
			return true;			
		}
		
		return false;
	}
}
?>