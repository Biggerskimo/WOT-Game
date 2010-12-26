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
 * Holds all functions to view a message.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class NMessage extends DatabaseObject
{
	/**
	 * Creates a new Message object.
	 * @param	int		$messageID
	 * @param	array	$row
	 */
	public function __construct($messageID, $row = null)
	{
		if($row === null)
		{
			$sql = "SELECT *
					FROM ugml_v_message
					WHERE messageID = ".$messageID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * Searches for all messages in a users' inbox.
	 * @param	int		$userid
	 * @return	array	$messages
	 */
	public static function getByUserID($userID)
	{
		$sql = "SELECT *
				FROM ugml_v_message
				WHERE recipentID = ".$userID;
		$result = WCF::getDB()->sendQuery($sql);
		
		$messages = array();
		while($row = WCF::getDB()->fetchArray($result))
		{
			$messages[] = new self(null, $row);
		}
		return $messages;
	}
}
?>