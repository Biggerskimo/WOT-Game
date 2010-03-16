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

require_once(WCF_DIR.'lib/system/database/MySQLDatabase.class.php');

/**
 * Extends the default database with lw-specific functions
 *
 */
class LWMySQLDatabase extends MySQLDatabase {
	public $time = 0;

	/**
	 * Extends the MySQLDatabase class for the option disabling shutdownqueries
	 */
	public function __construct($host, $user, $password, $database, $charset = 'utf8', $usePConnect = false, $autoSelect = true) {
		if(defined('NO_SHUTDOWN_QUERIES')) $this->useShutdownQueries = false;

		parent::__construct($host, $user, $password, $database, $charset, $usePConnect, $autoSelect);
	}
	
	/**
	 * Catches the too many connections error and sets a file system lock.
	 */
	protected function connect() {
		try {
			parent::connect();
		} catch(DatabaseException $e) {
			
			if(defined("COOKIE_PREFIX") && isset($_COOKIE[COOKIE_PREFIX.'userID']) && $_COOKIE[COOKIE_PREFIX.'userID'])
			{
				LWUtil::lockAccount(intval($_COOKIE[COOKIE_PREFIX.'userID']), 10);
			}
			
			// throw again...
			throw $e;
		}
	}

	/**
	 * Deletes all shutdownqueries
	 */
	public function deleteShutdownUpdates() {
		$this->shutdownQueries = array();
	}

	/**
	 * @see MySQLDatabase::sendQuery()
	 */
	public function sendQuery($query, $limit = 0, $offset = 0) {
		$startTime = microtime(true);
		$result = parent::sendQuery($query . ($limit > 0 ? " LIMIT " . $limit . " OFFSET " . $offset : ""));
		$endTime = microtime(true);
		
		$this->time += $endTime - $startTime;
		
		return $result;
	}
}
?>