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

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

// lw imports
require_once(LW_DIR.'lib/data/user/LWUser.class.php');

/**
 * Shows game-specific user-actions
 */
class GameUserPage extends AbstractPage {
	public $templateName = 'gameUser';
	public $userID = 0;
	public $user = null;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['userID'])) {
			$this->userID = intval($_REQUEST['userID']);
		}
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();

		$this->user = new LWUser($this->userID);
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'userID' => $this->userID,
				'thisUser' => $this->user));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.management');

		parent::show();
	}

}
?>