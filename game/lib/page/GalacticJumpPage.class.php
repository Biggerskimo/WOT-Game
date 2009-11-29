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
class GalacticJumpPage extends AbstractPage {
	public $templateName = 'galacticJump';

	protected $fleet = array();
	protected $moonObjs = array();

	/**
	 * Buguser protection
	 */
	protected function checkQueue() {
		global $user;

		check_user();

		if(LWCore::getPlanet()->galactic_jump_time > TIME_NOW) message('Sprungtor noch nicht abgekühlt! (Restdauer: '.gmdate('G:i:s)', LWCore::getPlanet()->galactic_jump_time - TIME_NOW));
		if(LWCore::getPlanet()->planet_type != 3 || LWCore::getPlanet()->quantic_jump <= 0) message('Kein Sprungtor vorhanden!');

		// check
		$sql = "SELECT * FROM ugml_galactic_jump_queue
				WHERE userID = ".WCF::getUser()->userID;

		$result = WCF::getDB()->getResultList($sql);

		//if(count($result) != 1 || $result[0]['time'] != $user['onlinetime'] || $result[0]['state'] != 1) message('Bug-User-Schutz, bitte erneut losschicken!');

		// save
		$shipStr = serialize($this->fleet);

		$sql = "UPDATE ugml_galactic_jump_queue
				SET ships = '".$shipStr."',
					state = 2,
					time = ".TIME_NOW."
				WHERE queueID = ".$result[0]['queueID'];
		WCF::getDB()->registerShutdownUpdate($sql);
	}

	/**
	 * @see Page::readParameters
	 */
	public function readParameters() {
		global $resource;

		parent::readParameters();

		// maxships
		/*foreach($_POST as $key => $row) {
			if(strstr($key, 'maxShip')) {
				unset($_POST[$key]);
				$key = substr($key, 7, 3);
				$maxShips[$key] = intval($row);
			}

		}*/

		// ships
		foreach($_POST as $key => $row) {
			if(strstr($key, 'ship')) {
				$key = substr($key, 4, 3);

				if(intval($row) == 0) continue;

				if($row > LWCore::getPlanet()->{$resource[$key]}) $ships[$key] = LWCore::getPlanet()->{$resource[$key]};
				//else if($row > $maxShips[$key]) $ships[$key] = $maxShips[$key];
				else $ships[$key] = intval($row);
			}
		}

		$this->fleet = $ships;

		$this->checkQueue();
	}

	/**
	 * @see Page::readData
	 */
	public function readData() {
		parent::readData();

		// get moons with galactic jump
		$sql = "SELECT *
				FROM ugml_planets
				WHERE id_owner = ".WCF::getUser()->userID."
					AND planet_type = 3
					AND quantic_jump > 0
					AND id != ".LWCore::getPlanet()->planetID."
				ORDER BY sortID";
		$moons = WCF::getDB()->sendQuery($sql);

		while($moon = WCF::getDB()->fetchArray($moons)) {
			$this->moonObjs[] = Planet::getInstance(null, $moon);
		}

		// calc max distance
		$this->maxDistance = floor(25000 + 8500 * pow(LWCore::getPlanet()->quantic_jump, 1.1));
	}

	/**
	 * @see Page::assignVariables
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array('moons' => $this->moonObjs,
				'thisGJ' => LWCore::getPlanet()->quantic_jump,
				'fleet' => $this->fleet,
				'maxDistance' => $this->maxDistance));
	}

	/**
	 * @see Page::show
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) message('Zutritt nicht erlaubt!');

		if(LWCore::getPlanet()->quantic_jump < 1) message('Kein Sprungtor vorhanden!');

		parent::show();
		echo_foot();
	}
}
?>