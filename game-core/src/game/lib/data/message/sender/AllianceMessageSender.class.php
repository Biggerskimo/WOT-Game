<?php
require_once(LW_DIR.'lib/data/message/sender/MessageSender.class.php');
/**
 * Represents an alliance as a sender.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class AllianceMessageSender implements MessageSender
{
	private $allianceID;
	private $messageID;
	private $allianceTag;
	private $userID;
	
	/**
	 * @see MessageSender::setSenderID()
	 */
	public function setSenderID($senderID, $messageID, $extra)
	{
		$this->allianceID = $senderID;
		$this->messageID = $messageID;
		list($this->allianceTag, $this->userID) = explode(',', $extra);
	}
	
	/**
	 * @see MessageSender::getSenderName()
	 */
	public function getSenderName()
	{
		return '['.$this->allianceTag.']';
	}
	
	/**
	 * @see MessageSender::getLink()
	 */
	public function getLink()
	{
		return 'index.php?page=Alliance';
	}
	
	/**
	 * @see MessageSender::getActions()
	 */
	public function getActions()
	{
		return array(
			array('wot.messages.message.answerCircular',
				'index.php?form=AllianceCircularCreate'),
			array('wot.messages.message.answerDirect',
				'index.php?form=MessageUser&amp;messageID='.$this->messageID)
		);
	}
}
?>