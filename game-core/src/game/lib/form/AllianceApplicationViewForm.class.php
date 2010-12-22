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
require_once(LW_DIR.'lib/data/message/MessageEditor.class.php');
require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');

/**
 * Views a application and saves if it has been agreed or disagreed.
 * 
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceApplicationViewForm extends AbstractForm {
	const ANSWER_TEXT_LENGTH = 5000;
	
	public $templateName = 'allianceApplicationView';
	
	public $alliance = null;
	
	public $userID = 0;
	public $user = null;
	
	public $agreed = null;
	public $answerText = '';

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_GET['userID'])) $this->userID = intval($_GET['userID']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if(isset($_POST['agreed'])) $this->agreed = (bool)$_POST['agreed'];
		if(isset($_POST['answerText'])) $this->answerText = StringUtil::trim($_POST['answerText']);
	
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {	
		// alliance
		$this->alliance = Alliance::getByUserID(WCF::getUser()->userID, true);
		
		// user
		if(empty($this->userID)) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		$this->user = new LWUser($this->userID);
		
		
		parent::readData();
	}


	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if($this->agreed === null) {
			throw new UserInputException('agreed', 'notValid');
		}
		
		if(strlen($this->answerText) > self::ANSWER_TEXT_LENGTH) {
			throw new UserInputException('answerText', 'notValid');
		}
		
		// check for application
		$sql = "SELECT COUNT(*)
						AS count
				FROM ugml_users
				WHERE ally_request = ".WCF::getUser()->ally_id."
					AND id = ".$this->userID;
		$result = WCF::getDB()->getFirstRow($sql);
		
		if($result['count'] != 1) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		if($this->agreed) {
			// send message to alliance
			if(!empty($this->answerText)) $message = WCF::getLanguage()->get('wot.alliance.newMember.alliance', array('answer' => $this->answerText, 'username' => $this->user->username));
			else $message = WCF::getLanguage()->get('wot.alliance.newMember.alliance.noAnswer', array('username' => $this->user->username));
			
			$this->alliance->sendMessageToAll(null, $message);
			
			// insert into alliance
			$this->alliance->addUser($this->userID);
			
			// send message to user
			if(!empty($this->answerText)) $message = WCF::getLanguage()->get('wot.alliance.newMember.user', array('answer' => $this->answerText, 'alliance' => $this->alliance));
			else $message = WCF::getLanguage()->get('wot.alliance.newMember.user.noAnswer', array( 'alliance' => $this->alliance));
			
			MessageEditor::create($this->userID, WCF::getLanguage()->get('wot.alliance.application'), $message, 0, $this->alliance, 0);
		} else {
			// update user
			$sql = "UPDATE ugml_users
					SET ally_request = NULL,
						ally_request_text = '',
						ally_register_time = 0
					WHERE id = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
			
			$sql = "DELETE FROM wcf".WCF_N."_session
					WHERE userID = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
			
			// send message to user
			if(!empty($this->answerText)) $message = WCF::getLanguage()->get('wot.alliance.application.disagreed', array('answer' => $this->answerText, 'alliance' => $this->alliance));
			else $message = WCF::getLanguage()->get('wot.alliance.application.disagreed.noAnswer', array('alliance' => $this->alliance));
			
			MessageEditor::create($this->userID, WCF::getLanguage()->get('wot.alliance.applicationView.application'), $message, 0, $this->alliance, 0);
		}
		
		
		header('Location: index.php?page=AllianceApplicationsList');
		exit;
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'application' => $this->user->ally_request_text,
				'user' => $this->user,
				'answerText' => $this->answerText
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