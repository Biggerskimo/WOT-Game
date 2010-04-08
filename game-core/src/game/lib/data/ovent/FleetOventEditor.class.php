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

require_once(LW_DIR.'lib/data/ovent/OventEditor.class.php');
require_once(LW_DIR.'lib/util/SerializeUtil.class.php');

/**
 * This class is able to handle overview events.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class FleetOventEditor extends OventEditor {
	/**
	 * Updates ships and resources.
	 */
	public function update() {
		$fleetData = $this->getPoolData();
		
		foreach($fleetData as &$fleetDate) {
			$fleetObj = Fleet::getInstance($fleetDate['fleetID']);
			
			$fleetDate['resources'] = array('metal' => $fleetObj->metal, 'crystal' => $fleetObj->crystal, 'deuterium' => $fleetObj->deuterium);
			$fleetDate['spec'] = $fleetObj->fleet;
			$fleetDate['missionID'] = $fleetObj->missionID;
		}
		
		if(isset($fleetData[0])) {
			$fleet0 = Fleet::getInstance($fleetData[0]['fleetID']);
			if($fleet0->missionID == 11 && $fleetData[0]['passage'] == "flight") {
				$formation = $fleet0->getNavalFormation();
				
				foreach($fleetData as $key => &$fleetDate) {
					if(!isset($formation->fleets[$fleetDate['fleetID']])) {
						unset($fleetData[$key]);
					}
				}
			}
		}
		
		$sql = "UPDATE ugml_ovent
				SET data = '".SerializeUtil::serialize($fleetData)."'
				WHERE oventID = ".$this->oventID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>