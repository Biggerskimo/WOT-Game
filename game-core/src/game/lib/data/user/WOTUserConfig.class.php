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

require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * With this class user configurations/options can be saved and accessed.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class WOTUserConfig extends DatabaseObject {
	protected static $instances = array();
	
	protected $userID = 0;
	protected $changes = array();
	
	/**
	 * Returns the active instance.
	 * 
	 * @param	int		userID
	 * @return	WOTUserConfig
	 */
	public static function getInstance($userID) {
		if(!isset(self::$instances[$userID])) {
			self::$instances[$userID] = new WOTUserConfig($userID);
		}
		return self::$instances[$userID];
	}
	
	/**
	 * Creates a new WOTUserConfig object.
	 * 
	 * @param	int		userID
	 */
	public function __construct($userID) {
		$this->userID = $userID;
		
		$this->loadData();
	}
	
	/**
	 * Saves the changes.
	 */
	public function __destruct() {
		$this->saveChanges();
	}
	
	/**
	 * Saves that this date has been changed.
	 * 
	 * @param	mixed	var
	 * @param	mixed	val
	 */
	public function __set($var, $val) {
		$this->changes[] = $var;
		
		$this->$var = $val;
	}
	
	/**
	 * Looks for stored user data. If not found, create new entry with default data.
	 */
	protected function loadData() {
		$sql = "SELECT *
				FROM ugml_user_config
				WHERE userID = ".$this->userID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		if(!$row) {
			$sqlInsert = "INSERT INTO ugml_user_config
					(userID)
					VALUES
					(".$this->userID.")";
			WCF::getDB()->sendQuery($sqlInsert);
			
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		$this->handleData($row);
	}
	
	/**
	 * @see DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		foreach($this->data as $key => $date) {
			if(strpos($date, '~wot:s:') === 0) {
				$this->data[$key] = unserialize(substr($date, 7));
			}
		}
	}
	
	/**
	 * Saves the changed data.
	 */
	public function saveChanges() {
		$setStrArr = array();
		foreach($this->changes as $change) {
			$val = $this->$change;
			
			if(!is_scalar($val)) {
				if(is_array($val)) {
					$val = "~wot:s:".serialize($val);
				}
				else {
					$val = "~wot:u:";
				}
			}
			$setStrArr[] = "`".$change."` = '".$val."'";
		}
		
		if(count($setStrArr)) {
			$sql = "UPDATE ugml_user_config
					SET ".implode(', ', $setStrArr)."
					WHERE userID = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>