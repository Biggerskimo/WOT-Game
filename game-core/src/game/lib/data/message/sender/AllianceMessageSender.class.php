<?php
require_once(LW_DIR.'lib/data/message/sender/MessageSender.class.php');
require_once(LW_DIR.'lib/data/message/sender/UserMessageSender.class.php');
/**
 * Represents an alliance as a sender.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 * @package	game.wot.message
 */
class AllianceMessageSender extends UserMessageSender implements MessageSender
{
	private $allianceID;
	private $messageID;
	private $allianceTag;
	private $username;
	
	/**
	 * @see MessageSender::setSenderID()
	 */
	public function setSenderID($senderID, $messageID, $extra)
	{
		$this->allianceID = $senderID;
		$this->messageID = $messageID;
		list($this->allianceTag, $this->userID, $this->username) = explode(',', $extra, 3);
	}
	
	/**
	 * @see MessageSender::getSenderName()
	 */
	public function getSenderName()
	{
		if(empty($this->username))
		{
			return '<a href="index.php?page=Alliance&amp;allianceID='.$this->allianceID.'">['.
				htmlspecialchars($this->allianceTag).']</a>';
		}
		
		return htmlspecialchars($this->username).
			' aus <a href="index.php?page=Alliance&amp;allianceID='.$this->allianceID.'">['.
			htmlspecialchars($this->allianceTag).']</a> (Rundmail)';
	}
	
	/**
	 * @see MessageSender::getActions()
	 */
	public function getActions()
	{
		if(empty($this->username))
		{
			return array();
		}
		return array(
			array('wot.messages.message.notify',
				'javascript:messages.notify('.$this->messageID.')'),
			array('wot.messages.message.answerCircular',
				'index.php?form=AllianceCircularCreate'),
			array('wot.messages.message.answerDirect',
				'index.php?form=MessageUser&amp;messageID='.$this->messageID)
		);
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