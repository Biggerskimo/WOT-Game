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
 * Updates the user tables of the wcf.
 * 
 * @author		Biggerskimo
 * @copyright	2007-2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.control
 */
class WOTAPIUpdatewcfAction extends AbstractWOTAPIAction {
	public static $tables = array('user', 'user_option_value', 'user_to_groups');
	
	public $user = array();
	public $user_option_value = array();
	public $user_to_groups = array();
	
	public $userIDsStr = null;
	
	public $delete = false;
	
	public $userCount = 0;
	public $userValid = '';
	
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->user = unserialize(gzuncompress(StringUtil::trim(WOTAPIUtil::unescape($this->data['user']))));
		$this->user_option_value = unserialize(gzuncompress(StringUtil::trim(WOTAPIUtil::unescape($this->data['user_option_value']))));
		$this->user_to_groups = unserialize(gzuncompress(StringUtil::trim(WOTAPIUtil::unescape($this->data['user_to_groups']))));
		
		if(isset($this->data['delete'])) {
			$this->delete = (bool)intval(StringUtil::trim($this->data['delete']));
		}
		
		if(isset($this->data['useridsstr'])) {
			$this->userIDsStr = StringUtil::trim(WOTAPIUtil::unescape($this->data['useridsstr']));
		}
		
		$this->userCount = intval($this->data['userscount']);
		$this->userValid = StringUtil::trim($this->data['usersvalid']);
	}
	
	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		if(count($this->user) != $this->userCount/* || md5($this->user.$this->userCount) != $this->userValid*/) {
			$this->wotAPIServerClient->send('user data validation failed '.(count($this->user) != $this->userCount).'('.$this->userCount.'-'.count($this->user).'-'.(md5($this->user.$this->userCount) != $this->userValid), 301);
			return;
		}
		
		WCF::getDB()->sendQuery("START TRANSACTION");
		
		foreach(self::$tables as $table) {
			// get own table columns
			$sql = "SHOW COLUMNS FROM `wcf".WCF_N."_".$table."`";
			$result = WCF::getDB()->sendQuery($sql);
			$tableDef = array();
			
			while($row = WCF::getDB()->fetchArray($result)) {
				$tableDef[$row['Field']] = true;
			}
			
			// build and execute sql
			$sqlInserts = "";
			$sqlInserts2 = array();
			$rowNameString = "";
			$i = 0;
			foreach($this->$table as $row) {
				foreach($row as $key => $value) {
					// ignore fields that we have not
					if(!isset($tableDef[$key])) {
						unset($row[$key]);
						continue;
					}
					
					// escape columns if needed
					if(!is_numeric($value) || $value == '') {
						$row[$key] = "'".escapeString($value)."'";
					}
					
					// build columns string
					if(empty($sqlInserts) && !count($sqlInserts2)) {
						$rowNameString .= ",`".$key."`";
					}
				}
				
				$sqlInserts .= ",(".implode(",", $row).")";
				$i++;
				
				if($i > 500)
				{
					$sqlInserts2[] = $sqlInserts;
					$sqlInserts = "";
					$i = 0;
				}
			}
			if($i != 0)
				$sqlInserts2[] = $sqlInserts;
			
			// enough preparing for now, go!
			if(count($sqlInserts2)) {
				if($this->delete) {
					$sqlCondition = "";
					if($this->userIDsStr !== null)
						$sqlCondition = " WHERE userID IN (".$this->userIDsStr.")";
					$sql = "DELETE FROM wcf".WCF_N."_".$table.$sqlCondition;
					WCF::getDB()->sendQuery($sql);
					
					$sqlC = "INSERT";
				}
				else {
					$sqlC = "REPLACE";
				}
				foreach($sqlInserts2 as $sqlInserts)
				{
					$sql = $sqlC." INTO `wcf".WCF_N."_".$table."`
							(".substr($rowNameString, 1).")
							VALUES ".substr($sqlInserts, 1);
					echo $sql;
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		WCF::getDB()->sendQuery("COMMIT");
		
		parent::execute();
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();		
		
		$this->wotAPIServerClient->send('wcf user tables successfully updated!,', 100);
	}
}
?>