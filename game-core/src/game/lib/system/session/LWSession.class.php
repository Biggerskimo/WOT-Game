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

// wot imports
require_once(LW_DIR.'lib/data/user/LWUserSession.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/session/CookieSession.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * LWSession extends the CookieSession class with cms specific functions.
 *
 * @package	game.wot.session
 * @author	Biggerskimo
 */
class LWSession extends CookieSession {
	protected $userSessionClassName = 'LWUserSession';
	protected $guestSessionClassName = 'LWUserSession';
	protected $styleID = 0;
	protected $useCookies = false;

	/**
	 * Initialises the session.
	 */
	public function init() {
		parent::init();

		// handle style id
		if ($this->user->userID) $this->styleID = $this->user->styleID;
		if (($styleID = $this->getVar('styleID')) !== null) $this->styleID = $styleID;

	}

	/**
	 * Resets the user data in the session.
	 * 
	 * @param	bool	reset
	 */
	public function setUpdate($update = true) {
		if(!$update) $this->userDataReset = false;
		else $this->userDataReset = true;
	}

	/**
	 * Sets the active style id.
	 *
	 * @param 	integer		$newStyleID
	 */
	public function setStyleID($newStyleID) {
		$this->styleID = $newStyleID;
		if ($newStyleID > 0) $this->register('styleID', $newStyleID);
		else $this->unregister('styleID');
	}

	/**
	 * Returns the active style id.
	 *
	 * @return	integer
	 */
	public function getStyleID() {
		return $this->styleID;
	}

	/**
	 * @see CookieSession::handleCookie()
	 */
	protected function handleCookie() {
		if (isset($_COOKIE[COOKIE_PREFIX.'cookieHash'])) {
			if ($_COOKIE[COOKIE_PREFIX.'cookieHash'] != $this->sessionID) {
				$this->useCookies = false;
			}
		}
		else {
			$this->useCookies = false;
		}

		if (!$this->useCookies) {
			HeaderUtil::setCookie('cookieHash', $this->sessionID, 2147483647);
		}
	}

	/**
	 * @see Session::update()
	 */
	public function update() {
		if ($this->doNotUpdate) return;

		$sessionVariablesSQL = $userDataSQL = '';

		// save updated session variables
		if ($this->sessionVariableChanged) {
			$sessionVariablesSQL = ", sessionVariables = '".escapeString(serialize($this->sessionVariables))."'";
		}

		// save updates user data
		if ($this->userDataChanged) {
			$userDataSQL = ", userData = '".escapeString(serialize($this->user))."'";
		}

		// reset update data
		if ($this->userDataReset) {
			$userDataSQL = ", userData = ''";
		}

		$sql = "UPDATE 	wcf".WCF_N."_".$this->sessionTable."
			SET 	ipAddress = '".escapeString($this->ipAddress)."',
				userAgent = '".escapeString($this->userAgent)."',
				requestURI = '".escapeString($this->requestURI)."',
				requestMethod = '".escapeString($this->requestMethod)."',
				packageID = ".PACKAGE_ID."
				".$sessionVariablesSQL."
				".$userDataSQL."
				".$this->updateSQL."
			WHERE 	sessionID = '".$this->sessionID."'";
		WCF::getDB()->sendQuery($sql);
	}
}
?>