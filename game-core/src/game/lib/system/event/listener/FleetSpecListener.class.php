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

require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Reads the information for rapidfire and drives from the db
 *
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetSpecListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		echo "FLEETSPEC";
		// drives ($specID => drive => $drive => $row => ...)
		$sql = "SELECT *
				FROM ugml_spec_drive";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$eventObj->data[$row['specID']]['drive'][$row['drive']] = $row;
		}
		
		// rapidfire ($specID => rapidfire => $target => $shots)
		$sql = "SELECT *
				FROM ugml_spec_rapidfire";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$eventObj->data[$row['specID']]['rapidfire'][$row['target']] = $row['shots'];
		}
	}
}
?>