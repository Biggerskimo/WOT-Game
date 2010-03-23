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
require_once(LW_DIR.'lib/data/ovent/Ovent.class.php');

/**
 * Hides an ovent from a user
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class HideOventAction extends AbstractAction {
	public $checked = 1;
	public $oventID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['oventID'])) {
			$this->oventID = LWUtil::checkInt($_REQUEST['oventID']);
		}
		
		if(isset($_REQUEST['checked'])) {
			$this->checked = LWUtil::checkInt($_REQUEST['checked'], 0, 1);
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		if (!WCF::getUser()->userID) {
			die('invalid userID');
		}
		
		$ovent = Ovent::getByOventID($this->oventID);
		
		if($ovent === null || $ovent->userID != WCF::getUser()->userID) {
			die('invalid oventID');
		}
		$ovent->getEditor()->check($this->checked);
		
		$this->executed();
		
		die('done');
	}
}
?>