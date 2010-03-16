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
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Tries to find onlinetimes of a user
 *
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class OnlineTimeAggregator {
	protected $user = null;
	protected $tableName = 'ugml_request';
	protected $stepTime = 3600;
	protected $minDelay = 900;
	
	protected $targetQueryTime = 0.5;
	protected $lastQueryTime = 0;
	
	protected $min = 0;
	protected $max = 0;
	
	protected $blocks = array();
	protected $lastWrittenBlock = -1;
	
	private $lastClickTime = 0;
	
	/**
	 * @see UserProfile::__construct()
	 */
	public function __construct(LWUser $user) {
		$this->user = $user;
	}
	
	/**
	 * Sets the used data table name.
	 * 
	 * @param	string
	 */
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}
	
	/**
	 * Sets the time, of which the clicks is read in each step to get analyzed.
	 * 
	 * @param	int		time
	 */
	public function setStepTime($time) {
		$this->stepTime = $time;
	}
	
	/**
	 * Sets the optimal query time in microseconds.
	 * 
	 * @param	int		query time
	 */
	public function setTargetQueryTime($time) {
		$this->targetQueryTime = $time;
	}
	
	/**
	 * Returns the blocks.
	 * 
	 * @return	array	blocks
	 */
	public function getBlocks() {
		return $this->blocks;
	}
	
	/**
	 * Loads the first and the last click of this user.
	 */
	protected function loadLimitations() {
		$sql = "SELECT MIN(`time`)
							AS min,
						MAX(`time`)
							AS max
				FROM ".$this->tableName."
				WHERE userID = ".$this->user->userID."
					AND `time` > ".$this->user->lastClickAnalyzationMin;
		// this may take a long time
		$row = WCF::getDB()->getFirstRow($sql);
		
		$this->min = $row['min'];
		$this->max = $row['max'];
	}
	
	/**
	 * Loads the clicks 
	 * 
	 * @param	int		start time
	 * @return	resource
	 */
	protected function getClicks($startTime) {
		$sql = "SELECT *
				FROM ".$this->tableName."
				WHERE userID = ".$this->user->userID."
					AND `time` BETWEEN ".$startTime." AND ".($startTime + $this->stepTime - 1)."
				ORDER BY `time` ASC";		
		$queryStartTime = microtime(true);
		$result = WCF::getDB()->sendQuery($sql);
		
		$this->lastQueryTime = microtime(true) - $queryStartTime;
		
		return $result;
	}
	
	/**
	 * Checks the last query execution time and returns the new step time.
	 */
	protected function setNextStepTime() {
		$multiplier = $this->targetQueryTime / $this->lastQueryTime;
		
		$multiplier = min(sqrt($multiplier), 1.25);
		
		$this->stepTime = intval($this->stepTime * $multiplier);
	}
	
	/**
	 * Starts analyzation.
	 */
	public function analyze() {
		$this->loadLimitations();
		
		$startTime = $this->min;
		$lastClickTime = $blockOpenTime = 0;
		
		do {
			$result = $this->getClicks($startTime);
			
			while($row = WCF::getDB()->fetchArray($result)) {
				$row['time'] = intval($row['time']);
				
				if($lastClickTime < ($row['time'] - $this->minDelay)) {
					// close block ...
					$this->blocks[] = array($blockOpenTime, $lastClickTime, $count);
					
					// ... and open new
					$blockOpenTime = $row['time'];
					$count = 0;
				}
				$lastClickTime = $row['time'];
				
				$count++;
			}
			// save memory
			unset($result);
			
			$startTime += $this->stepTime;
			
			$this->setNextStepTime();	
		} while($startTime < $this->max);
		
		// close block
		$this->blocks[] = array($blockOpenTime, $row['time']);
		
		// remove first block
		array_shift($this->blocks);
	}
	
	/**
	 * Writes the blocks to the database.
	 */
	public function writeBlocks() {
		$inserts = array();
		
		$lastClick = 0;
		foreach($this->blocks as $blockNo => $block) {
			$inserts[] = "(".$this->user->userID.", ".implode(", ", $block).")";
			
			$lastClick = $block[1];
		}
		
		$sql = "INSERT INTO ugml_onlinetime_block
				(userID, startTime, endTime, clicks)
				VALUES
				".implode(", ", $inserts);
		WCF::getDB()->sendQuery($sql);
		
		$sql = "UPDATE ugml_users
				SET lastClickAnalyzationMin = ".$lastClick."
				WHERE id = ".$this->user->userID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>