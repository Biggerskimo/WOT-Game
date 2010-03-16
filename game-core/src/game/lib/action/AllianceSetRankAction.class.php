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

require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');

/**
 * Sets a rank of a user in this alliance.
 * 
 * @author		Biggerskimo
 * @copyright 	2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceSetRankAction extends AbstractAction {
	protected $allianceID = 0;
	protected $alliance = null;
	protected $userID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['allianceID'])) $this->allianceID = LWUtil::checkInt($_REQUEST['allianceID']);
		else {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		if(isset($_REQUEST['userID'])) $this->userID = LWUtil::checkInt($_REQUEST['userID']);
		else {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		if(isset($_REQUEST['rankID'])) $this->rankID = LWUtil::checkInt($_REQUEST['rankID'], -1);
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
				
		$this->alliance = Alliance::getByUserID($this->userID, true);
		$this->user = new LWUser($this->userID);
		
		// can not edit users of other alliances		
		if($this->user->ally_id != $this->allianceID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
	
		// not allowed
		if(!$this->alliance->getRank(true, 6)) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		// change leader
		if($this->rankID == -1) {
			if(WCF::getUser()->userID != $this->alliance->ally_owner) {
				require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
				throw new PermissionDeniedException();
			}
			
			$this->alliance->changeLeader($this->userID);
		}
		
		// change normal rank
		else {
			if($this->alliance->getRank($this->rankID - 1) === null && $this->rankID != 0) {
				require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
				throw new IllegalLinkException();
			}
			
			$sql = "UPDATE ugml_users
					SET ally_rank_id = ".$this->rankID."
					WHERE id = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
			
			$sql = "DELETE FROM wcf".WCF_N."_session
					WHERE userID = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
		}
		
		$this->executed();

		header('Location: index.php?page=AllianceMembersList');
		exit;
	}
}
?>