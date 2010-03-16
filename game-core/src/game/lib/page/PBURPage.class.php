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
require_once(LW_DIR.'lib/data/account/PBU.class.php');

/**
 * Shows a small test page ;)
 * 
 * @author Biggerskimo
 * @copyright 2008,2009 Lost Worlds <http://lost-worlds.net>
 */
class PBURPage extends AbstractPage {
	public $template = '';
	
	protected $userID = 0;
	protected $allianceID = 0;
	protected $users = array();
	

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_GET['userID'])) $this->userID = intval($_GET['userID']);
		else if(isset($_GET['allianceID'])) $this->allianceID = intval($_GET['allianceID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		$sql = "SELECT *
				FROM ugml_pbu
				WHERE userID = ".$this->userID."
				ORDER BY pbuID
				DESC
				LIMIT 1";
		$row = WCF::getDB()->getFirstRow($sql);
		
		define('SERVER_ID', 7);
		$pbu = new PBU(null, $row);
			
		echo '<br><br>',$userID,'-',$pbuID,'<br>';
		var_dump($pbu);
		var_dump($pbu->getZipFile(true)->read(1<<27));
		exit;
	}
}
?>