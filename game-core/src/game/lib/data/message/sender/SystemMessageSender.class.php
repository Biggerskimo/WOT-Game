<?php
require_once(LW_DIR.'lib/data/message/sender/MessageSender.class.php');
/**
 * Represents a system-sender.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class SystemMessageSender implements MessageSender
{
	private $name;
	
	/**
	 * @see MessageSender::setSenderID()
	 */
	public function setSenderID($senderID, $messageID, $extra)
	{
		// ignore senderID
		// ignore messageID
		$this->name = $extra;
	}
	
	/**
	 * @see MessageSender::getSenderName()
	 */
	public function getSenderName()
	{
		return $this->name;
	}
	
	/**
	 * @see MessageSender::getLink()
	 */
	public function getLink()
	{
		return null;
	}
	
	/**
	 * @see MessageSender::getActions()
	 */
	public function getActions()
	{
		return array();
	}
}
?>