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
require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');

require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');

/**
 * The last step to start a fleet.
 * 
 * @author		Biggerskimo
 * @copyright	2008-2009 Lost Worlds <http://lost-worlds.net>
 */
class FleetStartFireAction extends AbstractAction {
	public $missionID = 0;
	public $metal = 0;
	public $crystal = 0;
	public $deuterium = 0;
	
	public $target = 'index.php?page=FleetStartShips';
	
	public $fleetQueue = null;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['mission'])) {
			$this->missionID = LWUtil::checkInt($_REQUEST['mission']);
		}
		
		if(isset($_REQUEST['metal'])) {
			$this->metal = LWUtil::checkInt($_REQUEST['metal'], 0, LWCore::getPlanet()->metal);
		}
		
		if(isset($_REQUEST['crystal'])) {
			$this->crystal = LWUtil::checkInt($_REQUEST['crystal'], 0, LWCore::getPlanet()->crystal);
		}
		
		if(isset($_REQUEST['deuterium'])) {
			$this->deuterium = LWUtil::checkInt($_REQUEST['deuterium'], 0, LWCore::getPlanet()->deuterium);
		}
		
		//TODO: integrate this in wcf eventlistener readParameters@FleetStartFireAction
		if(isset($_REQUEST['formation'])) {
			$this->formationID = LWUtil::checkInt($_REQUEST['formation']);
		}
	}
	
	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		$this->fleetQueue = new FleetQueue(3);
				
		$this->fleetQueue->missionID = $this->missionID;
		
		$this->fleetQueue->metal = $this->metal;
		$this->fleetQueue->crystal = $this->crystal;
		$this->fleetQueue->deuterium = $this->deuterium;
		$this->fleetQueue->formationID = $this->formationID;
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
		
		$this->fleetQueue->validate();
		$this->fleetQueue->fire();
		
		if($this->fleetQueue->backlink != '') {
			$this->target = $this->fleetQueue->backlink;
		}

		$this->executed();
		
		header('Location: '.$this->target);
		exit;
	}
}
?>