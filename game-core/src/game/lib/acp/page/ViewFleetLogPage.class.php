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

// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

// lw imports
require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');

/**
 * Shows game-specific user-actions
 */
class ViewFleetLogPage extends SortablePage {
	public $templateName = 'fleetLog';

	public $userID = 0;
	protected $userObj = null;
	public $fleets = array();

	public $itemsPerPage = 20;
	public $defaultSortField = 'fleet_start_time';
	public $realSortField = '';
	public $defaultSortOrder = 'DESC';

	public $sortField = '';
	public $sortOrder = '';

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();

		$this->readFleetLog();

		$this->userObj = new LWUser($this->userID);
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		//print_r($this);

		WCF::getTPL()->assign(array(
				'userID' => $this->userID,
				'thisUser' => $this->userObj,
				'fleets' => $this->fleets));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.management');

		parent::show();
	}

	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();

		$sql = "SELECT COUNT(*) AS count
				FROM ugml_archive_fleets
				WHERE fleet_owner = ".$this->userID."
					OR fleet_ofiara = ".$this->userID;

		$result = WCF::getDB()->getFirstRow($sql);

		return $result['count'];
	}

	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();

		$this->realSortField = $this->sortField;

		switch($this->sortField) {
			case 'fleet_start_time':
			case 'fleet_mission':
			case 'fleet_owner':
			case 'fleet_ofiara':
				break;
			case 'fleet_resource':
				$this->realSortField = '(fleet_resource_metal + fleet_resource_crystal + fleet_resource_deuterium)';
				break;
			case 'fleet_start_koord':
				$this->realSortField = 'fleet_start_galaxy '.$this->sortOrder.', fleet_start_system '.$this->sortOrder.', fleet_start_planet';
				break;
			case 'fleet_end_koord':
				$this->realSortField = 'fleet_end_galaxy '.$this->sortOrder.', fleet_end_system '.$this->sortOrder.', fleet_end_planet';
				break;
			default:
				$this->realSortField = $this->sortField = $this->defaultSortField;
		}
	}

	/**
	 * Reads the fleetlog
	 */
	public function readFleetLog() {
		$sql = "SELECT fleet.*,
					attacker.id AS attackerID,
					attacker.username AS attackerName,
					defender.id AS defenderID,
					defender.username AS defenderName
				FROM ugml_archive_fleets
					AS fleet
				LEFT JOIN ugml_users
					AS attacker
					ON fleet.fleet_owner = attacker.id
				LEFT JOIN ugml_users
					AS defender
					ON fleet.fleet_ofiara = defender.id
				WHERE fleet_owner = ".$this->userID."
					OR fleet_ofiara = ".$this->userID."
				ORDER BY ".$this->realSortField." ".$this->sortOrder."
				LIMIT ".$this->itemsPerPage."
				OFFSET ".(($this->pageNo - 1) * $this->itemsPerPage);
		$result = WCF::getDB()->sendQuery($sql);

		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->fleets[] = new Fleet(null, $row);
		}
	}

}
?>