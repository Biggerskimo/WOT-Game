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

require_once(LW_DIR.'lib/form/AllianceApplyForm.class.php');


/**
 * Inserts a application for a interrelation to a alliance.
 * 
 * Unlike the most alliance pages/forms/actions,
 *  the own alliance id is handled as allianceID2 (alliance2) for a compatibility
 *  to the normal apply form
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceInterrelationApplyForm extends AllianceApplyForm {
	public $templateName = 'allianceInterrelationApply';
	
	public $allianceID2 = 0;
	public $alliance2 = null;
	
	public $interrelationType = 1;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(!isset($_GET['allianceID'])) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		if(isset($_GET['allianceID2'])) $this->allianceID2 = intval($_GET['allianceID2']);
	
		if(!isset($_GET['allianceID2']) || $this->allianceID2 != WCF::getUser()->ally_id || $this->allianceID == $this->allianceID2) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
	
		if(isset($_POST['interrelationType'])) $this->interrelationType = intval($_POST['interrelationType']);
	}

	/**
	 * @see Form::save()
	 */
	public function save() {
		global $game_config;
	
		AbstractForm::save();
		
		$data = LWUtil::serialize(array('text' => $this->applicationText), 1);
		$this->alliance2->addInterrelation($this->allianceID, $this->interrelationType, 1, $data);
		
		if($this->interrelationType == 3) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.alliance.diplomacy.newWar', array('boardURL' => $game_config['diplomacyBoardURL'], 'boardID' => $game_config['diplomacyBoardID'])));
		}
		
		header('Location: index.php?page=Alliance');
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		$this->alliance = new Alliance($this->allianceID);
		$this->alliance2 = new AllianceEditor($this->allianceID2);

		if($this->alliance2->getInterrelation($this->allianceID, $this->interrelationType) !== null) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		
		if(!$this->alliance2->getRank(true, 6)) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		AbstractForm::readData();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$applicationTemplate = $this->applicationText;

		WCF::getTPL()->assign(array(
				'allianceID2' => $this->allianceID2,
				'alliance2' => $this->alliance2,
				'applicationTemplate' => $applicationTemplate
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
	}
}
?>