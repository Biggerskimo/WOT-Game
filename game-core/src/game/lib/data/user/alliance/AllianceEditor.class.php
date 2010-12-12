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

require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');

/**
 * Editor for creating, editing and deleting alliances
 * 
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceEditor extends Alliance {
	protected $ranks = array();
	
	/**
	 * Creates a new alliance
	 * 
	 * @param	string	name
	 * @param 	string	tag
	 * @param	int		owner id
	 * @param	string	name of the leader rank
	 * @return	AllianceEditor
	 */
	public static function create($allianceName, $allianceTag, $userID, $leaderRankName = 'Leader') {
		if(Alliance::getByUserID($userID) !== null) return null;
		
		// create
		$sql = "INSERT INTO ugml_alliance
				(ally_register_time, ally_ranks, ally_owner, ally_owner_range)
				VALUES
				(".TIME_NOW.", 'a:0:{}', ".$userID.", '".escapeString($leaderRankName)."')";
		WCF::getDB()->sendQuery($sql);
		$insertID = WCF::getDB()->getInsertID();
		
		$editor = new AllianceEditor($insertID);
		$editor->setName($allianceName, $allianceTag);
		
		// add the first user
		$editor->addUser($userID);
		
		return $editor;
	}
	
	/**
	 * Saves the ranks if edited.
	 */
	public function __destruct() {
		if(count($this->ranks)) {
			$this->ally_ranks = serialize($this->ranks);
			
			$sql = "UPDATE ugml_alliance
					SET ally_ranks = '".escapeString($this->ally_ranks)."'
					WHERE id = ".$this->allianceID;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Deletes this alliance.
	 */
	public function delete() {
		$sql = "SELECT GROUP_CONCAT(id SEPARATOR ',') AS userIDsStr
				FROM ugml_users
				WHERE ally_id = ".$this->allianceID;
		$result = WCF::getDB()->getFirstRow($sql);
		
		Session::resetSessions($result['userIDsStr']);
		
		$sql = "DELETE FROM ugml_alliance
				WHERE id = ".$this->allianceID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Lets a user join this alliance
	 * 
	 * @param	int		user id
	 */
	public function addUser($userID) {
		$sql = "UPDATE ugml_users
				SET ally_id = ".$this->allianceID.",
					ally_rank_id = 0,
					ally_request = NULL,
					ally_register_time = ".TIME_NOW."
				WHERE id = ".$userID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "UPDATE ugml_alliance
				SET ally_members = ally_members + 1
				WHERE id = ".$this->allianceID;
		WCF::getDB()->sendQuery($sql);
		
		Session::resetSessions($userID);
	}
	
	/**
	 * Makes a user to leader
	 * 
	 * @param	int		user id
	 * @param	string	owner name
	 */
	public function changeLeader($userID, $rankName = null) {
		if(Alliance::getByUserID($userID)->allianceID != $this->allianceID && $userID !== null) return;
		
		if($userID !== $this->ally_owner && $userID !== null) {
			$sql = "SELECT ally_rank_id
					FROM ugml_users
					WHERE id = ".$userID;
			$result = WCF::getDB()->getFirstRow($sql);
			$oldLeaderRank = $result['ally_rank_id'];
			
			$sql = "UPDATE ugml_users
					SET ally_rank_id = ".$oldLeaderRank."
					WHERE id = ".$this->ally_owner;
			$result = WCF::getDB()->sendQuery($sql);
			
			$sql = "UPDATE ugml_users
					SET ally_rank_id = 0
					WHERE id = ".$userID;
			$result = WCF::getDB()->sendQuery($sql);
			
			// set to leader		
			$this->ally_owner = $userID;
			
			Session::resetSessions($userID);
		}
		
		if($rankName !== null) $this->ally_owner_range = $rankName;
		$sql = "UPDATE ugml_alliance
				SET ally_owner = ".$this->ally_owner.",
					ally_owner_range = '".escapeString($this->ally_owner_range)."'
				WHERE id = ".$this->allianceID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes a user from this alliance
	 * 
	 * @param	int		user id
	 */
	public function deleteUser($userID) {
		$sql = "UPDATE ugml_users
				SET ally_id = NULL,
					ally_rank_id = 0
				WHERE id = ".$userID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "UPDATE ugml_alliance
				SET ally_members = ally_members - 1
				WHERE id = ".$this->allianceID;
		WCF::getDB()->sendQuery($sql);
		
		Session::resetSessions($userID);
	}
	
	/**
	 * Parses and saves the texts to the database.
	 * 
	 * @param	string	external text
	 * @param 	string	internal text
	 * @param	string	application template
	 */
	public function updateTexts($externalText = null, $internalText = null, $applicationTemplate = null) {
		if($externalText !== null) $this->ally_description = $externalText;		
		if($internalText !== null) $this->ally_text = $internalText;
		if($applicationTemplate !== null) $this->ally_request = $applicationTemplate;
		
		// save to db
		$sql = "UPDATE ugml_alliance
				SET ally_description = '".escapeString($this->ally_description)."',
					ally_text = '".escapeString($this->ally_text)."',
					ally_request = '".escapeString($this->ally_request)."'
				WHERE id = ".$this->allianceID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Sets the name and the tag
	 * 
	 * @param	string	name
	 * @param	string	tag
	 */
	public function setName($allianceName = null, $allianceTag = null) {
		if($allianceName !== null) $this->ally_name = $allianceName;
		if($allianceTag !== null) $this->ally_tag = $allianceTag;
		
		$sql = "UPDATE ugml_alliance
				SET ally_name = '".escapeString($this->ally_name)."',
					ally_tag = '".escapeString($this->ally_tag)."'
				WHERE id = ".$this->allianceID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Sets the links (logo and web)
	 * 
	 * @param	string	logo
	 * @param	string	web address
	 */
	public function changeLinks($allianceLogo = null, $webAddress = null) {
		if($allianceLogo !== null) $this->ally_image = $allianceLogo;
		if($webAddress !== null) $this->ally_web = $webAddress;
		
		$sql = "UPDATE ugml_alliance
				SET ally_image = '".escapeString($this->ally_image)."',
					ally_web = '".escapeString($this->ally_web)."'
				WHERE id = ".$this->allianceID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Sets a rank
	 * 
	 * @param	array	(rankID, rightID => value or rankID => false)
	 * @param	bool	save the rights (db optimizing)
	 * 
	 * If a rank (first dimension) in the array ist the boolean false,
	 * 	it will be deleted. Else it must be an array.
	 */
	public function setRank($param, $save = true) {
		if(!count($this->ranks)) $this->ranks = unserialize($this->ally_ranks);
		
		// rank
		foreach($param as $rankID => $rank) {
			// delete
			if($rank === false) {
				unset($this->ranks[$rankID]);
				continue;
			}
			
			// update
			$this->ranks[$rankID] = $rank;
		}
	}
	
	/**
	 * Adds a interrelation to another alliance.
	 * 
	 * @param	int		alliance id
	 * @param	int		interrelation type
	 * @param	int		interrelation state (0 for delete)
	 * @param	str		data
	 */
	public function addInterrelation($allianceID2, $interrelationType, $interrelationState, $data) {
		$existingInterrelation = $this->getInterrelation($allianceID2, $interrelationType);	
	
		if($interrelationState == 0) {
			// delete
			$sql = "DELETE FROM ugml_alliance_to_alliances
					WHERE allianceID".$existingInterrelation->ownAlliancePosition." = ".$this->allianceID."
						AND allianceID".$existingInterrelation->otherAlliancePosition." = ".$allianceID2;
		
		} else if($existingInterrelation !== null) {
			// update
			$sql = "UPDATE ugml_alliance_to_alliances
					SET interrelationState = ".$interrelationState
					.(($data !== null) ? ", data = '".escapeString($data)."'" : "").
					" WHERE allianceID".$existingInterrelation->ownAlliancePosition." = ".$this->allianceID."
						AND allianceID".$existingInterrelation->otherAlliancePosition." = ".$allianceID2;
		
		} else {
			// create
			$sql = "INSERT INTO ugml_alliance_to_alliances
					(allianceID1, allianceID2,
					 interrelationType, interrelationState,
					 creationTime, data)
					VALUES
					(".$this->allianceID.", ".$allianceID2.",
					 ".$interrelationType.", ".$interrelationState.",
					 ".TIME_NOW.", '".escapeString($data)."')";
		}
		WCF::getDB()->sendQuery($sql);
		
		$this->interrelations = array();
	}
}
?>