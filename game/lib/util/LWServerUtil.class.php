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

require_once(WBB_DIR.'../game/lib/util/LWUtil.class.php');
require_once(WCF_DIR.'lib/util/FileUtil.class.php');

/**
 * Contains to communicate between the servers
 */
class LWServerUtil {
	protected static $servers = array();
	
	/**
	 * Updates the wcf on an other server
	 */
	protected static function updateWCF($worldID) {
		$randomID = StringUtil::getRandomID();
		
		$server = self::$servers[$worldID];
		
		// export data as cvs
		$sql = "SELECT HIGH_PRIORITY *
				INTO OUTFILE '/home/lostwdbl/game/outfile/".$randomID."_wcf1_user.cvs'
	   			FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
	    		LINES TERMINATED BY '\n'
				FROM wcf1_user";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "SELECT HIGH_PRIORITY *
				INTO OUTFILE '/home/lostwdbl/game/outfile/".$randomID."_wcf1_user_option_value.cvs'
	   			FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
	    		LINES TERMINATED BY '\n'
				FROM wcf1_user_option_value";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "SELECT HIGH_PRIORITY *
				INTO OUTFILE '/home/lostwdbl/game/outfile/".$randomID."_wcf1_user_to_groups.cvs'
	   			FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
	    		LINES TERMINATED BY '\n'
				FROM wcf1_user_to_groups";
		WCF::getDB()->sendQuery($sql);
		
		// connect to the other db
		$tmpConnection = mysql_connect($server['mysqlHost'], $server['mysqlUser'], $server['mysqlPassword'], true);
		$sql = "SET charset latin1";
		mysql_query($sql, $tmpConnection);
		mysql_select_db($server['mysqlUser'], $tmpConnection);
		
		// load data into the db
		$sql = "LOAD DATA LOCAL INFILE '/home/lostwdbl/game/outfile/".$randomID."_wcf1_user.cvs'
				REPLACE INTO TABLE wcf1_user
				FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
	    		LINES TERMINATED BY '\n'";
	    mysql_query($sql, $tmpConnection);
		
	    $sql = "LOAD DATA LOCAL INFILE '/home/lostwdbl/game/outfile/".$randomID."_wcf1_user_option_value.cvs'
				REPLACE INTO TABLE wcf1_user_option_value
				FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
	    		LINES TERMINATED BY '\n'";
	    mysql_query($sql, $tmpConnection);
		
	    $sql = "LOAD DATA LOCAL INFILE '/home/lostwdbl/game/outfile/".$randomID."_wcf1_user_to_groups.cvs'
				REPLACE INTO TABLE wcf1_user_to_groups
				FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
	    		LINES TERMINATED BY '\n'";
	    mysql_query($sql, $tmpConnection);
		
		//if(WCF::getUser()->userID == 143) echo $sql.';';
		
		// clean up
		/*unlink('/home/lostwdbl/game/outfile/'.$randomID.'_wcf1_user.cvs');
		unlink('/home/lostwdbl/game/outfile/'.$randomID.'_wcf1_user_option_value.cvs');
		unlink('/home/lostwdbl/game/outfile/'.$randomID.'_wcf1_user_to_groups.cvs');*/
	}

	/**
	 * Sends a request.
	 */
	protected static function sendRequest($worldID, $args) {
		self::getAccessableServers();
		
		$server = self::$servers[$worldID];
		
		// build query string
		$queryStr = $server['url'].'board_reg.php';
		
		foreach($args as $argName => $argValue) {
			if(isset($arg)) $queryStr .= '&';
			else {
				$queryStr .= '?';
				$arg = true;
			}
			$queryStr .= rawurlencode($argName).'='.rawurlencode($argValue);
		}
		
		// send request
		$fileName = FileUtil::downloadFileFromHttp($queryStr, 'lost-worlds');
		//if($args['action'] == 2) include($fileName);
		
		// update wcf
		self::updateWCF($worldID);
	}
	
	/**
	 * Registeres a user to the given server
	 */
	public static function register($worldID, $user) {
		$userID = $user->userID;
		$userName = $user->username;
		// password must be set by the user again later
		$email = $user->email;
		
		$sql = "INSERT INTO wbb".WBB_N."_world_to_users
				(worldID, userID, deletionTime)
				VALUES
				(".$worldID.", ".$userID.", 0)";
		WCF::getDB()->sendQuery($sql);
		
		$hash = LWUtil::createHash($userID, $userName, $email);
		
		$args = array('action' => 1,
				'userID' => $userID,
				'userName' => $userName,
				'email' => $email,
				'hash' => $hash);
				
		self::sendRequest($worldID, $args);
	}
	
	/**
	 * Deletes a user
	 */
	public static function delete($worldID, $userIDsStr) {
		$hash = LWUtil::createHash($userIDsStr);
		
		$args = array('action' => 3,
				'userIDsStr' => $userIDsStr,
				'hash' => $hash);
				
		self::sendRequest($worldID, $args);
	}
	
	/**
	 * Updates the information on all servers
	 */
	public static function update($userID, $userName, $email) {		
		$hash = LWUtil::createHash($userID, $userName, $email);
		
		$args = array('action' => 2,
				'userID' => $userID,
				'userName' => $userName,
				'email' => $email,
				'hash' => $hash);
		
		self::getAccessableServers();
		foreach(self::$servers as $worldID => $server) {
			if($server['registered']) self::sendRequest($worldID, $args);
		}
	}
	
	/**
	 * Returns the accessable servers
	 *
	 * @return	array	servers
	 */
	public static function getAccessableServers() {
		if(!count(self::$servers)) {
			$sql = "SELECT DISTINCT `mainw`.*,
						(SELECT COUNT(*)
						FROM `wbb".WBB_N."_world_to_users`
							AS `subwtu1`
						WHERE `subwtu1`.`worldID` = `mainw`.`worldID`)
							AS `userCount`,
						(SELECT COUNT(*)
						FROM `wbb".WBB_N."_world_to_users`
							AS `subwtu2`
						WHERE `subwtu2`.`worldID` = `mainw`.`worldID`
							AND `subwtu2`.`userID` = ".WCF::getUser()->userID.")
							AS `registered`,
						`mainwtu`.`deletionTime`
					FROM `wbb".WBB_N."_worlds`
						AS `mainw`
					LEFT JOIN `wbb".WBB_N."_world_to_groups`
						AS `mainwtg`
						ON `mainwtg`.`worldID` = `mainw`.`worldID`
					LEFT JOIN `wbb".WBB_N."_world_to_users`
						AS `mainwtu`
						ON `mainwtu`.`userID` = ".WCF::getUser()->userID."
							AND `mainwtu`.`worldID` = `mainw`.`worldID`
					WHERE `mainwtg`.`groupID` IN(SELECT `subutg`.`groupID`
							FROM `wcf".WCF_N."_user_to_groups`
							AS `subutg`
							WHERE `userID` = ".WCF::getUser()->userID."
							UNION SELECT 1)
						OR `mainwtu`.`userID` IS NOT NULL";
			$result = WCF::getDB()->sendQuery($sql);
			
			while($row = WCF::getDB()->fetchArray($result)) {
				self::$servers[$row['worldID']] = $row;
			}
		}	
		
		return self::$servers;
	}
}
?>