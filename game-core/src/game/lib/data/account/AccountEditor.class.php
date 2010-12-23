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

require_once(LW_DIR.'lib/data/account/Account.class.php');

/**
 * This class holds functions to edit game accounts.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.user
 */
class AccountEditor extends Account {
	const TMP_USERID = 100;
	
	public static $switchTables = array(
			array('ugml_alliance', 'ally_owner'),
			array('ugml_buddy', 'sender'),
			array('ugml_buddy', 'owner'),
			array('ugml_fleets', 'fleet_owner'),
			array('ugml_fleets', 'fleet_ofiara'),
			array('ugml_fleet_queue', 'userID'),
			array('ugml_galactic_jump_queue', 'userID'),
			array('ugml_messages', 'message_owner'),
			array('ugml_messages', 'message_sender'),
			array('ugml_naval_formation_to_users', 'userID'),
			array('ugml_notes', 'owner'),
			array('ugml_planets', 'id_owner'),
			array('ugml_stat', 'userID')
			// do NOT delete account row
		);
	public static $switchCols = array(
			array(array(
					'ugml_users',
					'id'),
				array(
					'id_planet',
					'current_planet',
					'galaxy',
					'system',
					'planet'
				)
			)
		);
	
	public static $deleteTables = array(
			array('ugml_alliance', 'ally_owner'),
			array('ugml_buddy', 'sender'),
			array('ugml_buddy', 'owner'),
			array('ugml_fleet', 'ownerID'),
			array('ugml_fleet', 'ofiaraID'),
			//array('ugml_fleets', 'fleet_owner'),
			//array('ugml_fleets', 'fleet_ofiara'),
			array('ugml_fleet_queue', 'userID'),
			array('ugml_galactic_jump_queue', 'userID'),
			array('ugml_messages', 'message_owner'),
			array('ugml_messages', 'message_sender'),
			array('ugml_naval_formation_to_users', 'userID'),
			array('ugml_notes', 'owner'),
			array('ugml_planets', 'id_owner'),
			array('ugml_pbu', 'userID'),
			//array('ugml_stat', 'userID'),
			//array('ugml_stat_entry', 'userID'),
			// also delete account row
			array('ugml_users', 'id')
		);
		
	/**
	 * Creates a new game account.
	 * 
	 * @param	int		user id
	 * @param	string	user name
	 * @param	string	email
	 */
	public static function create($userID, $username, $email) {
		$sql = "INSERT INTO ugml_users
				(id, username, email,
				 email_2, register_time, lastLoginTime,
				 dilizium, diliziumFeatures)
				VALUES
				(".$userID.", '".escapeString($username)."', '".escapeString($email)."',
				 '".$email."', ".time().", ".time().",
				 500, 'a:0:{}')";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "UPDATE ugml_config
				SET config_value = (SELECT COUNT(*)
									FROM ugml_users)
				WHERE config_name = 'users_amount'";
		WCF::getDB()->sendQuery($sql);
		
		$accountEditor = new AccountEditor($userID);
		
		// TODO: event listener
		require_once(LW_DIR.'lib/data/news/News.class.php');
		require_once(LW_DIR.'lib/data/user/UserSettings.class.php');
		WCF::getCache()->addResource('news-'.PACKAGE_ID, WCF_DIR.'cache/cache.news-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderNews.class.php');
		$news = WCF::getCache()->get('news-'.PACKAGE_ID);
		foreach($news as $key => $newsItem)
			if($key != "hash")
				UserSettings::setSetting($userID, $newsItem->getIdentifier(), TIME_NOW);
		
		return $accountEditor;
	}
	
