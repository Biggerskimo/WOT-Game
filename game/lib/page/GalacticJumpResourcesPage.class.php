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
 * Shows the first galactic jump page.
 */
class GalacticJumpResourcesPage extends AbstractPage {
	public $templateName = 'galacticJumpResources';

	protected $moonID = null;
	protected $moonObj = null;
	protected $distance = 0;

	/**
	 * @see Page::readParameters
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_POST['targetmoon'])) $this->moonID = intval($_POST['targetmoon']);
		else message('Mond ausw&auml;hlen!');

		$this->getData();
	}

	/**
	 * Buguser protection
	 */
	protected function checkQueue() {
		global $user;

		check_user();

		// check
		$sql = "SELECT * FROM ugml_galactic_jump_queue
				WHERE userID = ".WCF::getUser()->userID;

		$result = WCF::getDB()->getResultList($sql);

		$this->fleet = unserialize($result[0]['ships']);

		//if(count($result) != 1 || $result[0]['time'] != $user['onlinetime'] || $result[0]['state'] != 2) message('Bug-User-Schutz, bitte erneut losschicken!');

		// save
		$sql = "UPDATE ugml_galactic_jump_queue
				SET endPlanetID = ".$this->moonObj->planetID.",
					state = 3,
					time = ".TIME_NOW."
				WHERE queueID = ".$result[0]['queueID'];
		WCF::getDB()->registerShutdownUpdate($sql);
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
	 * @see Page::readData
	 */
	public function getData() {
		$this->moonObj = Planet::getInstance($this->moonID);

		$this->checkQueue();

		$this->calcDistance();

		$this->calcStorage();

		// calc max distance
		$this->maxDistance = floor(25000 + 8500 * pow(LWCore::getPlanet()->quantic_jump, 1.1));
	}

	/**
	 * @see Page::assignVariables
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array('distance' => $this->distance,
				'maxDistance' => $this->maxDistance,
				'thisGJ' => LWCore::getPlanet()->quantic_jump,
				'storage' => $this->storage));
	}

	/**
	 * @see Page::show
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) message('Zutritt nicht erlaubt!');

		if($this->maxDistance < $this->distance) message('Zielmond zu weit entfernt!');

		if($this->moonObj->galactic_jump_time > TIME_NOW) message('Sprungtor auf Zielmond noch nicht abgek&uuml;hlt!');

		parent::show();
		echo_foot();
	}
}
?>