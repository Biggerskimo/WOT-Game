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
	private $senderID;
	private $messageID;
	private $name;
	
	/**
	 * @see MessageSender::setSenderID()
	 */
	public function setSenderID($senderID, $messageID, $extra)
	{
		$this->senderID = $senderID;
		$this->messageID = $messageID;
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
	 * @see MessageSender::getActions()
	 */
	public function getActions()
	{
		if($this->senderID == 3) // spionage
		{
			return array(
				array('wot.messages.message.attack',
					'index.php?action=AfterEspionage&amp;command=attack&amp;messageID='.$this->messageID),
				array('wot.messages.message.simulate',
					'index.php?action=AfterEspionage&amp;command=simulate&amp;messageID='.$this->messageID),
			);
		}
		return array();
	}
	
	/**
	 * @see MessageSender::escape()
	 */
	public function escape()
	{
		return false;
	}
}
?>