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


/**
 * Shows result of a fleet search.
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetListPage extends SortablePage {
	public $templateName = 'fleetList';

	public $searchID = 0;
	
	public $itemsPerPage = 50;
	
	public $fleetIDs = array();
	public $fleets = array();

	public $defaultSortField = 'impactTime';
	public $defaultSortOrder = 'DESC';

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
	
		
		if (isset($_REQUEST['searchID'])) {
			$this->searchID = intval($_REQUEST['searchID']);
			
			if($this->searchID) {
				$this->readSearchResult();
			}
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'fleets' => $this->fleets,
				'searchID' => $this->searchID,
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readFleets();
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wot.acp.menu.link.game.fleet.search');
		
		// check permission
		//WCF::getUser()->checkPermission('admin.user.canSearchFleet');

		parent::show();
	}

	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();

		$sql = "SELECT COUNT(*)
					AS count
				FROM ugml_archive_fleet
				WHERE fleetID IN (".implode(',', $this->fleetIDs).")";
		$result = WCF::getDB()->getFirstRow($sql);

		return $result['count'];
	}

	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();

		switch($this->sortField) {
			case 'fleetID':
			case 'impactTime':
			case 'returnTime':
			case 'startPlanetID':
			case 'targetPlanetID':
			case 'missionID':
				break;
			default:
				$this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * Gets the list of results.
	 */
	protected function readFleets() {
		// get fleets
		$sql = "SELECT *
				FROM ugml_archive_fleet
				WHERE fleetID IN (".implode(',', $this->fleetIDs).")
				ORDER BY ".$this->sortField." ".$this->sortOrder."
				LIMIT ".$this->itemsPerPage."
				OFFSET ".(($this->pageNo - 1) * $this->itemsPerPage);
		$result = WCF::getDB()->sendQuery($sql);
		
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->fleets[] = unserialize(LWUtil::unserialize($row['data']));
		}
	}
	
	/**
	 * Gets the result of the search with the given search id.
	 */
	protected function readSearchResult() {
		// get user search from database
		$sql = "SELECT searchData
				FROM wcf".WCF_N."_search
				WHERE searchID = ".$this->searchID."
					AND userID = ".WCF::getUser()->userID."
					AND searchType = 'fleets'";
		$search = WCF::getDB()->getFirstRow($sql);
		
		if (!isset($search['searchData'])) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		$data = unserialize($search['searchData']);
		$this->fleetIDs = $data['matches'];
	}
}
?>