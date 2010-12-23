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

/**
 * Holds all functions to manage a alliance
 *
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class Alliance extends DatabaseObject {
	protected $interrelations = array();
	
	/**
	 * Creates a new Alliance Object
	 * 
	 * @param	int		alliance id
	 * @param	array	data row
	 */
	public function __construct($allianceID, $row = null) {
		if($row === null) {
			$sql = "SELECT *
					FROM ugml_alliance
					WHERE id = ".intval($allianceID);
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
		
		$this->allianceID = $this->id;
	}
	
	/**
	 * @see DatabaseObject::__get()
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		if(!defined('DO_NOT_PARSE_ALLIANCE_TEXTS') && ($name == 'ally_text' || $name == 'ally_description')) {
			require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
			$parser = MessageParser::getInstance();
			$parser->setOutputType('text/html');
			
			$value = $parser->parse($value, true, false, true);
			$value = str_replace('<br />', '', $value);
		}
		
		return $value;
	}
	
	/**
	 * Gets the alliance of this user.
	 * 
	 * @param	int		user id
	 * @param 	bool	editor
	 * 
	 * @return	Alliance
	 */
	public static function getByUserID($userID = null, $editor = false) {
		if($userID === null) $userID = WCF::getUser()->userID;
	
		$sql = "SELECT alliance.*
				FROM ugml_users
					AS user
				LEFT JOIN ugml_alliance
					AS alliance
					ON user.ally_id = alliance.id
				WHERE alliance.id IS NOT NULL
					AND user.id = ".$userID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		if(WCF::getDB()->countRows() == 0) return null;
		
		if($editor) {
			require_once(LW_DIR.'lib/data/user/alliance/AllianceEditor.class.php');
			return new AllianceEditor(null, $row);
		}
		return new Alliance(null, $row);
	}

	/**
	 * Returns the ranks. If no rank id is given, a multidimensional array
	 *  will be returned. If rank id is a boolean true, the rank of the
	 *  actual user will be returned.
	 * 
	 * @param	mixed	rank id
	 * @param	int		right id
	 * @return	mixed	array or int
	 */
	public function getRank($rankID = null, $right = null) {
		$ranks = unserialize($this->ally_ranks);
		
		// no rank id
		if($rankID === null) return $ranks;
		
		// get rank id of the user
		if($rankID === true) {
			if(WCF::getUser()->ally_id != $this->allianceID) return false;
			
			$rankID = WCF::getUser()->ally_rank_id - 1;
			$rankIdByUser = true;
		}
		
		if(!isset($ranks[$rankID]) && $rankID >= 0) return null;
		
		// return var
		if($right !== null) {
			// is leader
			if($this->ally_owner == WCF::getUser()->userID && $rankIdByUser) {
				return true;
			}
			
			return $ranks[$rankID][$right];
		}
		return $ranks[$rankID];
	}
	
	/**
	 * Sends a message to all member of this alliance
	 * 
	 * @param	str			subject (null for default)
	 * @param	str			text
	 * @param	int			rank id (-1 for all, 0 for leader)
	 * @param	Alliance	startalliance
	 */
	public function sendMessageToAll($subject, $message, $rankID = -1, $alliance = null) {
		if($alliance === null) $alliance = $this;	
	
		if($subject === null) $subject = WCF::getLanguage()->get('wot.alliance.message.defaultSubject', array('alliance' => $alliance, 'username' => WCF::getUser()->username));
		else {
			$subject = StringUtil::encodeHTML($subject);
		}
		require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/html');
		
		$message = $parser->parse($message, true, false, true);
		
		
		// get members
		$sql = "SELECT id
				FROM ugml_users
				WHERE ally_id = ".$this->allianceID;
		$result = WCF::getDB()->sendQuery($sql);
		
		// send
		require_once(LW_DIR.'lib/data/message/MessageEditor.class.php');
		while($row = WCF::getDB()->fetchArray($result)) {
			if( // send to all
				$rankID == -1
				// send only to one rank
				|| ($rankID > 0 && $row['ally_rank_id'] == $rankID)
				// send to leader
				|| ($rankID == 0 && $row['id'] == $this->ally_owner)) {
				
				MessageEditor::create($row['id'], $subject, $message, 0, $alliance, 2);
			}
		}
	}
	
	/**
	 * Returns a interrelation to another alliance
	 * 
	 * @param	int		alliance id
	 * @param	int		type
	 * @param	int		state
	 * @return	mixed	array or boolen (if type & state are given); null when none has been found
	 */
	public function getInterrelation($allianceID2 = -1, $interrelationType = null, $interrelationState = null) {
		if(!count($this->interrelations)) {
			$sql = "	SELECT ugml_alliance.*,
							ugml_alliance_to_alliances.interrelationType,
							ugml_alliance_to_alliances.interrelationState,
							ugml_alliance_to_alliances.creationTime,
							ugml_alliance_to_alliances.data,
							'1' AS ownAlliancePosition,
							'2' AS otherAlliancePosition
						FROM ugml_alliance_to_alliances
						LEFT JOIN ugml_alliance
							ON ugml_alliance_to_alliances.allianceID2 = ugml_alliance.id
						WHERE allianceID1 = ".$this->allianceID."
					UNION DISTINCT
						SELECT ugml_alliance.*,
							ugml_alliance_to_alliances.interrelationType,
							ugml_alliance_to_alliances.interrelationState,
							ugml_alliance_to_alliances.creationTime,
							ugml_alliance_to_alliances.data,
							'2' AS ownAlliancePosition,
							'1' AS otherAlliancePosition
						FROM ugml_alliance_to_alliances
						LEFT JOIN ugml_alliance
							ON ugml_alliance_to_alliances.allianceID1 = ugml_alliance.id
						WHERE allianceID2 = ".$this->allianceID;
			
			// save
			$result = WCF::getDB()->sendQuery($sql);
			
			while($row = WCF::getDB()->fetchArray($result)) {
				
				@$this->interrelations[$row['id']] = new Alliance(null, $row);
			}
		}
		
		// return
		if($allianceID2 == -1) {
			if($interrelationType !== null) {
				$interrelations = array();
				foreach($this->interrelations as $allianceID3 => $alliance) {
					if($alliance->interrelationType == $interrelationType) $interrelations[$allianceID3] = $alliance;
				}
				if(!count($interrelations)) return null;
				return $interrelations;
			}
			return $this->interrelations;
		}
		if($interrelationType === null) return $this->interrelations[$allianceID2];
		if($this->interrelations[$allianceID2]->interrelationType == $interrelationType) {
			if($interrelationState === null) return $this->interrelations[$allianceID2];
			if($this->interrelations[$allianceID2]->interrelationState == $interrelationState) return $this->interrelations[$allianceID2];
		}
		
		return null;
	}
	
	/**
	 * Returns the object of the leader
	 * 
	 * @return	LWUser	leader
	 */
	public function getLeader() {
		require_once(LW_DIR.'lib/data/user/LWUser.class.php');	
	
		return new LWUser($this->ally_owner);
	}
	
	/**
	 * Returns a linked alliance tag
	 *
	 * @return	string	linked alliance tag
	 */
	public function __toString() {
		return '<a href="index.php?page=Alliance&amp;allianceID='.$this->allianceID.'">['.StringUtil::encodeHTML($this->ally_tag).']</a>';
	}
}
?>