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

require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(LW_DIR.'lib/data/message/sender/UserMessageSender.class.php');
require_once(LW_DIR.'lib/data/message/NMessage.class.php');
require_once(LW_DIR.'lib/data/message/NMessageEditor.class.php');

/**
 * Shows a form that allows a user to write a message to another user.
 * 
 * @author		Biggerskimo
 * @copyright	2010 - 2011 Lost Worlds <http://lost-worlds.net>
 */
class MessageUserForm extends AbstractForm
{
	public $templateName = 'messageUser';
	
	public $messageID = 0;
	
	public $username = '';
	public $subject = '';
	public $text = '';
	
	public $user = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters()
	{
		parent::readParameters();
		
		if(isset($_REQUEST['messageID'])) $this->messageID = intval($_REQUEST['messageID']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters()
	{
		parent::readFormParameters();
		
		if(isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if(isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if(isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData()
	{
		if($this->messageID)
		{
			$message = new NMessage($this->messageID);
			$sender = $message->getSender();
			if($sender instanceof UserMessageSender)
			{
				$this->user = $sender->getUser();
				$this->username = $this->user->username;
				$this->subject = 'Re: '.$message->subject;
			}
		}
		
		parent::readData();
	}
	
	/**
	 * @see Form::validate
	 */
	public function validate()
	{
		if(!$this->user->userID)
		{
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.messages.create.invalidUser'));
		}
		
		$sql = "SELECT *
				FROM ugml_user_ignore
				WHERE senderID = ".WCF::getUser()->userID."
					AND recipentID = ".$this->user->userID;
		if(WCF::getDB()->getFirstRow($sql))
		{
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException(WCF::getLanguage()->get('wot.messages.create.ignored'));
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables()
	{
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'subject' => $this->subject,
			'text' => $this->text,
			'user' => $this->user
		));
	}
	
	/**
	 * @see Form::save()
	 */
	public function save()
	{
		parent::save();
		
		NMessageEditor::create(
			$this->user->userID, array(1, WCF::getUser()->userID),
			$this->subject, $this->text, 4);
	}

	/**
	 * @see Page::show()
	 */
	public function show()
	{
		// check user
		if (!WCF::getUser()->userID) message('Zutritt nicht erlaubt!');
		
		parent::show();
	}
}
?>