	/**
	 * Updates the data of the account.
	 * 
	 * @param	username
	 * @param	email
	 */
	public function update($username, $email) {
		if(!empty($username)) {
			$this->username = $username;
		}
		
		if(!empty($email)) {
			$this->email = $this->email_2 = $email;
		}
		
		$sql = "UPDATE ugml_users
				SET username = '".escapeString($this->username)."',
					email = '".escapeString($this->email)."',
					email_2 = '".escapeString($this->email)."'
				WHERE id = ".$this->userID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes a account in all game-related tables.
	 */
	public function delete() {
		if($this->userID) {
			foreach(self::$deleteTables as $tableArray) {
				$table = $tableArray[0];
				$col = $tableArray[1];
				
				$sql = "DELETE FROM ".$table."
						WHERE ".$col." = ".$this->userID;			
				WCF::getDB()->sendQuery($sql);
			}
		}
	}
	
	/**
	 * Switches this account with an other.
	 * 
	 * @param	int		other user id
	 */
	public function doSwitch($userID2) {
		global $resource;
		
		$str = '';
		// switch tables	
		foreach(self::$switchTables as $tableArray) {
			$table = $tableArray[0];
			$col = $tableArray[1];
			
			// swap first user to tmp user
			$sql = "UPDATE ".$table."
					SET ".$col." = ".self::TMP_USERID."
					WHERE ".$col." = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
			
			// swap second user to first user
			$sql = "UPDATE ".$table."
					SET ".$col." = ".$this->userID."
					WHERE ".$col." = ".$userID2;
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
			
			// swap tmp user (first before first swap) to second user
			$sql = "UPDATE ".$table."
					SET ".$col." = ".$userID2."
					WHERE ".$col." = ".self::TMP_USERID;
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
		}
		
		// switch cols
		// create tmp tables
		$deleteTables = array();
		foreach(self::$switchCols as $tableArray) {
			$tableName = $tableArray[0][0];
			$tmpTableName = $tableName."_tmp";
			$deleteTables[] = $tmpTableName;
			
			$sql = "SHOW CREATE TABLE ".$tableName;
			$row = WCF::getDB()->getFirstRow($sql);
			$str .= "\n".$sql;
			
			// replace ("CREATE TABLE `ugml_bla`" -> "CREATE TEMPORARY TABLE `ugml_bla_tmp`")
			$createTableSyntax = $row['Create Table'];
			$createTableSyntax = str_replace("CREATE TABLE", "CREATE TEMPORARY TABLE", $createTableSyntax);
			$sql = str_replace($tableName, $tmpTableName, $createTableSyntax);
			
			// remove foreign keys (sql error 1005: can't create table, error no 150)
			$regex = "/,\s*(?:CONSTRAINT.+?)?FOREIGN KEY\s*\(.+?\)\s*REFERENCES[^\(]+\(.+?\)[^\),]*(?=[,\)])/";
			$sql = preg_replace($regex, "$1", $sql);
			
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
		}
		
		// switch
		foreach(self::$switchCols as $tableArray) {
			$tableName = $tableArray[0][0];
			$accountIdColName = $tableArray[0][1];
			$tmpTableName = $tableName."_tmp";			
			$colArray = $tableArray[1];
			$colString = "";
			
			// build col string (t2 overwrites t1)
			foreach($colArray as $colName) {
				if(!empty($colString)) $colString .= ",";
				
				$colString .= " t1.".$colName." = t2.".$colName;
			}
			
			for($specID = 100; $specID < 200; ++$specID) {
				if(!isset($resource[$specID])) {
					continue;
				}
				if(!empty($colString)) {
					$colString .= ",";
				}
				
				$colString .= " t1.".$resource[$specID]." = t2.".$resource[$specID];
			}
			
			// copy first user to tmp user
			$sql = "INSERT INTO ".$tmpTableName."
					SELECT * FROM ".$tableName."
					WHERE ".$accountIdColName." = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
			
			// swap values of second user to first user
			$sql = "UPDATE ".$tableName." AS t1,
						".$tableName." AS t2
					SET ".$colString."
					WHERE t1.".$accountIdColName." = ".$this->userID."
						AND t2.".$accountIdColName." = ".$userID2;
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
			
			// swap values of tmp (first user before copy) user to second user
			$sql = "UPDATE ".$tableName." AS t1,
						".$tmpTableName." AS t2
					SET ".$colString."
					WHERE t1.".$accountIdColName." = ".$userID2."
						AND t2.".$accountIdColName." = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
		}
		
		// delete tmp tables
		foreach($deleteTables as $tableName) {
			$sql = "DROP TEMPORARY TABLE IF EXISTS ".$tableName;
			
			WCF::getDB()->sendQuery($sql);
			$str .= "\n".$sql;
		}
	}
}
?>