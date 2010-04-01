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

// wcf imports
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

require_once(LW_DIR.'lib/data/user/UserSettings.class.php');

/**
 * Represents a user session in the game.
 *
 * @package		game.wot.user
 * @author		Biggerskimo
 * @copyright	2006-2009 Lost Worlds <http://lost-worlds.net>
 */
class LWUserSession extends UserSession {
	protected $buddies = null;
	protected $outstandingNotifications = null;
	protected $firstRessources = array();
	public $actualProduction = array();
	
	protected $settings = array();
	public $stats = array();
	
	const STAT_TYPE_ID = 1;

	/**
	 * @see UserSession::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null) {
		// user data
		$this->sqlSelects .= " wot_user.*, ";
		$this->sqlJoins .= " LEFT JOIN ugml".LW_N."_users
								AS wot_user
								ON wot_user.id = user.userID ";
		
		// new stats
		$this->sqlSelects .= " wot_stat.rank AS wotRank,
								wot_stat.points AS wotPoints, ";
		$this->sqlJoins .= " LEFT JOIN ugml_stat_entry 
								AS wot_stat
								ON wot_stat.statTypeID = ".self::STAT_TYPE_ID."
									AND wot_stat.relationalID = user.userID ";
		
		// new stats 2
		$this->sqlSelects .= " GROUP_CONCAT(DISTINCT
									CONCAT(wot_stat2.statTypeID, ',', wot_stat2.rank, ',', wot_stat2.points)
									SEPARATOR ';')
								AS statStr,";
		$this->sqlJoins .= " LEFT JOIN ugml_stat_entry 
								AS wot_stat2
								ON wot_stat2.relationalID = user.userID ";
		
		// buddies
		$this->sqlSelects .= " CONCAT(
									COALESCE(GROUP_CONCAT(DISTINCT wot_buddy1.owner SEPARATOR ','), ''),
									',',
									COALESCE(GROUP_CONCAT(DISTINCT wot_buddy2.sender SEPARATOR ','), ''))
								AS buddy,";
		$this->sqlJoins .= " LEFT JOIN ugml_buddy
								AS wot_buddy1
								ON wot_buddy1.sender = user.userID";
		$this->sqlJoins .= " LEFT JOIN ugml_buddy
								AS wot_buddy2
								ON wot_buddy2.owner = user.userID";
		
		// settings
		$this->sqlSelects .= " GROUP_CONCAT(DISTINCT CONCAT(wot_setting.setting, ',', wot_setting.value) SEPARATOR '|') AS settingsStr,";
		$this->sqlJoins .= " LEFT JOIN ugml_user_setting
								AS wot_setting
								ON user.userID = wot_setting.userID";
		
		// alliance
		$this->sqlSelects .= " wot_alliance.ally_tag AS allianceTag,
								wot_alliance.ally_name AS allianceName,
								wot_alliance.id AS allianceID,";
		$this->sqlJoins .= " LEFT JOIN ugml_alliance
								AS wot_alliance
								ON wot_user.ally_id = wot_alliance.id";
		
		// other selects
		$this->sqlSelects .= " wot_user.id AS lwUserID, wot_user.current_planet AS actualPlanet, wot_user.banned AS wotBanned, ";
		
		parent::__construct($userID, $row, $username);
		
		$this->points = $this->wotPoints;
		$this->rank = $this->wotRank;
		
		// process settings
		$parts = explode('|', $this->settingsStr);
		foreach($parts as $part) {
			if(!empty($part) && strpos($part, ',')) {
				list($setting, $value) = explode(',', $part);
				
				$this->settings[$setting] = $value;
			}
		}
		
		// process stats
		$parts = explode(';', $this->statStr);
		foreach($parts as $part) {
			if(!empty($part) && strpos($part, ',')) {
				list($statTypeID, $rank, $points) = explode(',', $part);
				
				$this->stats[$statTypeID] = array('rank' => $rank, 'points' => $points);
			}
		}
		
		//$this->checkPlanetChange();
	}
	
	/**
	 * Returns the setting by a given identifier.
	 *
	 * @param	string	identifier
	 * @return	value
	 */
	public function getSetting($setting) {
		if(isset($this->settings[$setting])) {
			return unserialize($this->settings[$setting]);
		}
		return null;
	}
	
