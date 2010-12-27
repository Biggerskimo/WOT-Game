<?php
require_once(LW_DIR.'lib/data/message/sender/MessageSender.class.php');
/**
 * Represents an user as a sender.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class UserMessageSender implements MessageSender
{
	private $userID;
	private $messageID;
	private $username;
	
	/**
	 * @see MessageSender::setSenderID()
	 */
	public function setSenderID($senderID, $messageID, $extra)
	{
		$this->userID = $senderID;
		$this->messageID = $messageID;
		$this->username = $extra;
	}
	
	/**
	 * @see MessageSender::getSenderName()
	 */
	public function getSenderName()
	{
		return $this->username;
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
		return array(
			array('wot.messages.message.answer',
				'index.php?form=MessageUser&amp;messageID='.$this->messageID),
			array('wot.messages.message.notify',
				'javascript:messages.notify('.$this->messageID.')'),
			array('wot.messages.message.blacklist',
				'javascript:messages.blacklist('.$this->userID.')')
		);
	}
}
?>