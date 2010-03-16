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

require_once(LW_DIR.'lib/action/FleetStartDirectFireAction.class.php');
require_once(LW_DIR.'lib/data/system/System.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');

/**
 * Fires interplanetary missiles
 * 
 * @author		Biggerskimo
 * @copyright	Lost Worlds 2008 <http://lost-worlds.net>
 */
class FireInterplanetaryMissilesAction extends FleetStartDirectFireAction {
	protected $fleetQueueClassName = 'MissileFleetQueue';
	
	public $primaryDestination = 0;
	
	public $missionID = 20;
	
	protected $lookForSpecType = 51;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_REQUEST['primaryDestination'])) {
			$this->primaryDestination = LWUtil::checkInt($_REQUEST['primaryDestination']);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->getFleetQueue()->primaryDestination = $this->primaryDestination;
	}

	/**
	 * @see Action::executed()
	 */
	public function executed() {
		parent::executed();

		header('Location: ../galaxy.php?g='.$this->galaxy.'&s='.$this->system);
		exit;
	}
}
?>