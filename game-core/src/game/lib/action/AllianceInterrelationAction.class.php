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
 * Manages the interrelations of a alliance.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceInterrelationAction extends AbstractAction {
	protected $allianceID = 0;
	protected $alliance = null;
	
	protected $allianceID2 = 0;
	protected $alliance2 = null;
	
	public $interrelationType = 0;
	public $interrelationState = 0;
	
	protected $interrelations = array();

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['allianceID'])) $this->allianceID = intval($_REQUEST['allianceID']);
		else {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		if(isset($_REQUEST['allianceID2'])) $this->allianceID2 = intval($_REQUEST['allianceID2']);
		else {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		if(isset($_REQUEST['interrelationType'])) $this->interrelationType = intval($_REQUEST['interrelationType']);
		if(isset($_REQUEST['interrelationState'])) $this->interrelationState = intval($_REQUEST['interrelationState']);
		
		if($this->allianceID != WCF::getUser()->ally_id) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		$this->alliance = Alliance::getByUserID(WCF::getUser()->userID, true);
		
		$this->interrelations = $this->alliance->getInterrelation();
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		if (!WCF::getUser()->userID || !$this->alliance->getRank(true, 6)) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		$this->alliance->addInterrelation($this->allianceID2, $this->interrelationType, $this->interrelationState, null);

		$this->executed();

		header('Location: index.php?page=AllianceDiplomacy&allianceID='.$this->allianceID);
		exit;
	}
}
?>