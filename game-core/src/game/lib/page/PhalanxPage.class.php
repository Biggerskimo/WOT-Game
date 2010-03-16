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

/**
 * Shows the phalanx.
 */
class PhalanxPage extends AbstractPage {
	public $templateName = 'phalanx';

	protected $galaxy = null;
	protected $system = null;
	protected $planet = null;
	protected $systemObj = null;
	public $planetObj = null;
	protected $fleetObjs = array();
	protected $costs = 0;

	/**
	 * @see Page::readParameters
	 */
	public function readParameters() {
		parent::readParameters();

		require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');

		if(isset($_GET['galaxy'])) $this->galaxy = intval($_GET['galaxy']);
		else throw new IllegalLinkException();

		if(isset($_GET['system'])) $this->system = intval($_GET['system']);
		else throw new IllegalLinkException();

		if(isset($_GET['planet'])) $this->planet = intval($_GET['planet']);
		else throw new IllegalLinkException();
	}

	/**
	 * @see Page::readData
	 */
	public function readData() {
		parent::readData();

		// get system
		require_once(LW_DIR.'lib/data/system/System.class.php');
		$this->systemObj = new System($this->galaxy, $this->system);

		// get planet
		$this->planetObj = $this->systemObj->getPlanet($this->planet);

		// get fleets
		$sql = "SELECT ugml_fleet.*,
					ugml_naval_formation.formationID,
								GROUP_CONCAT(
									CONCAT(specID, ',', shipCount) 
									SEPARATOR ';')
								AS fleet
				FROM ugml_fleet
				LEFT JOIN ugml_naval_formation
					ON ugml_fleet.formationID = ugml_naval_formation.formationID
		    	LEFT JOIN ugml_fleet_spec
		    		ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
				WHERE targetPlanetID = ".$this->planetObj->planetID."
					OR startPlanetID = ".$this->planetObj->planetID."
				GROUP BY ugml_fleet.fleetID
				ORDER BY ugml_fleet.impactTime,
					ugml_fleet.returnTime";
		$fleets = WCF::getDB()->sendQuery($sql);

		$fleetArray = array();
		
		require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
			
		while($row = WCF::getDB()->fetchArray($fleets)) {
			$fleet = Fleet::getInstance(null, $row);
		
			$fleetArray += $fleet->getFleetSet($this->planetObj->planetID);
		}
		
		ksort($fleetArray);
		
		$this->fleetObjs = $fleetArray;

		// calculate needed deuterium
		// 500 * (ENTFERNUNG + 2) * 0,9 ^ STUFE * (1 + 0,001 * 0,9 ^ (STUFE + 3)) ^ (ANZEIGEN + 1)

		// check phalanx
		if($this->galaxy != LWCore::getPlanet()->galaxy) message('Unerreichbare Koordinaten!');

		$range = (pow(LWCore::getPlanet()->sensor_phalanx, 2) - 1);

		if($this->system < LWCore::getPlanet()->system - $range) message('Unerreichbare Koordinaten!');

		if($this->system > LWCore::getPlanet()->system + $range) message('Unerreichbare Koordinaten!');

		if(LWCore::getPlanet()->deuterium < $this->systemObj->getPhalanxCosts()) message('Zu wenig Deuterium vorhanden!');

		if($this->planetObj->id_owner == WCF::getUser()->userID && WCF::getUser()->userID != 143) message('Du kannst dich nicht selbst phalanxen!');

		if(LWCore::getPlanet()->sensor_phalanx <= 0) message('Unerreichbare Koordinaten!');

		$this->costs = $this->systemObj->getPhalanxCosts();

		$sql = "UPDATE ugml_planets
				SET deuterium = deuterium - ".$this->costs.",
					phalanx_views = phalanx_views + 1
				WHERE id = ".LWCore::getPlanet()->planetID;
		WCF::getDB()->registerShutdownUpdate($sql);

		LWCore::getPlanet()->deuterium -= $this->costs;
		LWCore::getPlanet()->phalanx_views++;

	}

	/**
	 * @see Page::assignVariables
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array('fleets' => $this->fleetObjs,
				'costs' => floor($this->costs)));
	}

	/**
	 * @see Page::show
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) message('Zutritt nicht erlaubt!');

		parent::show();
		echo_foot();
	}
}
?>