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
require_once(LW_DIR.'lib/data/user/LWUser.class.php');


/**
 * Inserts a application to a alliance
 * 
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceApplyForm extends AbstractForm {
	const APPLICATION_TEXT_LENGTH = 10000;

	public $templateName = 'allianceApply';
	
	public $allianceID = 0;
	public $alliance = null;
	public $applicationText = '';

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_GET['allianceID'])) $this->allianceID = intval($_GET['allianceID']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if(isset($_POST['applicationText'])) $this->applicationText = StringUtil::trim($_POST['applicationText']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if(strlen($this->applicationText) > self::APPLICATION_TEXT_LENGTH) {
			throw new UserInputException('applicationText', 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$sql = "UPDATE ugml_users
				SET ally_request = ".$this->allianceID.",
					ally_request_text = '".escapeString($this->applicationText)."',
					ally_register_time = ".TIME_NOW."
				WHERE id = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		WCF::getSession()->setUpdate(true);
		
		header('Location: index.php?page=Alliance');
		exit;
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->alliance = new Alliance($this->allianceID);
		
		if(!$this->alliance->allianceID || WCF::getUser()->ally_request || WCF::getUser()->ally_id) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		if(!empty($this->applicationText)) $applicationTemplate = $this->applicationText;
		else $applicationTemplate = $this->alliance->ally_request;
		
		WCF::getTPL()->assign(array(
				'allianceID' => $this->allianceID,
				'allianceTag' => $this->alliance->ally_tag,
				'applicationText' => $this->applicationText,
				'textLength' => self::APPLICATION_TEXT_LENGTH,
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