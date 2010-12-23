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

require_once(LW_DIR.'lib/data/message/Message.class.php');

/**
 * Editor for creating, editing and deleting messages.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.message
 */
class MessageEditor extends Message {
	/**
	 * Creates a new message
	 * 
	 * @param	int		recipent id
	 * @param	string	subject
	 * @param 	string	text
	 * @param	int		sender id
	 * @param	string	sender name
	 * @param	int		message type
	 */
	public static function create($recipentID, $subject, $text, $senderID = null, $senderName = null, $messageType = 1) {
		if($senderID === null) $senderID = WCF::getUser()->userID;
		if($senderName === null) {
			require_once(LW_DIR.'lib/data/user/LWUser.class.php');
			$sender = new LWUser($senderID);
			$senderName = $sender->getLinkedUsername();
		}
		if($senderID == 0)
			$senderID = "NULL";
		
		// insert
		$sql = "INSERT INTO ugml_messages
				(message_owner, message_sender, message_time,
				 message_type, message_from, message_subject,
				 message_text)
				VALUES
				(".$recipentID.", ".$senderID.", ".time().",
				 ".$messageType.", '".escapeString($senderName)."', '".escapeString($subject)."',
				 '".escapeString($text)."')";
		WCF::getDB()->sendQuery($sql);
		
		// update user
		$sql = "UPDATE ugml_users
				SET new_message = new_message + 1
				WHERE id = ".$recipentID;
		WCF::getDB()->sendQuery($sql);
		
		Session::resetSessions($recipentID);
	}
}
?>