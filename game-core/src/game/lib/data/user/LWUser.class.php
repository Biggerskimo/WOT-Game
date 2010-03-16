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

require_once(LW_DIR.'lib/data/user/WOTUserConfig.class.php');

/**
 * Represents a user in the game.
 *
 * @package		game.wot.user
 * @author		Biggerskimo
 * @copyright	2006-2008 Lost Worlds <http://lost-worlds.net>
 */
class LWUser extends UserSession {
	protected $avatar = null;

	const STAT_TYPE_ID = 1;
	
	/**
	 * @see UserProfile::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null, $email = null) {
		$this->sqlSelects .= ' lw_user.*, lw_stat.*, lw_user.password AS gamePassword, ';

		$this->sqlJoins .= ' LEFT JOIN ugml_users lw_user ON (lw_user.id = user.userID) ';
		$this->sqlJoins .= ' LEFT JOIN ugml_stat lw_stat ON (lw_stat.userID = user.userID) ';
		
		// new stats
		$this->sqlSelects .= " wot_stat.rank AS wotRank,
								wot_stat.points AS wotPoints, ";
		$this->sqlJoins .= " LEFT JOIN ugml_stat_entry 
								AS wot_stat
								ON wot_stat.statTypeID = ".self::STAT_TYPE_ID."
									AND wot_stat.relationalID = user.userID ";
		
		parent::__construct($userID, $row, $username, $email);
		
		$this->points = $this->wotPoints;
		$this->rank = $this->wotRank;
	}
	
	/**
	 * Sets $user.
	 */
	public function setUserVar() {
		global $user;
		
		$user = $this->data;
	}
	
	/**
	 * Returns the username with linked coordinates
	 * 
	 * @return	string	username with coords
	 */
	public function getLinkedUsername() {
		return $this->username.
			' <a href="galaxy.php?g='.
			$this->galaxy.
			'&amp;s='.
			$this->system.
			'" target="Mainframe">['.
			$this->galaxy.
			':'.
			$this->system.
			':'.
			$this->planet.
			']</a>';
	}
	
	/**
	 * Returns the planet object of this user
	 * 
	 * @return	Planet
	 */
	public function getPlanet() {
		return Planet::getInstance($this->id_planet);
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