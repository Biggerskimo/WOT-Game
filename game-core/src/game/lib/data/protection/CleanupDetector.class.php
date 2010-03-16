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

require_once(LW_DIR.'lib/data/protection/BotDetectorClass.class.php');

/**
 * Uses the detector interface to cleanup the requests.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class CleanupDetector extends BotDetectorClass {
	const REQUEST_COUNT = 500;
	
	protected $deleteRequests = 0;

	/**
	 * @see	BotDetectorInterface::checkBot()
	 */
	public function checkBot() {
		// get last requests
		$sql = "SELECT COUNT(*) AS count
				FROM ugml_request
				WHERE userID = ".WCF::getUser()->userID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		$count = $row['count'];
		$count -= self::REQUEST_COUNT;
		
		if($count <= 0) return false;
		$this->deleteRequests = $count;
		$this->information = $count.' rows should get deleted.';
		
		return true;
	}
	
	/**
	 * @see	BotDetectorInterface::saveDetection()
	 */
	public function saveDetection() {
		$sql = "INSERT INTO ugml_archive_request
				SELECT * FROM ugml_request
				WHERE userID = ".WCF::getUser()->userID."
				ORDER BY requestID ASC
				LIMIT ".$this->deleteRequests;
		WCF::getDB()->sendQuery($sql);
	
		$sql = "DELETE FROM ugml_request
				WHERE userID = ".WCF::getUser()->userID."
				ORDER BY requestID ASC
				LIMIT ".$this->deleteRequests;
		WCF::getDB()->sendQuery($sql);
	}
}
?>