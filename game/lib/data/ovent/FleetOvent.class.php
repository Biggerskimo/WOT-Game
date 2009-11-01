<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOventFleet.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOverview.class.php');

/**
 * This class shows a fleet overview event.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds
 */
class FleetOvent extends Ovent {
	private static $registeredFleetIDs = array();
	private static $fleetOverview = null;
	
	public function __construct($oventID, $row = null) {
		parent::__construct($oventID, $row);
				
		if(self::$fleetOverview === null) {
			self::$fleetOverview = new FleetOverview();
		}
		
		if(!in_array($this->fleetID, self::$registeredFleetIDs)) {
			self::$fleetOverview->add($this->missionID, $this->resources['metal'], $this->resources['crystal'], $this->resources['deuterium']);
		
			self::$registeredFleetIDs[] = $this->fleetID;
		}
	}
	
	/**
	 * @see Ovent::getTemplateName()
	 */
	public function getTemplateName() {		
		return "oventFleet";
	}
}
?>