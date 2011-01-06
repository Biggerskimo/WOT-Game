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


/**
 * This page sends a list of users
 * 
 * @author		Biggerskimo
 * @copyright	2011 Lost Worlds <http://lost-worlds.net>
 */
class MessageUserSuggestPage extends AbstractPage {
	public $templateName = '';
	
	public $input = "";
	public $users = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['q']))
			$this->input = StringUtil::trim($_REQUEST['q']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$sql = "SELECT username,
					ugml_planets.galaxy,
					ugml_planets.system,
					ugml_planets.planet,
					ugml_planets.name
				FROM ugml_users
				LEFT JOIN ugml_planets
					ON ugml_users.id_planet = ugml_planets.id
				WHERE username LIKE '".escapeString($this->input)."%'";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result))
		{
			$this->users[] = array(
				$row['username'],
				$row['name'],
				'['.$row['galaxy'].':'.$row['system'].':'.$row['planet'].']',
				$row['galaxy'],
				$row['system']
			);
		}
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
		
		foreach($this->users as $user)
		{
			echo htmlentities($user[0])."|".htmlentities($user[1])
				."|".$user[2]."|".$user[3]."|".$user[4]."\n";
		}
	}
}
?>