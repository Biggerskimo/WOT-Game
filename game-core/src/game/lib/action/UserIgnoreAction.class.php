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
require_once(LW_DIR.'lib/data/message/NMessage.class.php');

/**
 * Provides actions to do with a espionage report.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class UserIgnoreAction extends AbstractAction {
	public $userID = 0;
	public $doIgnore = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['userID'])) {
			$this->userID = intval($_REQUEST['userID']);
		}
		
		if(isset($_REQUEST['doIgnore'])) {
			$this->doIgnore = intval($_REQUEST['doIgnore']);
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		if (!WCF::getUser()->userID)
			die('invalid userID');
		
		if(!$this->userID)
			die('invalid userID');
			
		$user = new LWUser($this->userID);
		if(!$user->userID)
			die('invalid userID');
		
		if($this->doIgnore)
		{
			$sql = "INSERT IGNORE INTO
					ugml_user_ignore
					(senderID, recipentID)
					VALUES
					(".$this->userID.", ".WCF::getUser()->userID.")";
			WCF::getDB()->sendQuery($sql);
		}
		else {
			$sql = "DELETE FROM
					ugml_user_ignore
					WHERE senderID = ".$this->userID."
						AND recipentID = ".WCF::getUser()->userID;
			WCF::getDB()->sendQuery($sql);
		}
		
		$this->executed();
		
		die('done');
	}
}
?>