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

require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(LW_DIR.'lib/data/user/alliance/AllianceEditor.class.php');


/**
 * Shows the page with a list with ranks, where the rights can be editted
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceRankListForm extends AbstractForm {
	public $templateName = 'allianceRankList';
	
	protected $allianceID = 0;
	protected $alliance = null;
	
	protected $ranks = array();

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_GET['allianceID'])) $this->allianceID = intval($_GET['allianceID']);
		if(!isset($_GET['allianceID']) || $this->allianceID != WCF::getUser()->ally_id) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
	
		// existing ranks
		foreach($this->ranks as $rankID => $rank) {
			foreach($rank as $rightID => $value) {
				// rank name	
				if($rightID == 0) {
					if(!empty($_POST['rank'.$rankID.'right'.$rightID])) {
						$this->ranks[$rankID][0] = StringUtil::trim($_POST['rank'.$rankID.'right'.$rightID]);	
					}
					
				// right value
				} else {
					if(isset($_POST['rank'.$rankID.'right'.$rightID])) $this->ranks[$rankID][$rightID] = true;
					else $this->ranks[$rankID][$rightID] = false;
				}
			}
		}
		
		// new rank
		if(!empty($_POST['rankNewright0'])) {
			$newRank = array();
			
			// name
			$newRank[0] = StringUtil::trim($_POST['rankNewright0']);
			
			if(empty($newRank[0])) $newRank[0] = WCF::getLanguage()->get('wot.alliance.rank');
			
			// rights
			for($rightID = 1; $rightID <= 9; ++$rightID) {
				if(isset($_POST['rankNewright'.$rightID])) $newRank[$rightID] = true;
				else $newRank[$rightID] = false;
			}
			
			$this->ranks[] = $newRank;
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {		
		$this->alliance = new AllianceEditor($this->allianceID);
		
		$this->ranks = $this->alliance->getRank();
		
		parent::readData();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		$this->alliance->setRank($this->ranks);
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'alliance' => $this->alliance
		));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}

		$_SERVER['HTTP_ACCEPT'] = str_replace('platzhalter', 'application/xhtml+xml', $_SERVER['HTTP_ACCEPT']);
		parent::show();
		//echo_foot();
	}
}
?>