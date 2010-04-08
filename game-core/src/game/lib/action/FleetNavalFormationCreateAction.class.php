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

require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/fleet/NavalFormationEditor.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOvent.class.php');

/**
 * Creates a new naval formation for a naval formation.
 * 
 * @author		Biggerskimo
 * @copyright 	2008 - 2010 Lost Worlds <http://lost-worlds.net>
 */
class FleetNavalFormationCreateAction extends AbstractAction {
	protected $fleetID = 0;
	protected $fleet = null;
	protected $navalFormation = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['fleetID'])) $this->fleetID = intval($_REQUEST['fleetID']);
		else {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
				
		$this->fleet = Fleet::getInstance($this->fleetID);
		
		// check fleet
		if($this->fleet->impactTime <= TIME_NOW || $this->fleet->ownerID != WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		$this->navalFormation = NavalFormationEditor::create($this->fleetID, $this->fleet->ownerID);
		
		FleetOvent::update($this->fleet);
		
		$this->executed();
		
		header('Location: index.php?page=FleetStartShips');
		exit;
	}
}
?>