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
 * Detects more many clicks on the same page; logs the min, avg and max time
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class ManyClicksDetector extends BotDetectorClass {
	const REQUEST_COUNT = 500;

	/**
	 * @see	BotDetectorInterface::checkBot()
	 */
	public function checkBot() {
		// get last 500 requests
		$sql = "SELECT *
				FROM ugml_request
				WHERE userID = ".WCF::getUser()->userID."
					AND requestID > COALESCE((SELECT requestID
											  FROM ugml_bot_detection
											  WHERE userID = ".WCF::getUser()->userID."
												  AND detectorID = ".$this->detectorID."
											  ORDER BY detectionID
												  DESC 
											  LIMIT 1),
											 0)
				ORDER BY requestID DESC
				LIMIT ".self::REQUEST_COUNT;
		$result = WCF::getDB()->sendQuery($sql);
		
		$siteSeries = array();
		$lastClickTime = 0;
		
		// sum up data
		while($row = WCF::getDB()->fetchArray($result)) {
			$lastSite = end($siteSeries);
			if(@$lastSite['site'] == $row['site']) {
				$lastSite['clicks'][] = $lastClickTime - $row['time'];
				
				$key = end(array_keys($siteSeries));
				$siteSeries[$key] = $lastSite;
			} else {
				$row['clicks'] = array();
				$siteSeries[] = $row;
			}
			
			$lastClickTime = $row['time'];
		}
		
		// analyse
		foreach($siteSeries as $seriesID => $series) {
			switch($series['site']) {
				case 'galaxy':
					if(count($series['clicks']) + 1 >= 499) {
						for($i = 0; $i < 5; ++$i) unset($series['clicks'][$i]);
					
						$minTime = min($series['clicks']);
						$maxTime = max($series['clicks']);
						$avgTime = array_sum($series['clicks']) / count($series['clicks']);
						
						$this->information .= 'last '.(count($series['clicks']) + 6).' clicks on page '.$series['site'].' ('.$minTime.'-'.$avgTime.'-'.$maxTime.');';
					}
					break;
				case 'overview':
				case 'messages':
					for($i = 0; $i < 5; ++$i) unset($series['clicks'][$i]);
					
					if(count($series['clicks']) + 1 >= 50) {
						$minTime = min($series['clicks']);
						$maxTime = max($series['clicks']);
						$avgTime = array_sum($series['clicks']) / count($series['clicks']);
						 
						$this->information .= 'last '.(count($series['clicks']) + 6).' clicks on page '.$series['site'].' ('.$minTime.'-'.$avgTime.'-'.$maxTime.');';
					}
					break;
			}
		}
		
		if(empty($this->information)) return false;
		return true;
		
		/*$fastest = 60 * 60 * 24 * 7;
		$slowest = 0;
		
		$count = 0;
		while($row = WCF::getDB()->fetchArray($result)) {
			if(!isset($firstSite)) $firstSite = $row['site'];
			
			if($row['site'] == $firstSite) {
				++$count;
				
				if(!isset($lastTime)) $lastTime = $row['time'];
				else {
					$timeDiff = $lastTime - $row['time'];
					
					if($timeDiff < $fastest) $fastest = $timeDiff;
				}
			} else break;
		}*/
		
		$this->information = 'last '.$count.' clicks at page '.$firstSite;
			
		if($count == self::REQUEST_COUNT) return true;
		return false;
	}
}
?>