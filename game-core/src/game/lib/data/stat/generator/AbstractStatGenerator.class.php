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

require_once(LW_DIR.'lib/data/stat/generator/StatGenerator.class.php');
/**
 * This class provides basic functions for a stat generator.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
abstract class AbstractStatGenerator implements StatGenerator {
	public $template = 'statBit';
	
	protected $statTypeID = 0;
	
	protected $sqlSelects = "";
	protected $sqlJoins = "";
	protected $sqlConditions = "";
	protected $sqlGroupBy = "";
	
	/**
	 * @see StatGenerator::__construct()
	 */
	public function __construct($statTypeID) {
		$this->statTypeID = $statTypeID;
	}
	
	/**
	 * @see StatGenerator::generate()
	 */
	public function generate($param = array()) {
		$this->deleteEntries();
		
		$this->generateEntries();
		
		// rank
		$this->rankManagement();
		
		// re-init cache
		StatGeneratorFactory::init();
		WCF::getCache()->clearResource('statTypes-'.PACKAGE_ID, true);
	}
	
	/**
	 * This method is responsible for the rank management.
	 */
	protected function rankManagement() {
		// first we must create the new ranks ...
		$this->createCurrentRanks();
		// ... and calculate the changes
		$this->calculateChanges();
		// delete old archive data ...
		$this->cleanUpArchive();
		// ... and insert new
		$this->refreshArchive();
	}
	
	/**
	 * Creates the ranks for the current points.
	 */
	protected function createCurrentRanks() {
		$sql = "SET @cnt = 0;";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "UPDATE ugml_stat_entry
				SET rank = (@cnt := (@cnt + 1))
				WHERE statTypeID = ".$this->statTypeID."
				ORDER BY points DESC,
					relationalID DESC";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Calculates the changes for last day.
	 * 
	 * @param	string	additional conditions
	 */
	protected function calculateChanges($additionalConditions = "") {
		// before we can calculate the changes, we need comparision data.
		$comparisionTimestamp = $this->getComparisionTimestamp();
		
		$sql = "UPDATE ugml_stat_entry,
					ugml_stat_entry_archive
				SET ugml_stat_entry.`change` = (ugml_stat_entry_archive.rank - ugml_stat_entry.rank)
				WHERE ugml_stat_entry.statTypeID = ugml_stat_entry_archive.statTypeID
					AND ugml_stat_entry.relationalID = ugml_stat_entry_archive.relationalID
					AND ugml_stat_entry_archive.`time` = ".$comparisionTimestamp."
					AND ugml_stat_entry.statTypeID = ".$this->statTypeID.$additionalConditions;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Returns the timestamp for the comparision with the current ranks (=>changes).
	 * 
	 * @return	int		timestamp
	 */
	protected function getComparisionTimestamp() {
		StatGeneratorFactory::init();
		$cache = WCF::getCache()->get('statTypes-'.PACKAGE_ID, 'byStatTypeID');
		
		$optimalTimestamp = time() - 60 * 60 * 24;
		$nearestTimestamp = 0;
		
		foreach($cache[$this->statTypeID]['times'] as $time) {
			if(abs($time - $optimalTimestamp) < abs($nearestTimestamp - $optimalTimestamp)) {
				$nearestTimestamp = $time;
			}
		}
		return $nearestTimestamp;
	}
	
	/**
	 * Deletes old stat entries in the archive.
	 */
	protected function cleanUpArchive() {
		$comparisionTimestamp = $this->getComparisionTimestamp();
		
		$sql = "DELETE FROM ugml_stat_entry_archive
				WHERE statTypeID = ".$this->statTypeID."
				AND `time` < ".$comparisionTimestamp;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Inserts the current data in the archive.
	 */
	protected function refreshArchive() {
		$sql = "INSERT INTO ugml_stat_entry_archive
				SELECT *, ".time()."
				FROM ugml_stat_entry
				WHERE statTypeID = ".$this->statTypeID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new temporary entry.
	 *  It may be created between stat refreshes, is behind all other rank entries with 0 points.
	 *  
	 *  @param	int		relational id
	 */
	protected function createTemporaryEntry($relationalID) {
		$sql = "INSERT INTO ugml_stat_entry
				(statTypeID, relationalID)
				VALUES
				(".$this->statTypeID.", ".$relationalID.")";
		WCF::getDB()->sendQuery($sql);
		
		StatGeneratorFactory::init();
		WCF::getCache()->clearResource('statTypes-'.PACKAGE_ID, true);
	}
	
	/**
	 * Deletes the old stat entries.
	 */
	protected function deleteEntries() {
		$sql = "DELETE FROM ugml_stat_entry
				WHERE statTypeID = ".$this->statTypeID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Generates the new entries.
	 */
	abstract protected function generateEntries();
	
	/**
	 * Returns the relational id for this stat type of the logged in user.
	 * 
	 * @return	int		relational id
	 */
	abstract public function getCurrentRelationalID();
	
	/**
	 * Searches for the current position of the logged in user.
	 * 
	 * @param	int		relational id
	 * @return	int		rank
	 */
	protected function getCurrentRank($relationalID = null) {
		if($relationalID === null) {
			$relationalID = $this->getCurrentRelationalID();
		}
		
		// not in the stats; so show the tops
		if(!$relationalID) {
			return 1;
		}
		do {
			$sql = "SELECT rank
					FROM ugml_stat_entry
					WHERE statTypeID = ".$this->statTypeID."
						AND relationalID = ".$relationalID;
			$row = WCF::getDB()->getFirstRow($sql);
			
			if(isset($createdTemporary)) {
				break;
			}
			if(!@$row) {
				$this->createTemporaryEntry($relationalID);
				$createdTemporary = true;
				
				continue;
			}
		} while(false);
		
		return $row['rank'];
	}
	
	/**
	 * Searches for the current position of the logged in user and returns the points
	 * 
	 * @param	int		relational id
	 * @return	int		rank
	 */
	public function getPoints($relationalID = null) {
		if($relationalID === null) {
			$relationalID = $this->getCurrentRelationalID();
		}
		
		// not in the stats
		if(!$relationalID) {
			return 0;
		}
		do {
			$sql = "SELECT points
					FROM ugml_stat_entry
					WHERE statTypeID = ".$this->statTypeID."
						AND relationalID = ".$relationalID;
			$row = WCF::getDB()->getFirstRow($sql);
			
			if(isset($createdTemporary)) {
				break;
			}
			if(!@$row['points']) {
				$this->createTemporaryEntry($relationalID);
				$createdTemporary = true;
				
				continue;
			}
		} while(false);
		
		return $row['points'];
	}
	
	/**
	 * Returns the highest rank in this stat type.
	 * 
	 * @return	int		rank
	 */
	protected function getMaxRank() {		
		StatGeneratorFactory::init();
		$cache = WCF::getCache()->get('statTypes-'.PACKAGE_ID, 'byStatTypeID');
		$max = $cache[$this->statTypeID]['max'];
		
		return $max;
	}
	
	/**
	 * @see StatGenerator::getRows()
	 */
	public function getRows($start = 1, $rowCount = 100, $showRank = false) {
		// search for the user position
		if(!$start) {
			$max = $this->getMaxRank();
			
			// show all
			if($max <= $rowCount) {
				$start = 1;
			}
			// show only a part
			else {
				if($showRank) {
					$middle = $this->getCurrentRank($showRank);
				}
				else {
					$middle = $this->getCurrentRank();
				}
				
				$diffToTop = floor(($rowCount - 1) / 2);
				$diffToEnd = ceil(($rowCount - 1) / 2);
				
				if($middle - $diffToTop < 1) {
					$middle = $diffToTop + 1;
				}
				if($middle + $diffToEnd > $max) {
					$middle = $max - $diffToEnd;
				}
				
				$start = $middle - $diffToTop;
			}
		}
		
		$return = array();
		
		$sql = "SELECT ".$this->sqlSelects."
					ugml_stat_entry.rank,
					ugml_stat_entry.`change`,
					ugml_stat_entry.points					
				FROM ugml_stat_entry
				".$this->sqlJoins."
				WHERE statTypeID = ".$this->statTypeID."
					AND ugml_stat_entry.rank BETWEEN ".$start." AND ".($start + $rowCount - 1).$this->sqlConditions."
				".(!empty($this->sqlGroupBy) ? "GROUP BY ".substr($this->sqlGroupBy, 0, -1) : "")."
				ORDER BY rank";
		$result = WCF::getDB()->sendQuery($sql);
		//echo $sql;
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$return[$row['rank']] = $row;
		}
		
		return $return;
	}
	
	/**
	 * Returns the template name that should be used to display the stat rows.
	 * 
	 * @return	string 	template name
	 */
	public function getTemplateName() {
		return $this->template;
	}
	
	/**
	 * Returns the 'real' the name that should be selected in the select-field.
	 * 
	 * @return	string	real option
	 */
	public function getOptionName() {
		StatGeneratorFactory::init();
		$cache = WCF::getCache()->get('statTypes-'.PACKAGE_ID, 'byStatTypeID');
		$name = $cache[$this->statTypeID]['name'];
		
		return $name;
	}
}
?>