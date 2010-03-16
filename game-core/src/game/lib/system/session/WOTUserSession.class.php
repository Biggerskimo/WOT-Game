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

/**
 * Provides isGO() method.
 * 
 * @author	Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class WOTUserSession extends UserSession {
	/**
	 * @see UserSession::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null) {
		// user data		
		$this->sqlSelects .= " lw_user.*, ";
		$this->sqlJoins .= " LEFT JOIN ugml".LW_N."_users
								AS lw_user
								ON lw_user.id = user.userID ";
		
		// other selects
		$this->sqlSelects .= " lw_user.id AS lwUserID, ";
		
		parent::__construct($userID, $row, $username);
	}
	
	/**
	 * Checks if this user has game operator rights.
	 * 
	 * @return	boolean
	 */
	public function isGO() {
		return ($this->authlevel > 0);
	}
}
?>