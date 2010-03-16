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
require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');

/**
 * Creates a new circular to the own alliance and interrelations of this alliance
 * 
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceCircularCreateForm extends AbstractForm {
	const CIRCULAR_TEXT_LENGTH = 5000;
	
	public $templateName = 'allianceCircularCreate';
	
	public $alliance = null;
	protected $interrelations = array();
	protected $alliances = array();
	
	public $circularText = '';

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		//print_r($this);
		
		//print_r($_POST);
		
		if(isset($_POST['circularText'])) $this->circularText = StringUtil::trim($_POST['circularText']);
	
		if(isset($_POST['alliance'.$this->alliance->allianceID])) {
			$this->alliances[$this->alliance->allianceID] = intval($_POST['alliance'.$this->alliance->allianceID.'Rank']);
		}
		
		foreach($this->interrelations as $allianceID2 => $alliance2) {
			if(isset($_POST['alliance'.$allianceID2])) {
				$this->alliances[$allianceID2] = intval($_POST['alliance'.$allianceID2.'Rank']);
			}
		}
		
		//print_r($this->alliances);
		
		//exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {	
		// alliance
		$this->alliance = Alliance::getByUserID(WCF::getUser()->userID, true);
		$this->interrelations = $this->alliance->getInterrelation();
		$this->interrelations[$this->alliance->allianceID] = $this->alliance;
		
		parent::readData();
	}


	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if(strlen($this->circularText) > self::CIRCULAR_TEXT_LENGTH) {
			throw new UserInputException('answerText', 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		foreach($this->alliances as $allianceID2 => $alliance2) {
			$this->interrelations[$allianceID2]->sendMessageToAll(null, $this->circularText, $alliance2, $this->alliance);
		}
				
		header('Location: index.php?page=Alliance');
		exit;
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'allianceID' => $this->alliance->allianceID,
				'alliance' => $this->alliance,
				'interrelations' => $this->interrelations
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