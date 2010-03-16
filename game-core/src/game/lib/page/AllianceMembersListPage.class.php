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

require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');

/**
 * Lists the members of an alliance.
 * 
 * @author		Biggerskimo
 * @copyright	2008,2009 Lost Worlds <http://lost-worlds.net>
 */
class AllianceMembersListPage extends SortablePage {
	public $templateName = 'allianceMembersList';
	
	protected $alliance = null;
	protected $members = array();

	public $itemsPerPage = 200;
	public $defaultSortField = 'username';
	public $defaultSortOrder = 'ASC';

	public $sortField = '';
	public $sortOrder = '';
	
	public $realSortField = '';

	/**
	 * @see Page::readParameters
	 */
	public function readParameters() {
		parent::readParameters();

		$this->alliance = Alliance::getByUserID(WCF::getUser()->userID);
	}

	/**
	 * @see Page::readData
	 */
	public function readData() {
		parent::readData();
		
		if($this->alliance === null) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
		if(!$this->alliance->getRank(true, 4)) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		// get members
		$sql = "SELECT id
				FROM ugml_users
				LEFT JOIN ugml_stat_entry
					ON ugml_users.id = ugml_stat_entry.relationalID
						AND ugml_stat_entry.statTypeID = 1
				WHERE ally_id = ".$this->alliance->allianceID."
				ORDER BY ".$this->realSortField." ".$this->sortOrder."
				LIMIT ".$this->itemsPerPage."
					OFFSET ".(($this->pageNo - 1) * $this->itemsPerPage);
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$this->members[$row['id']] = new LWUser($row['id']);
		}
	}

	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();

		$sql = "SELECT COUNT(*) AS count
				FROM ugml_users
				WHERE ally_id = ".$this->alliance->allianceID;
		$result = WCF::getDB()->getFirstRow($sql);

		return $result['count'];
	}

	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch($this->sortField) {
			case 'username':
			case 'points':
			case 'ally_rank_id':
			case 'ally_register_time':
			case 'onlinetime':
				$this->realSortField = $this->sortField;
				break;
			case 'coordinates':
				$this->realSortField = 'galaxy '.$this->sortOrder.', system '.$this->sortOrder.', planet ';
				break;
			default:
				$this->realSortField = $this->sortField = $this->defaultSortField;
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
				'alliance' => $this->alliance,
				'users' => $this->members,
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
		//echo_foot();
	}
}
?>