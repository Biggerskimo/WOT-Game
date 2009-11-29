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

/**
 * Sends recycler to harvest a debris.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds
 */
class GalaxyHarvestAction extends FleetStartDirectFireAction {
	const RECYCLER = 209;
	
	public $missionID = 8;
	
	protected $allowSpec = true;
	
	/**
	 * @see FleetStartDirectFireAction::readParametersTarget()
	 */
	public function readParametersTarget() {
		parent::readParametersTarget();
		
		$this->planetKind = 2;
	}
	
	/**
	 * @see FleetStartDirectFireAction::readParametersSpec()
	 */
	public function readParametersSpec() {
		$system = new System($this->galaxy, $this->system);
		$planet = $system->getPlanet($this->planet, 2);
		
		$resources = $planet->metal + $planet->crystal;
		
		$recyclers = min(ceil($resources / 20000), Spec::getSpecObj(self::RECYCLER)->level);
		
		$this->spec[self::RECYCLER] = $recyclers;
	}
}
?>