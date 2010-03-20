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
require_once(WCF_DIR.'lib/system/session/Session.class.php');;
require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/util/PlanetUtil.class.php');

/**
 * Shows and handles actions to rename or delete a planet.
 * 
 * @author	Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class PlanetActionsForm extends AbstractForm {
	public $templateName = 'planetActions';
	
	private $newName = array();
	private $password = array();
		
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if($this->action == 'rename') {
			if(isset($_POST['newName'])) $this->newName = StringUtil::trim($_POST['newName']);			
		}
		else if($this->action == 'delete') {
			if(isset($_POST['password'])) $this->password = StringUtil::trim($_POST['password']);			
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if($this->action == 'rename') {
			if(!PlanetUtil::isValid($this->newName)) {
				throw new UserInputException('newName', 'notValid');
			}
		}
		else if($this->action == 'delete') {
			// main planet
			if(LWCore::getPlanet()->planetID == WCF::getUser()->id_planet) {
				throw new SystemException('tried to delete main planet');
			}
			
			// password
			if(!WCF::getUser()->checkPassword($this->password)) {
				throw new UserInputException('password', 'notValid');
			}
			
			// check fleets (moon, if existing and planet)
			if(LWCore::getPlanet()->planetKind == 1 && LWCore::getPlanet()->getMoon() != null) {
				if(count(Fleet::getByPlanetID(LWCore::getPlanet()->getMoon()->planetID, Fleet::OFIARA | Fleet::OWNER))) {
					throw new UserInputException('password', 'activityMoon');
				}
			}
			
			// check current
			if(count(Fleet::getByPlanetID(LWCore::getPlanet()->planetID, Fleet::OFIARA | Fleet::OWNER))) {
				throw new UserInputException('password', 'activity');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		if($this->action == 'rename') {
			LWCore::getPlanet()->getEditor()->rename($this->newName);
		}
		else if($this->action == 'delete') {
			if(LWCore::getPlanet()->planetKind == 1 && LWCore::getPlanet()->getMoon() != null) {
				LWCore::getPlanet()->getMoon()->getEditor()->delete();
			}
			LWCore::getPlanet()->getEditor()->delete();
			
			$sql = "UPDATE ugml_users
					SET current_planet = id_planet
					WHERE id = ".WCF::getUser()->userID;
			WCF::getDB()->sendQuery($sql);
			
			Session::resetSessions(WCF::getUser()->userID);
			
			$this->saved();
			header('Location: index.php?page=Overview&cp='.WCF::getUser()->id_planet);
			exit;
		}
		$this->saved();
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
		
		parent::show();
	}
}
?>