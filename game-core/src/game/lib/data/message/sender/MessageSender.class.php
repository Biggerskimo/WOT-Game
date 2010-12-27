<?php
/**
 * All classes that represent a message sender should implement this interface.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
interface MessageSender
{
	/**
	 * Saves the sender id. It will be used later (probably).
	 * 
	 * @param	int		senderID
	 * @param	int 	messageID
	 * @param	String	extra text
	 */
	public function setSenderID($senderID, $messageID, $extra);
	
	/**
	 * Returns the text to be shown for this sender.
	 * 
	 * @return	String	sender name
	 */
	public function getSenderName();
	
	/**
	 * Returns a url as a link or null/empty string when no link available.
	 * 
	 * @return	String	sender link
	 */
	public function getLink();
	
	/**
	 * Returns an array with extra links/actions. Has to be like this:
	 * array(
	 * [0] => array(<name>, <url>),
	 * [1] => array(<name>, <url>),
	 * ...
	 * )
	 */
	public function getActions();
	
	/**
	 * Returns whether the message has to be escaped or not.
	 * 
	 * @return bool
	 */
	public function escape();
}
?>