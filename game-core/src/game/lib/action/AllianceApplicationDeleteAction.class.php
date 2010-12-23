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

/**
 * deletes the application of this user
 * 
 * @author	Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceApplicationDeleteAction extends AbstractAction {
	protected $userID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		if(isset($_GET['userID'])) $this->userID = intval($_GET['userID']);
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		if($this->userID != WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		

		$sql = "UPDATE ugml_users
				SET ally_request = NULL,
					ally_request_text = ''
				WHERE id = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		WCF::getSession()->setUpdate(true);

		$this->executed();

		header('Location: index.php?page=Alliance');
		exit;
	}
}
?>