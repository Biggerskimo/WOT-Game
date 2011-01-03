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
require_once(LW_DIR.'lib/data/message/sender/MessageSender.class.php');
require_once(LW_DIR.'lib/data/message/NMessageEditor.class.php');

/**
 * Represents a message folder.
 * 
 * @author		Biggerskimo
 * @copyright	2011 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class MessageFolder extends DatabaseObject
{
	/**
	 * Creates a new MessageFolder object.
	 * 
	 * @param	int		$folderID
	 * @param	array	$row
	 */
	public function __construct($folderID, $row = null)
	{
		if($row === null)
		{
			$sql = "SELECT ugml_message_folder.*,
						COUNT(all.messageID) AS messageCount,
						COUNT(unviewed.messageID) AS unviewedCount
					FROM ugml_message_folder
					LEFT JOIN ugml_message
						AS `all`
						ON ugml_message_folder.folderID = `all`.folderID
					LEFT JOIN ugml_message
						AS unviewed
						ON ugml_message_folder.folderID = unviewed.folderID
							AND unviewed.viewed = 0
					GROUP BY ugml_message_folder.folderID";
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * Searches for all message folders owned by a user.
	 * 
	 * @param	int		$userid
	 */
	public static function getByUserID($userID)
	{
		$sql = "SELECT ugml_message_folder.*,
					COUNT(all.messageID) AS messageCount,
					COUNT(unviewed.messageID) AS unviewedCount
				FROM ugml_message_folder
				LEFT JOIN ugml_message
					AS `all`
					ON ugml_message_folder.folderID = `all`.folderID
				LEFT JOIN ugml_message
					AS unviewed
					ON ugml_message_folder.folderID = unviewed.folderID
						AND unviewed.viewed = 0
				WHERE ugml_message_folder.userID = ".$userID."
				GROUP BY ugml_message_folder.folderID";
		$result = WCF::getDB()->sendQuery($sql);
		
		$folders = array();
		while($row = WCF::getDB()->fetchArray($result))
		{
			$folders[$row['folderID']] = new self(null, $row);
		}
		return $folders;
	}
}
?>