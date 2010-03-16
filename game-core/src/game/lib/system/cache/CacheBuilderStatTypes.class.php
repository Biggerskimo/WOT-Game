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

require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the stat types.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class CacheBuilderStatTypes implements CacheBuilder {
	public $data = array();

	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$sql = "SELECT *
				FROM ugml_stat_type
				GROUP BY ugml_stat_type.statTypeID";
		$result = WCF::getDB()->sendQuery($sql);		
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$sql = "SELECT DISTINCT `time`
					FROM ugml_stat_entry_archive
					WHERE statTypeID = ".$row['statTypeID'];
			$result2 = WCF::getDB()->sendQuery($sql);
			
			while($row2 = WCF::getDB()->fetchArray($result2)) {
				$row['times'][] = $row2['time'];
			}
			
			// range
			$sql = "SELECT MAX(rank)
						AS max
					FROM ugml_stat_entry
					WHERE statTypeID = ".$row['statTypeID'];
			$row += WCF::getDB()->getFirstRow($sql);
			
			$this->data[$row['statTypeID']] = $row;
		}
		
		$this->data = array('byStatTypeID' => $this->data,
							'byTypeName' => array());
		
		foreach($this->data['byStatTypeID'] as $statTypeID => $row) {
			$name = StringUtil::firstCharToUpperCase($row['type']).StringUtil::firstCharToUpperCase($row['name']);
			
			$this->data['byTypeName'][$name] = $row;
		}
		
		// get the names and the types
		$sql = "SELECT GROUP_CONCAT(DISTINCT type)
							AS types,
						GROUP_CONCAT(DISTINCT name)
							AS names
				FROM ugml_stat_type
				GROUP BY NULL";
		$row = WCF::getDB()->getFirstRow($sql);
		
		$this->data['types'] = explode(',', $row['types']);
		$this->data['names'] = explode(',', $row['names']);
		
		return $this->data;
	}
	/*public function getData($cacheResource) {
		$sql = "SELECT ugml_stat_type.*,
					GROUP_CONCAT(DISTINCT ugml_stat_entry_archive.`time`)
						AS times
				FROM ugml_stat_type
				LEFT JOIN ugml_stat_entry_archive
					ON ugml_stat_type.statTypeID = ugml_stat_entry_archive.statTypeID
				GROUP BY ugml_stat_type.statTypeID,
					ugml_stat_entry_archive.`time`";
		$result = WCF::getDB()->sendQuery($sql);		
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$row['times'] = explode(',', $row['times']);
			
			$paramPairs = explode(';', $row['params']);
			$row['params'] = array();
			foreach($paramPairs as $paramPair) {
				if(strlen($paramPair) >= 3) {
					list($param, $value) = explode(',', $paramPair);
					
					$row['params'][$param] = $value;
				}
			}
			
			// range
			$sql = "SELECT MAX(rank)
						AS max
					FROM ugml_stat_entry
					WHERE statTypeID = ".$row['statTypeID'];
			$row += WCF::getDB()->getFirstRow($sql);
			
			$this->data[$row['statTypeID']] = $row;
		}
		
		$this->data = array('byStatTypeID' => $this->data,
							'byTypeName' => array());
		
		foreach($this->data['byStatTypeID'] as $statTypeID => $row) {
			$name = StringUtil::firstCharToUpperCase($row['type']).StringUtil::firstCharToUpperCase($row['name']);
			
			$this->data['byTypeName'][$name] = $row;
		}
		
		// get the names and the types
		$sql = "SELECT GROUP_CONCAT(DISTINCT type)
							AS types,
						GROUP_CONCAT(DISTINCT name)
							AS names
				FROM ugml_stat_type
				GROUP BY NULL";
		$row = WCF::getDB()->getFirstRow($sql);
		
		$this->data['types'] = explode(',', $row['types']);
		$this->data['names'] = explode(',', $row['names']);
		
		return $this->data;
	}*/
}
?>