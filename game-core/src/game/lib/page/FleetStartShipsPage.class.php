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

require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');
require_once(LW_DIR.'lib/data/fleet/NavalFormation.class.php');
require_once(LW_DIR.'lib/data/system/System.class.php');

/**
 * Shows the form for selecting the ships; it will be saved by the
 *  FleetStartCoordinatesForm
 * 
 * @author		Biggerskimo
 * @copyright	2008-2009 Lost Worlds <http://lost-worlds.net>
 */
class FleetStartShipsPage extends AbstractPage {
	public $templateName = 'fleetStartShips';
	
	protected $fleetQueue = null;
	
	protected $targetPlanetID = 0;
	protected $galaxy = 0;
	protected $system = 0;
	protected $planet = 0;
	protected $planetType = 0;
	protected $backlink = '';
	public $systemObj = null;
	public $planetObj = null;
	
	// naval formation
	protected $detailedFleetID = 0;
	protected $navalFormation = null;
	
	public $fleets = array();
	
	protected $specs = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['detailedFleetID'])) $this->detailedFleetID = intval($_REQUEST['detailedFleetID']);
	}
	
	/**
	 * @see Page::readData
	 */
	public function readData() {
		parent::readData();
		
		//echo ".";

		$this->fleetQueue = new FleetQueue(0);
		$this->readTarget();
		
		$this->specs = Spec::getBySpecType(3);
		
		$this->fleets = Fleet::getByUserID(WCF::getUser()->userID);
		foreach($this->fleets as $fleetID => $fleet) {
			$this->fleets[$fleetID]->navalFormation = NavalFormation::getByFleetID($fleetID);
		}
		
		// backlink
		if(isset($_REQUEST['backlink'])) $this->backlink = StringUtil::trim($_REQUEST['backlink']);
		$array = array();
		preg_match('/^(https?:\/\/[^\/]*\/)?(.*)$/i', $this->backlink, $array);
		$this->fleetQueue->backlink = $this->backlink = isset($array[2]) ? $array[2] : '';		
		//echo ".";
		
		// TODO: clean this one up
		$sql = "DELETE FROM ugml_galactic_jump_queue
				WHERE userID = ".WCF::getUser()->userID;
		WCF::getDB()->registerShutdownUpdate($sql);
		$sql = "INSERT INTO ugml_galactic_jump_queue (userID, startPlanetID, state, time)
				VALUES(".WCF::getUser()->userID.", ".LWCore::getPlanet()->planetID.", 1, ".TIME_NOW.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Saves a given target to the fleet queue.
	 */
	protected function readTarget() {
		if(isset($_REQUEST['targetPlanetID'])) {
			$this->targetPlanetID = LWUtil::checkInt($_REQUEST['targetPlanetID']);
		}
		else {
			if(isset($_REQUEST['galaxy'])) $this->galaxy = LWUtil::checkInt($_REQUEST['galaxy'], 1, 9);
			if(isset($_REQUEST['system'])) $this->system = LWUtil::checkInt($_REQUEST['system'], 1, 499);
			if(isset($_REQUEST['planet'])) $this->planet = LWUtil::checkInt($_REQUEST['planet'], 1, 15);
			if(isset($_REQUEST['planetType'])) $this->planetType = LWUtil::checkInt($_REQUEST['planetType'], 1, 3);
			if(isset($_REQUEST['missionID'])) $this->fleetQueue->missionID = LWUtil::checkInt($_REQUEST['missionID']);
		}
		
		// planet		
		$this->fleetQueue->startPlanetID = LWCore::getPlanet()->planetID;
		
		$this->planetObj = Planet::getInstance($this->targetPlanetID);
		if($this->planetObj->planetID) {
			$this->fleetQueue->storePlanet($this->planetObj);

			return;
		}
		
		$this->systemObj = new System($this->galaxy, $this->system);
		$this->planetObj = $this->systemObj->getPlanet($this->planet, $this->planetType);
		
		if($this->planetObj !== null) {
			$this->fleetQueue->storePlanet($this->planetObj);
		}
	}

	/**
	 * @see Page::assignVariables
	 */
	public function assignVariables() {
		parent::assignVariables();
		//echo ".";
		WCF::getTPL()->assign(array(
		'detailedFleetID' => $this->detailedFleetID,
		'fleets' => $this->fleets,
		'specs' => $this->specs));
		//echo ":";
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
		//echo ".";	
		parent::show();
	}
}
?>
