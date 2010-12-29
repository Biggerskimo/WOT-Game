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

/**
 * Holds all functions to view a message.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
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
	 * @param	int		$time
	 */
	public static function create($recipentID, $sender, $subject, $text, $time = null)
	{
		if($time === null)
			$time = time();
			
		$sql = "INSERT INTO ugml_message
				(`time`, senderGroup, senderID,
				 recipentID, subject, text)
				VALUES
				(".$time.", ".$sender[0].", ".$sender[1].",
				 ".$recipentID.", '".escapeString($subject)."', '".escapeString($text)."')";
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
	 * Sets the 'remembered'-flag on this message.
	 */
	public function remember()
	{
		$this->getObject()->remembered = $this->getObject()->remembered ? 0 : 1;
		$sql = "UPDATE ugml_message
				SET remembered = ".$this->getObject()->remembered." 
				WHERE messageID = ".$this->getObject()->messageID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see AbstractDecorator::getObject()
	 */
	protected function getObject() {
		return $this->message;
	}
}
?>