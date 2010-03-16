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

require_once(LW_DIR.'lib/data/fleet/EspionageFleet.class.php');
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Gets the last scan of this planet by a owner
 */
class ViewScanPage extends AbstractPage {
	public $templateName = 'galaxyScan';

	protected $planetID = 0;
	public $planetObj = null;
	public $report = null;
	public $reports = array();

	/**
	 * @see Page::readParameters
	 */
	public function readParameters() {
		parent::readParameters();

		require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');

		if(isset($_GET['planetID'])) $this->planetID = intval($_GET['planetID']);
		else throw new IllegalLinkException();
	}

	/**
	 * @see Page::readData
	 */
	public function readData() {
		global $user;
		
		parent::readData();
		
		$features = unserialize(WCF::getUser()->diliziumFeatures);
		if(!isset($features['galaxyScans']) || $features['galaxyScans'] <= TIME_NOW) {
			message('Dir steht diese Funktion nicht zur Verf&uuml;gung!');
		} 
		
		// get planet
		$this->planetObj = Planet::getInstance($this->planetID);

		$subject = 'Spionagebericht von '.$this->planetObj;
		$this->reports = EspionageFleet::searchReports(WCF::getUser()->userID, $this->planetID);
		
		if(!count($this->reports)) message('Kein Spionagebericht gefunden!');
		
	}

	/**
	 * @see Page::assignVariables
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array('reports' => $this->reports,
						'planetID' => $this->planetID));
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