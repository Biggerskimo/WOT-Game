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

/**
 * Sends a espionage probe.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class GalaxyEspionageAction extends FleetStartDirectFireAction {
	const ESPIONAGE_PROBE = 210;
	
	public $missionID = 6;
	
	protected $allowSpec = true;
	
	/**
	 * @see FleetStartDirectFireAction::readParametersSpec()
	 */
	public function readParametersSpec() {
		$this->spec[self::ESPIONAGE_PROBE] = min(WCF::getUser()->spio_anz, LWCore::getPlanet()->spy_sonde);
	}
}
?>