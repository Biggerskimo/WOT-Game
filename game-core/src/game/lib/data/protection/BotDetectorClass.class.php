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

require_once(LW_DIR.'lib/data/protection/BotDetectorInterface.class.php');

/**
 * Bot detectors can extend this class.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
abstract class BotDetectorClass implements BotDetectorInterface {
	protected $detectorID = 0;
	protected $information = '';
	
	/**
	 * @see BotDetectorInterface::__construct()
	 */
	public function __construct($detectorID) {
		$this->detectorID = $detectorID;
	}

	/**
	 * @see BotDetectorInterface::getInformation()
	 */
	public function getInformation() {
		return $this->information;
	}
	
	/**
	 * @see BotDetectorInterface::saveDetection()
	 */
	public function saveDetection() {
		$sql = "INSERT INTO ugml_bot_detection
				(detectorID, requestID, userID,
				 planetID, time, information)
				VALUES
				(".$this->detectorID.", ".LWCore::$requestID.", ".WCF::getUser()->userID.",
				 ".LWCore::getPlanet()->planetID.", ".TIME_NOW.", '".escapeString($this->information)."')";
		WCF::getDB()->sendQuery($sql);
	}
}
?>