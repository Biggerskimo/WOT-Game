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
	
	const STAT_TYPE_ID = 1;

	/**
	 * @see UserSession::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null) {
		// user data		
		$this->sqlSelects .= " lw_user.*, ";
		$this->sqlJoins .= " LEFT JOIN ugml".LW_N."_users
								AS lw_user
								ON lw_user.id = user.userID ";
		
		// new stats
		$this->sqlSelects .= " wot_stat.rank AS wotRank,
								wot_stat.points AS wotPoints, ";
		$this->sqlJoins .= " LEFT JOIN ugml_stat_entry 
								AS wot_stat
								ON wot_stat.statTypeID = ".self::STAT_TYPE_ID."
									AND wot_stat.relationalID = user.userID ";
		
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
		$this->sqlSelects .= " GROUP_CONCAT(CONCAT(wot_setting.hash, ',', wot_setting.value) SEPARATOR ';') AS settingsStr,";
		$this->sqlJoins .= " LEFT JOIN ugml_user_setting
								AS wot_setting
								ON user.userID = wot_setting.userID";
		
		// other selects
		$this->sqlSelects .= " lw_user.id AS lwUserID, lw_user.current_planet AS actualPlanet, lw_user.banned AS wotBanned, ";
		
		parent::__construct($userID, $row, $username);
		
		$this->points = $this->wotPoints;
		$this->rank = $this->wotRank;
		
		// process settings
		$parts = explode(';', $this->settingsStr);
		foreach($parts as $part) {
			if(!empty($part)) {
				list($hash, $value) = explode(',', $part);
				
				$this->settings[$hash] = $value;
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
	public function getSetting($identifier) {
		return unserialize($this->settings[sha1($identifier)]);
	}
	
	/**
	 * Sets a setting with an identifier and value.
	 *
	 * @param	string	identifier
	 * @param	mixed	value
	 */
	public function setSetting($identifier, $value) {
		$hash = sha1($identifier);
		$svalue = serialize($value);
		
		$sql = "REPLACE INTO ugml_user_setting
				(userID, hash, value)
				VALUES
				(".$this->userID.", '".$hash."', '".escapeString($svalue)."')";
		WCF::getDB()->sendQuery($sql);
		
		$this->settings[$hash] = $svalue;
		
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