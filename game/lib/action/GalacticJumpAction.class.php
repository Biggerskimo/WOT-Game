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

/**
 * knock downs a building
 */
class GalacticJumpAction extends AbstractAction {
	protected $metal = 0;
	protected $crystal = 0;
	protected $deuterium = 0;
	protected $finishCoolDownTime = TIME_NOW;
	protected $neededDeuterium = 0;

	/**
	 * Buguser protection
	 */
	protected function checkQueue() {
		global $resource, $user;

		check_user();

		// check
		$sql = "SELECT * FROM ugml_galactic_jump_queue
				WHERE userID = ".WCF::getUser()->userID;

		$result = WCF::getDB()->getResultList($sql);

		if(count($result) != 1 || $result[0]['time'] != $user['onlinetime'] || $result[0]['state'] != 3) message('Bug-User-Schutz, bitte erneut losschicken!');

		$this->fleet = unserialize($result[0]['ships']);
		foreach($this->fleet as $shipTypeID => $count) {
			if(LWCore::getPlanet()->{$resource[$shipTypeID]} < $count) message('Zu viele Schiffe ausgew&auml;hlt!');
		}

		$this->moonObj = Planet::getInstance($result[0]['endPlanetID']);

		// save
		$sql = "DELETE FROM ugml_galactic_jump_queue
				WHERE queueID = ".$result[0]['queueID'];
		WCF::getDB()->registerShutdownUpdate($sql);
	}

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_POST['resource1'])) $this->metal = intval($_POST['resource1']);
		if(isset($_POST['resource2'])) $this->crystal = intval($_POST['resource2']);
		if(isset($_POST['resource3'])) $this->deuterium = intval($_POST['resource3']);

		$this->getData();
	}

	/**
	 * Calculates the distance
	 */
	protected function calcDistance() {
		if(($this->moonObj->galaxy - LWCore::getPlanet()->galaxy) != 0) {
			$dist = abs($this->moonObj->galaxy - LWCore::getPlanet()->galaxy) * 20000;
		} else if(($this->moonObj->system - LWCore::getPlanet()->system) != 0) {
			$dist = abs($this->moonObj->system - LWCore::getPlanet()->system) * 5 * 19 + 2700;
		} else if(($this->moonObj->planet - LWCore::getPlanet()->planet) != 0) {
			$dist = abs($this->moonObj->planet - LWCore::getPlanet()->planet) * 5 + 1000;
		}
		$this->distance = $dist;
	}

	/**
	 * Calculats the storage of the ships
	 */
	protected function calcStorage() {
		global $pricelist;

		foreach($this->fleet as $shipTypeID => $count) {
			$this->storage += $pricelist[$shipTypeID]['capacity'] * $count;
		}
	}

	/**
	 * Calculates the time when the cool down is finished.
	 */
	protected function calcCoolDownTime() {
		$gJ = LWCore::getPlanet()->quantic_jump;
		$ress = ($this->metal + $this->crystal + $this->deuterium);

		$time = (($this->distance / 100000) * pow($ress, 0.05) + 1) * pow(0.95, $gJ) * 3600;
		$this->finishCoolDownTime = $time + TIME_NOW;
	}

	/**
	 * Calculates the needed deuterium
	 */
	protected function calcNeededDeuterium() {
		$gJ = LWCore::getPlanet()->quantic_jump;
		$ress = ($this->metal + $this->crystal + $this->deuterium);

		$neededDeuterium = ceil((pow(1.000007, $ress) * $this->distance / 10000 + $ress * 0.1) * pow(0.99, $gJ));
		$this->neededDeuterium = $neededDeuterium;
	}

	/**
	 * @see Page::readData()
	 */
	protected function getData() {
		$this->checkQueue();
		$this->calcDistance();
		$this->calcStorage();
		$this->calcCoolDownTime();
		$this->calcNeededDeuterium();
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		global $resource;

		parent::execute();

		// check permission
		if (!WCF::getUser()->userID) {
			message('Einloggen!');
		}

		if(($this->metal + $this->crystal + $this->deuterium) > $this->storage) message('Zu viele Ressourcen ausgew&auml;hlt!');
		if($this->metal > LWCore::getPlanet()->metal) message('Zu wenig Metall vorhanden!');
		if($this->crystal > LWCore::getPlanet()->crystal) message('Zu wenig Kristall vorhanden!');
		if(($this->deuterium + $this->neededDeuterium) > LWCore::getPlanet()->deuterium) message('Zu wenig Deuterium vorhanden!');

		// target planet
		$shipStr = "";
		foreach($this->fleet as $shipTypeID => $count) {
			$shipStr .= $resource[$shipTypeID]." = ".$resource[$shipTypeID]." + ".intval($count).", ";
		}

		$sql = "UPDATE ugml_planets
				SET ".$shipStr."
					metal = metal + ".$this->metal.",
					crystal = crystal + ".$this->crystal.",
					deuterium = deuterium + ".$this->deuterium.",
					galactic_jump_time = ".$this->finishCoolDownTime."
				WHERE id = ".$this->moonObj->planetID;
		WCF::getDB()->sendQuery($sql);

		// start planet
		$shipStr = "";
		foreach($this->fleet as $shipTypeID => $count) {
			$shipStr .= $resource[$shipTypeID]." = ".$resource[$shipTypeID]." - ".intval($count).", ";
		}

		$sql = "UPDATE ugml_planets
				SET ".$shipStr."
					metal = metal - ".$this->metal.",
					crystal = crystal - ".$this->crystal.",
					deuterium = deuterium - ".($this->neededDeuterium + $this->deuterium).",
					galactic_jump_time = ".$this->finishCoolDownTime."
				WHERE id = ".LWCore::getPlanet()->planetID;
		WCF::getDB()->sendQuery($sql);

		WCF::getUser()->changePlanet($this->moonObj->planetID, 'UserMoon');

		$this->executed();

		header('Location: game/index.php?page=FleetStartShips');
		exit;
	}
}
?>