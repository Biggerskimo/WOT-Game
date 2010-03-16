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

/**
 * This class shows manages building ovents.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class BuildingOvent extends Ovent {
	// TODO
	const OVENT_TYPE_ID = 2;
	
	/**
	 * Updates the building ovent for a planet.
	 *
	 * @param	int		planet id
	 */
	public static function check($planetID) {
		$ovents = Ovent::getByConditions(array('planetID' => $planetID, 'oventTypeID' => self::OVENT_TYPE_ID));
		$planet = Planet::getInstance($planetID);
		
		// event needed
		if($planet->b_building > time()) {
			if(isset($ovents[0])) {
				if($ovents[0]->time == $planet->b_building) {
					// no changes needed
					return;
				}
				// delete old ovent
				$ovents[0]->getEditor()->delete();
			}
			
			// create new
			$data = self::getData($planet);
			$fields = array('userID' => $planet->id_owner, 'planetID' => $planetID);
			OventEditor::create(self::OVENT_TYPE_ID, $planet->b_building, null, $planetID, $fields, 0, $data);
		}
		// no event needed; delete
		else {
			foreach($ovents as $ovent) {
				$ovent->getEditor()->delete();
			}
		}
	}
	
	/**
	 * Builds the data array.
	 *
	 * @param	Planet	planet
	 */
	protected static function getData(Planet $planet) {
		Spec::storeData($planet);
		
		$data = array('specID' => $planet->b_building_id, 'level' => Spec::getSpecObj($planet->b_building_id)->level + 1,
				'planetID' => $planet->planetID, 'planetName' => $planet->name,
				'coords' => array($planet->galaxy, $planet->system, $planet->planet, $planet->planetKind));
		
		return $data;
	}
	
	/**
	 * @see Ovent::getTemplateName()
	 */
	public function getTemplateName() {
		return "oventBuilding";
	}
}
?>