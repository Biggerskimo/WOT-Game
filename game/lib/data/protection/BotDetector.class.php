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
 * Executes bot detector classes
 */
class BotDetector {
	protected $detectors = array();
	
	/**
	 * Starts the bot detection
	 */
	public function __construct() {
		$this->getDetectors();
		$this->executeDetectors();
	}
	
	/**
	 * Reads the detectors to start from the database
	 */
	protected function getDetectors() {
		$sql = "SELECT *
				FROM ugml_bot_detector
				WHERE executionProbability > ".rand(0, 99)."
					AND '".$_SERVER['PHP_SELF'].LWUtil::getArgsStr()."' REGEXP locationPattern
				ORDER BY executionPriority DESC";
		
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$this->detectors[$row['detectorID']] = $row;
		}
	}
	
	/**
	 * Executes the detectors read from db
	 */
	protected function executeDetectors() {
		foreach($this->detectors as $detectorID => $detectorArray) {
			$className = $detectorArray['className'];
		
			require_once(LW_DIR.'lib/data/protection/'.$className.'.class.php');
			$detectorObj = new $className($detectorArray['detectorID']);
			
			if($detectorObj->checkBot()) {
				$detectorObj->saveDetection();
			}
		}
	}
}
?>