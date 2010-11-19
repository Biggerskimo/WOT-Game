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

require_once(LW_DIR.'lib/wotapi/AbstractWOTAPIAction.class.php');

/**
 * Collects the galaxy data.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIGetgalaxydataAction extends AbstractWOTAPIAction {
	public $galaxy = array();
	
	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		$sql = "SELECT galaxy,
					system,
					planet,
					id_owner,
					id
				FROM ugml_planets
				WHERE planetKind = 1
				ORDER BY galaxy,
					system,
					planet";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			if(!isset($this->galaxy[$row['galaxy']])) {
				$this->galaxy[$row['galaxy']] = array();
			}
			
			if(!isset($this->galaxy[$row['galaxy']][$row['system']])) {
				$this->galaxy[$row['galaxy']][$row['system']] = array();
			}
			
			if(!isset($this->galaxy[$row['galaxy']][$row['system']][$row['planet']])) {
				$array = array('userID' => $row['id_owner'], 'planetID' => $row['id']);
				$this->galaxy[$row['galaxy']][$row['system']][$row['planet']] = $array;
			}
		}
		
		parent::execute();
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();
		
		$data = array();
		$data['galaxy'] = gzcompress(serialize($this->galaxy));
		
		
		$this->wotAPIServerClient->send('appending galaxy data', 100, $data);
		
		echo '~~',strlen($data['galaxy']);
	}
}
?>