	/**
	 * Sets a setting with an identifier and value.
	 *
	 * @param	string	setting
	 * @param	mixed	value
	 */
	public function setSetting($setting, $value, $expireTime = 0x7FFFFFFF) {
		$svalue = serialize($value);
		
		$sql = "REPLACE INTO ugml_user_setting
				(userID, setting, expireTime, value)
				VALUES
				(".$this->userID.", '".escapeString($setting)."', ".$expireTime.", '".escapeString($svalue)."')";
		WCF::getDB()->sendQuery($sql);
		
		$this->settings[$setting] = $svalue;
		
		WCF::getSession()->setUpdate(true);
	}
	 

	/**
	 * Initialises the user session.
	 */
	public function init() {
		parent::init();

		$this->ignores = $this->outstandingNotifications = null;
		$this->checkPlanetChange();
	}
	
	/**
	 * Sets $user.
	 */
	public function setUserVar() {
		global $user;
		
		$user = $this->data;
	}

	/**
	 * Returns true, if the active user has a buddy with the given user.
	 *
	 * @param	int		user id
	 * @return	bool
	 */
	public function hasBuddy($userID) {
		if ($this->buddies === null) {
			if ($this->buddy) {
				$this->buddies = ArrayUtil::trim(explode(',', $this->buddy));
			}
			else {
				$this->buddies = array();
			}
		}

		return in_array($userID, $this->buddies);
	}

	/**
	 * @see	PM::getOutstandingNotifications()
	 */
	public function getOutstandingNotifications() {
		if ($this->outstandingNotifications === null) {
			require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');
			$this->outstandingNotifications = PM::getOutstandingNotifications(WCF::getUser()->userID);
		}

		return $this->outstandingNotifications;
	}
	
	/**
	 * Returns the active planet.
	 * 
	 * @return	Planet
	 */
	public function getPlanet() {
		return Planet::getInstance($this->current_planet);
	}

	/**
	 * Changes the actual planet.
	 *
	 * @param	int		planet id
	 * @param	string	class name
	 */
	public function changePlanet($planetID, $planetClassName) {
		global $planetrow;

		$sql = "UPDATE ugml".LW_N."_users
				SET current_planet = ".$planetID.",
					planetClassName = '".$planetClassName."'
				WHERE id = ".$this->userID;
		WCF::getDB()->sendQuery($sql);

		$this->current_planet = $planetID;
		$this->planetClassName = $planetClassName;

		//WCF::getSession()->setUpdate(true);

		// reinit planet
		//LWCore::initPlanet();
		
		Session::resetSessions($this->userID, true, false);

		// update old ugamela vars
		$planetrow = array_merge((array)$planetrow, (array)$this->getPlanet(), $this->getPlanet()->data);
	}
	
	/**
	 * Checks if the user requests a planet change.
	 */
	protected function checkPlanetChange() {
		if(!(isset($_GET['cp']) || isset($_POST['cp'])) || !(isset($_REQUEST['page']) || isset($_REQUEST['form']) || isset($_REQUEST['action']))) {
			return;
		}
		
		$planetID = isset($_GET['cp']) ? intval(@$_GET['cp']) : intval(@$_POST['cp']);
		
		$planets = Planet::getByUserID($this->userID);
		
		if(isset($planets[$planetID])) {
			$this->changePlanet($planetID, $planets[$planetID]->className);
			
			// now create new request without planet change (forwarding)
			unset($_REQUEST['cp']);
			if(isset($_REQUEST['re'])) {
				unset($_REQUEST['re']);
			}
			
			$location = LWUtil::getFileName().'.php'.LWUtil::getArgsStr();
			
			WCF::getDB()->deleteShutdownUpdates();
			
			header('Location: '.$location);
			exit;
		}
	}
	
	/**
	 * Checks if this user has game operator rights.
	 * 
	 * @return	boolean
	 */
	public function isGO() {
		return ($this->authlevel > 0);
	}
	
	/**
	 * Returns the wot user config object for this user.
	 * 
	 * @return	WOTUserConfig
	 */
	public function getConfig() {
		return WOTUserConfig::getInstance($this->userID);
	}
}
?>