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
 * Creates a alliance
 * 
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceCreateForm extends AbstractForm {
	public $templateName = 'allianceCreate';
	
	public $allianceName = null;
	public $allianceTag = null;

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
		
		if(isset($_POST['allianceName'])) $this->allianceName = StringUtil::trim($_POST['allianceName']);
		if(isset($_POST['allianceTag'])) $this->allianceTag = StringUtil::trim($_POST['allianceTag']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if(strlen($this->allianceName) < 3 || strlen($this->allianceName) > 35) {
			throw new UserInputException('allianceName', 'notValid');
		}
		
		if(strlen($this->allianceTag) < 3 || strlen($this->allianceTag) > 8) {
			throw new UserInputException('allianceTag', 'notValid');
		}
		
		// check for existing alliances
		$sql = "SELECT ally_name,
					ally_tag
				FROM ugml_alliance
				WHERE ally_name = '".escapeString($this->allianceName)."'
					OR ally_tag = '".escapeString($this->allianceTag)."'";
		$result = WCF::getDB()->sendQuery($sql);

		while($row = WCF::getDB()->fetchArray($result)) {
			if($row['ally_name'] == $this->allianceName) {
				throw new UserInputException('allianceName', 'notUnique');
				break;
			}
			
			if($row['ally_tag'] == $this->allianceTag) {
				throw new UserInputException('allianceTag', 'notUnique');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		AllianceEditor::create($this->allianceName, $this->allianceTag, WCF::getUser()->userID);
		WCF::getSession()->setUpdate(true);
		
		header('Location: index.php?page=Alliance');
		exit;
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'allianceName' => $this->allianceName,
				'allianceTag' => $this->allianceTag
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