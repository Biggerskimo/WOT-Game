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

require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');


/**
 * Shows the alliance start page.
 * 
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class AlliancePage extends AbstractPage {
	public $templateName = 'alliance';
	
	protected $allianceID = 0;
	protected $alliance = null;
	protected $applicationsCount = 0;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_GET['allianceID'])) $this->allianceID = intval($_GET['allianceID']);
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();

		if(!empty($this->allianceID)) $this->alliance = new Alliance($this->allianceID);
		else $this->alliance = Alliance::getByUserID(WCF::getUser()->userID);
		
		// no such alliance
		if($this->alliance === null || $this->alliance->id <= 0) {
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			//$_SERVER['HTTP_ACCEPT'] = str_replace('platzhalter', 'application/xhtml+xml', $_SERVER['HTTP_ACCEPT']);
			
			// user has no alliance
			if(empty($this->allianceID)) {
				// waiting for a answer to the application
				if(WCF::getUser()->ally_request) {
					$alliance = new Alliance(WCF::getUser()->ally_request);
					throw new NamedUserException(WCF::getLanguage()->get('wot.alliance.waitingForApplicationAnswer', array('allianceID' => $alliance->allianceID, 'allianceTag' => $alliance->ally_tag, 'userID' => WCF::getUser()->userID)));
				}
			
				throw new NamedUserException(WCF::getLanguage()->get('wot.alliance.notMember'));
			}
			
			// requested alliance does not exist
			throw new NamedUserException(WCF::getLanguage()->get('wot.alliance.notExisting'));
		}
		
		// applications
		if($this->alliance->getRank(true, 3)) {
			$sql = "SELECT COUNT(*) AS count
					FROM ugml_users
					WHERE ally_request = ".$this->alliance->allianceID;
			$result = WCF::getDB()->getFirstRow($sql);
			
			$this->applicationsCount = $result['count'];
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'alliance' => $this->alliance,
				'leader' => new LWUser($this->alliance->ally_owner),
				'applicationsCount' => $this->applicationsCount
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

		//$_SERVER['HTTP_ACCEPT'] = str_replace('platzhalter', 'application/xhtml+xml', $_SERVER['HTTP_ACCEPT']);
		parent::show();
	}
}
?>