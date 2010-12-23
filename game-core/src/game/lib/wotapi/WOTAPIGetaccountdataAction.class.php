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
 * Sends the essential account information.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIGetaccountdataAction extends AbstractWOTAPIAction {
	public $accounts = array();
	
	const STAT_TYPE_ID = 1;
	
	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		$sql = "SELECT id, urlaubs_modus, onlinetime, ugml_stat_entry.rank, ugml_stat_entry.points,
					(fleet1.fleetID IS NULL AND fleet2.fleetID IS NULL) AS deletable
				FROM ugml_users
				LEFT JOIN ugml_stat_entry
					ON ugml_stat_entry.statTypeID = ".self::STAT_TYPE_ID."
						AND ugml_stat_entry.relationalID = ugml_users.id
				LEFT JOIN ugml_fleet AS fleet1
					ON fleet1.ownerID = ugml_users.id
				LEFT JOIN ugml_fleet AS fleet2
					ON fleet2.ofiaraID = ugml_users.id
				GROUP BY ugml_users.id";
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
			$this->accounts[$row['id']] = array(
				'userID' => $row['id'],
				'umodeSetting' => $row['urlaubs_modus'],
				'onlineTime' => $row['onlinetime'],
				'rank' => $row['rank'],
				'points' => $row['points'],
				'deletable' => $row['deletable']);
		}
		
		parent::execute();
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();
		
		$data = array();
		$data['accounts'] = gzcompress(serialize($this->accounts));
		
		
		$this->wotAPIServerClient->send('appending account data', 100, $data);
		
		echo '~~~',strlen($data['accounts']);
	}
}
?>