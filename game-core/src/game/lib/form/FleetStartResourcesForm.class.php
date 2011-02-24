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
require_once(LW_DIR.'lib/data/system/System.class.php');
require_once(LW_DIR.'lib/util/FleetSpecUtil.class.php');

/**
 * Shows the form for selecting a mission, naval formation, standby time and resources; it will be saved by the
 *  FleetStartFireAction
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetStartResourcesForm extends AbstractForm {
	public $templateName = 'fleetStartResources';
	
	protected $fleetQueue = null;
	
	protected $consumption = 0;
	
	protected $targetPlanet = null;
	protected $missions = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		if(isset($_REQUEST['galaxy'])) $this->galaxy = intval($_REQUEST['galaxy']);
		if(isset($_REQUEST['system'])) $this->system = intval($_REQUEST['system']);
		if(isset($_REQUEST['planet'])) $this->planet = intval($_REQUEST['planet']);
		if(isset($_REQUEST['planetType'])) $this->planetType = intval($_REQUEST['planetType']);
		
		if(isset($_REQUEST['speed'])) $this->speed = floatval($_REQUEST['speed']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		$this->fleetQueue = new FleetQueue(2);

		parent::readData();
		
		$this->getTargetPlanet();		
		$this->fleetQueue->speedPercent = $this->speed;
		$this->missions = $this->fleetQueue->getMissions();		
		$this->capacity = $this->fleetQueue->getCapacity() - $this->fleetQueue->getConsumption();
	}
	
	/**
	 * Returns the planet object of the target planet.
	 * 
	 * @return	Planet
	 */
	protected function getTargetPlanet() {
		if($this->planetObj === null) {
			$system = new System($this->galaxy, $this->system);
			$this->planetObj = $system->getPlanet($this->planet, $this->planetType);
			
			$this->fleetQueue->storePlanet($this->planetObj);
			$this->fleetQueue->galaxy = $this->galaxy;
			$this->fleetQueue->system = $this->system;
			$this->fleetQueue->planet = $this->planet;
			$this->fleetQueue->planetType = $this->planetType;
			
		}
		
		return $this->planetObj;
	}

	/**
	 * @see Page::assignVariables
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$this->getTargetPlanet();
		
		WCF::getTPL()->assign(array(
			'fleetQueue' => $this->fleetQueue,
			'missions' => $this->missions,
			'capacity' => $this->capacity,
			'deuterium' => LWCore::getPlanet()->deuterium - $this->fleetQueue->getConsumption()
		));
		
		
		//TODO: integrate this in wcf eventlistener assignVariables@FleetStartResourcesForm
		WCF::getTPL()->assign('navalFormations', NavalFormation::getByTargetPlanetID($this->fleetQueue->getTargetPlanet()->planetID, WCF::getUser()->userID));
	}

	/**
	 * @see Page::show
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