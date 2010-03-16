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
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');


/**
 * Shows the revisions of a fleet.
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetViewPage extends AbstractPage {
	public $templateName = 'fleetView';

	public $fleetID = 0;
	public $fleetData = array();

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
			
		if (isset($_REQUEST['fleetID'])) {
			$this->fleetID = intval($_REQUEST['fleetID']);
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'fleetID' => $this->fleetID,
				'fleetData' => $this->fleetData
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$sql = "SELECT *
				FROM ugml_archive_fleet
				WHERE fleetID = ".$this->fleetID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		$this->fleetData = unserialize(LWUtil::unserialize($row['data']));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wot.acp.menu.link.game.fleet.search');
		
		parent::show();
	}
}
?>