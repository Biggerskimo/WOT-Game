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
require_once(LW_DIR.'lib/data/user/alliance/AllianceEditor.class.php');

/**
 * deletes the application of this user
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceRankDeleteAction extends AbstractAction {
	protected $rankID = 0;
	protected $allianceID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		if(isset($_GET['rankID'])) $this->rankID = intval($_GET['rankID']);
		
		if(isset($_GET['allianceID'])) $this->allianceID = intval($_GET['allianceID']);
		if(!isset($_GET['allianceID']) || $this->allianceID != WCF::getUser()->ally_id) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		// update users
		$sql = "DELETE FROM wcf".WCF_N."_session
				WHERE userID IN(SELECT id
							 	FROM ugml_users
							 	WHERE ally_id = ".$this->allianceID."
							 		AND ally_rank_id = ".($this->rankID + 1).")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "UPDATE ugml_users
				SET ally_rank_id = 0
				WHERE ally_id = ".$this->allianceID."
					AND ally_rank_id = ".($this->rankID + 1);
		WCF::getDB()->sendQuery($sql);
		
		// update alliance
		$alliance = new AllianceEditor($this->allianceID);
		$ranks = $alliance->getRank();
		
		$ranks[$this->rankID] = false;
		$alliance->setRank($ranks);

		$this->executed();

		header('Location: index.php?form=AllianceRankList&allianceID='.$this->allianceID);
		exit;
	}
}
?>