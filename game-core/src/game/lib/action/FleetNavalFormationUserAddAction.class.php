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
require_once(LW_DIR.'lib/data/fleet/NavalFormationEditor.class.php');

/**
 * Deletes a user from a naval formation.
 * 
 * @author		Biggerskimo
 * @copyright 	2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetNavalFormationUserAddAction extends AbstractAction {
	public $navalFormationID = 0;
	public $navalFormation = null;
	
	public $username = '';
	public $userID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['navalFormationID'])) $this->navalFormationID = intval($_REQUEST['navalFormationID']);
		else {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		if(isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
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
		
		$this->navalFormation = new NavalFormation($this->navalFormationID);
		
		// check fleet
		if($this->navalFormation->getLeaderFleet()->ownerID != WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		if($this->navalFormation->usersLimitReached()) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		$user = new LWUser(null, null, $this->username);
		
		if(!$user->userID) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		$this->userID = $user->userID;
		
		$this->navalFormation->getEditor()->addUser($this->userID);
		
		$this->executed();

		header('Location: index.php?page=FleetStartShips');
		exit;
	}
}
?>