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
 * Holds all functions to view a message.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class NMessage extends DatabaseObject
{
	private $sender = null;
	
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
		
		$this->initSender();
	}
	
	/**
	 * Searches for all messages in a users' inbox.
	 * 
	 * @param	int		$userid
	 * @param	int 	$checked
	 * @param	array	$folders
	 * @param	int		$limit
	 * @param	int		$offset
	 * @return	array	$messages
	 */
	public static function getByUserID($userID, $checked = null, $folders = null, $onlyNew = false, $limit = null, $offset = null)
	{
		if($folders !== null && !count($folders))
			return array();
		
		$sql = "SELECT *
				FROM ugml_v_message
				WHERE recipentID = ".$userID;
		if($checked !== null)
			$sql .= " AND checked = ".$checked;
		if($folders !== null)
			$sql .= " AND folderID IN(".implode(',', $folders).")";
		if($onlyNew)
			$sql .= " AND viewed = 0";
		if($limit !== null)
			$sql .= " LIMIT ".$limit;
		if($offset !== null)
			$sql .= " OFFSET ".$offset;
		
		$result = WCF::getDB()->sendQuery($sql);
		
		$messages = array();
		while($row = WCF::getDB()->fetchArray($result))
		{
			$messages[$row['messageID']] = new self(null, $row);
		}
		return $messages;
	}
	
	/**
	 * Returns the editor for this message.
	 * 
	 * @return NMessageEditor
	 */
	public function getEditor()
	{
		return new NMessageEditor($this);
	}
	
	/**
	 * Searches for the correct sender class and loads an instance of it.
	 */
	protected function initSender()
	{
		// TODO: create factory with cache; remember ugml_message_sender!
		switch($this->senderGroup)
		{
			case 1:
				require_once(LW_DIR.'lib/data/message/sender/UserMessageSender.class.php');
				$this->sender = new UserMessageSender();
				break;
			case 2:
				require_once(LW_DIR.'lib/data/message/sender/AllianceMessageSender.class.php');
				$this->sender = new AllianceMessageSender();
				break;
			case 3:
			default: // throw exception?
				require_once(LW_DIR.'lib/data/message/sender/SystemMessageSender.class.php');
				$this->sender = new SystemMessageSender();
		}
		$this->sender->setSenderID($this->senderID, $this->messageID, $this->extra);
	}
	
	/**
	 * Returns the MessageSender-object for this message.
	 * 
	 * @return		MessageSender
	 */
	public function getSender()
	{
		return $this->sender;
	}
	
	/**
	 * Sends a notification to the game operators.
	 */
	public function notify()
	{
		$sql = "INSERT IGNORE INTO ugml_message_notification
				(messageID, notificationTime)
				VALUES
				(".$this->messageID.", ".time().")";
		WCF::getDB()->sendQuery($sql);
		
		$subject = WCF::getLanguage()->get('wot.messages.notification.subject');
		$text = WCF::getLanguage()->get('wot.messages.notification.introduction');
		
		$text .= "\n<br />\n";
		$text .= "\n<br />\n";
		$text .= "messageID: ".$this->messageID."\n<br />\n";
		$text .= "subject: \"".$this->subject."\"\n<br />\n";
		$text .= "sender/userID: ".$this->senderID."\n<br />\n";
		$text .= "sender/name: \"".$this->getSender()->getSenderName()."\"\n<br />\n";
		$text .= "time: ".date('r', $this->time)." (".$this->time.")\n<br />\n";
		$text .= "text: \n<br />\n";
		$text .= "\"".$this->text."\"";
		
		$sql = "SELECT id
				FROM ugml_users
				WHERE notifiee = 1";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result))
		{
			NMessageEditor::create(
				$row['id'], array(1, $this->recipentID), $subject, $text, 4
			);
		}
	}
}
?>