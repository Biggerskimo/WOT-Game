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
class BuildingKnockDownAction extends AbstractAction {
	protected $buildingID = null;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_GET['buildingID'])) $this->buildingID = intval($_GET['buildingID']);
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

		if(isset($resource[$this->buildingID]) && $this->buildingID < 100 && LWCore::getPlanet()->{$resource[$this->buildingID]} >= 1 && $this->buildingID != 33 && $this->buildingID != 41) {
			$sql = "UPDATE ugml_planets
					SET ".$resource[$this->buildingID]." = ".$resource[$this->buildingID]." - 1
					WHERE id = ".LWCore::getPlanet()->planetID;
			WCF::getDB()->registerShutdownUpdate($sql);

			$this->executed();
		}

		header('Location: ../buildings.php');
		exit;
	}
}
?>