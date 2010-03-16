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

require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');
require_once(LW_DIR.'lib/data/system/System.class.php');

/**
 * This action can fire a fleet with one request.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
abstract class FleetStartDirectFireAction extends AbstractAction {
	protected $fleetQueueClassName = 'FleetQueue';
	
	public $galaxy = 1;
	public $system = 1;
	public $planet = 1;
	public $planetKind = 1;
	public $planetObj = null;
	
	public $metal = 0;
	public $crystal = 0;
	public $deuterium = 0;
	
	public $spec = array();
	
	public $missionID = 0;
	
	public $speedPercent = 1;
	
	protected $lookForSpecType = 3;
	
	protected $allowTarget = true;
	protected $allowResources = false;
	protected $allowSpec = true;
	protected $allowMission = false;
	protected $allowSpeed = false;
	
	public $fleetQueue = null;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if($this->allowTarget) {
			$this->readParametersTarget();
		}
		
		if($this->allowResources) {
			$this->readParametersResources();
		}
		
		if($this->allowSpec) {
			$this->readParametersSpec();
		}
		
		if($this->allowMission) {
			$this->missionID = LWUtil::checkInt($_REQUEST['missionID']);
		}
		
		if($this->allowPercent) {
			$this->speedPercent = floatval($_REQUEST['speedPercent']);
		}
	}
	
	/**
	 * Reads the parameters of the target.
	 */
	public function readParametersTarget() {
		if(isset($_REQUEST['galaxy'])) {
			$this->galaxy = LWUtil::checkInt($_REQUEST['galaxy'], 1, 9);
		}
		
		if(isset($_REQUEST['system'])) {
			$this->system = LWUtil::checkInt($_REQUEST['system'], 1, 499);
		}
		
		if(isset($_REQUEST['planet'])) {
			$this->planet = LWUtil::checkInt($_REQUEST['planet'], 1, 15);
		}
		
		if(isset($_REQUEST['planetKind'])) {
			$this->planetKind = LWUtil::checkInt($_REQUEST['planetKind'], 1, 3);
		}
	}
	
	/**
	 * Reads the parameters for resources.
	 */
	public function readParametersResources() {
		if(isset($_REQUEST['metal'])) {
			$this->metal = LWUtil::checkInt($_REQUEST['metal'], 0, LWCore::getPlanet()->metal);
		}
		
		if(isset($_REQUEST['crystal'])) {
			$this->crystal = LWUtil::checkInt($_REQUEST['crystal'], 0, LWCore::getPlanet()->crystal);
		}
		
		if(isset($_REQUEST['deuterium'])) {
			$this->deuterium = LWUtil::checkInt($_REQUEST['deuterium'], 0, LWCore::getPlanet()->deuterium);
		}
	}
	
	/**
	 * Reads the parameters of the specs.
	 */
	public function readParametersSpec() {
		$specs = Spec::getBySpecType($this->lookForSpecType, false);
		
		foreach($specs as $specID => $specObj) {
			if(isset($_REQUEST['spec'.$specID]) && intval($_REQUEST['spec'.$specID])) {
				$this->spec[$specID] = LWUtil::checkInt($_REQUEST['spec'.$specID], 0, $specObj->level);
			}
		}
	}
	
	/**
	 * Returns the current fleet queue object.
	 * 
	 * @return FleetQueue
	 */
	protected function getFleetQueue() {
		if($this->fleetQueue === null) {
			require_once(LW_DIR.'lib/data/fleet/queue/'.$this->fleetQueueClassName.'.class.php');
			
			$this->fleetQueue = new $this->fleetQueueClassName(0);
		}
		
		return $this->fleetQueue;
	}
	
	/**
	 * @see	Page::readData()
	 */
	public function readData() {		
		$this->getFleetQueue()->startPlanetID = LWCore::getPlanet()->planetID;
		
		$system = new System($this->galaxy, $this->system);
		$this->planetObj = $system->getPlanet($this->planet, $this->planetKind);
		$this->getFleetQueue()->storePlanet($this->planetObj);
		
		$this->getFleetQueue()->metal = $this->metal;
		$this->getFleetQueue()->crystal = $this->crystal;
		$this->getFleetQueue()->deuterium = $this->deuterium;
		
		$this->getFleetQueue()->ships = $this->spec;		
		
		$this->getFleetQueue()->missionID = $this->missionID;
		
		$this->getFleetQueue()->speedPercent = $this->speedPercent;
		
		/*var_dump($this);
		var_dump($this->getFleetQueue()->getConsumption());
		exit;*/
		
		for($speedPercent = 0.9;
			$speedPercent >= 0.1 &&
				$this->getFleetQueue()->getConsumption() > $this->getFleetQueue()->getCapacity();
			$speedPercent -= 0.1) {
			
			$this->speedPercent = $this->getFleetQueue()->speedPercent = $speedPercent;
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		$this->readData();
		
		$i = 0;
		do {
			try {
				$this->fleetQueue->validate();
				$this->fleetQueue->fire();
				
				// everything okay
				break;
			}
			catch(NamedUserException $e) {
				die($e->getMessage());
			}
			catch(SystemException $e) {
				if($i >= 6) {
					die($e->getMessage());
				}
				++$i;
				
				usleep(500000);
			}
		} while(true);

		$this->executed();
		
		exit;
	}
}
?>