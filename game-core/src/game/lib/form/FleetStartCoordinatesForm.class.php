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

require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');
require_once(LW_DIR.'lib/data/fleet/NavalFormation.class.php');

/**
 * Shows the form for selecting a planet; it will be saved by the
 *  FleetStartResourcesForm
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetStartCoordinatesForm extends AbstractForm {
	public $templateName = 'fleetStartCoordinates';
	
	protected $fleetQueue = null;
	
	protected $ships = array();	
	protected $specs = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$specs = Spec::getBySpecType(3);
				
		foreach($specs as $specID => $specObj) {
			$shipCount = LWUtil::checkInt(@$_REQUEST['ship'.$specID], 0, $specObj->level);		
			
			if($shipCount) {
				$specObj->level = $shipCount;
				$this->specs[$specID] = clone $specObj;
				$this->ships[$specID] = $shipCount;
			}
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		$this->fleetQueue = new FleetQueue(1);
		
		parent::readData();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$this->fleetQueue->ships = $this->ships;
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		global $game_config;
	
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'maxSpeed' => $this->fleetQueue->getSpeed(),
			'capacity' => $this->fleetQueue->getCapacity(),
			'speedFactor' => ($game_config['fleet_speed'] / 2500),
			'fleetQueue' => $this->fleetQueue,
			'specs' => $this->specs));
		
		//TODO: integrate this in wcf eventlistener assignVariables@FleetStartCoordinatesForm
		WCF::getTPL()->assign('navalFormations', NavalFormation::getByUserID(WCF::getUser()->userID));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
?>