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

require_once(LW_DIR.'lib/data/AbstractDecorator.class.php');
require_once(LW_DIR.'lib/data/message/NMessage.class.php');
require_once(LW_DIR.'lib/data/user/UserSettings.class.php');

/**
 * Holds all functions to view a message.
 * 
 * @author		Biggerskimo
 * @copyright	2010 - 2011 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class NMessageEditor extends DatabaseObject
{
	private $message = null;
	
	/**
	 * Creates a new MessageEditor object.
	 * @param	Message		$messageID
	 */
	public function __construct(NMessage $message)
	{
		$this->message = $message;
	}
	
	/**
	 * Creates a new message.
	 * 
	 * @param	int		$recipentID (userID)
	 * @param	array	$sender (array($senderGroup, $senderID))
	 * @param	string	$subject
	 * @param	string	$text
	 * @param	int		$preset (1 = espionage, 2 = combat, 3 = alliance, 4 = direct, 5 = other)
	 * @param	int		$time
	 */
	public static function create($recipentID, $sender, $subject, $text, $preset = 5, $time = null)
	{
		if($time === null)
			$time = time();
			
		$sql = "INSERT INTO ugml_message
				(`time`, senderGroup, senderID,
				 recipentID, folderID,
				 subject, text)
				VALUES
				(".$time.", ".$sender[0].", ".$sender[1].",
				 ".$recipentID.", MESSAGE_FOLDER_PRESET(".$recipentID.", ".$preset."),
				 '".escapeString($subject)."', '".escapeString($text)."')";
		WCF::getDB()->sendQuery($sql);
		
		$messageID = WCF::getDB()->getInsertID();
		
		return new NMessage($messageID);
	}
	
	/**
	 * Deletes this message.
	 */
	public function delete()
	{
		WCF::getDB()->startTransaction();
		
		$sql = "INSERT INTO ugml_archive_message
				SELECT *
				FROM ugml_message
				WHERE messageID = ".$this->getObject()->messageID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM ugml_message
				WHERE messageID = ".$this->getObject()->messageID;
		WCF::getDB()->sendQuery($sql);
		
		WCF::getDB()->commit();
	}
	
	/**
	 * Deletes some messages.
	 * 
	 * @param	int		userID
	 * @param	int		checked
	 */
	public function deleteAll($userID, $checked = null)
	{
		$sql = "DELETE FROM ugml_message
				WHERE recipentID = ".$userID;
		if($checked !== null)
			$sql .= " AND checked = ".$checked;
		WCF::getDB()->sendQuery($sql);
		
		if($checked === null || $checked)
			UserSettings::setSetting($userID, 'checkedMessages', 0);
		return;
	}
	
	/**
	 * Sets the 'viewed'-flag for some messages.
	 * 
	 * @param	str		messageIDs
	 * @param	int		viewed
	 */
	public static function view($messageIDs, $viewed = 1)
	{
		if(!is_array($messageIDs))
			$messageIDs = array($messageIDs);
		
		$sql = "UPDATE ugml_message
				SET viewed = 1
				WHERE messageID IN (".implode(',', $messageIDs).")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Sets the 'checked'-flag for the messages of a given user.
	 * 
	 * @param	int		userID
	 * @param	int		checked
	 * @param	array	folderIDs
	 */
	public function checkAll($userID, $checked = 1, $folderIDs = null)
	{
		$sql = "UPDATE ugml_message
				SET checked = ".$checked."
				WHERE recipentID = ".$userID;
		if($folderIDs !== null && count($folderIDs))
			$sql .= " AND folderID IN (".implode(',', $folderIDs).")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "SELECT COUNT(*) AS count
				FROM ugml_message
				WHERE checked = 1
					AND recipentID = ".$userID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		UserSettings::setSetting($userID, 'checkedMessages', intval($row['count']));
	}
	
	/**
	 * Sets the 'checked'-flag on this message.
	 */
	public function check()
	{
		$this->getObject()->checked = $this->getObject()->checked ? 0 : 1;
		$sql = "UPDATE ugml_message
				SET checked = ".$this->getObject()->checked." 
				WHERE messageID = ".$this->getObject()->messageID;
		WCF::getDB()->sendQuery($sql);
		
		WCF::getUser()->setSetting('checkedMessages',
			intval(WCF::getUser()->getSetting('checkedMessages'))
			+ ($this->getObject()->checked ? 1 : -1));
	}
	
	/**
	 * @see AbstractDecorator::getObject()
	 */
	protected function getObject() {
		return $this->message;
	}
}
?>