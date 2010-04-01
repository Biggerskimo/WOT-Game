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

require_once(LW_DIR.'lib/util/SerializeUtil.class.php');
/**
 * All classes that are overview events should implement this class.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
abstract class Ovent extends DatabaseObject {
	protected static $cache = array();
	protected $poolData = array();
	
	/**
	 * Creates a new Ovent object.
	 * 
	 * @param	int		ovent id
	 * @param	array	database row
	 */
	public function __construct($oventID, $row = null) {
		if($row === null) {
			$sql = "SELECT *
					FROM ugml_ovent
					WHERE oventID = ".$oventID;
			$row = WCF::getDB()->sendQuery($sql);
		}
		
		parent::__construct($row);
	}
	
	public function __get($name) {
		$parent = parent::__get($name);
		
		if($parent === null) {
			$this->extractPool();
			
			if(isset($this->poolData[$name])) {
				return $this->poolData[$name];
			}
		}
		
		return $parent;
	}
	
	/**
	 * Unserializes the pool data.
	 */
	protected function extractPool() {
		if(!count($this->poolData)) {
			$this->poolData = SerializeUtil::unserialize($this->data['data']);
		}
	}
	
	/**
	 * Checks whether this ovent is highlighted.
	 *
	 * @return	bool	highlighted
	 */
	public function isHighlighted() {
		return $this->highlighted != 0;
	}
	
	/**
	 * Sets this ovent highlighted (or not)
	 */
	public function setHighlighted($highlighted = false) {
		$this->highlighted = intval($highlighted);
		
		$sql = "UPDATE ugml_ovent
				SET highlighted = ".$this->highlighted."
				WHERE oventID = ".$this->oventID;
		WCF::getDB()->sendQuery($sql);		
	}
	
	/**
	 * Returns the pool data.
	 *
	 * @return	array	data
	 */
	public function getPoolData() {
		$this->extractPool();
		
		return $this->poolData;
	}
	
	/**
	 * Returns the template which should process this ovent.
	 * 
	 * @return	string	template name
	 */
	abstract public function getTemplateName();
	
	/**
	 * Searches for the class path and returns an Ovent object.
	 * 
	 * @param	int		oventID
	 * @return	Ovent
	 */
	public static function getByOventID($oventID) {
		self::initCache();
		
		$sql = "SELECT *
				FROM ugml_ovent
				WHERE oventID = ".$oventID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		$classPath = self::$cache[$row['oventTypeID']]['classPath'];
		$className = StringUtil::getClassName($classPath);
		
		require_once(LW_DIR.$classPath);
		$obj = new $className(null, $row);
		
		return $obj;
	}
	
	/**
	 * Reads all rows matching the given conditions an returns an array of Ovent objects.
	 *
	 * @param	array	conditions
	 * @param	bool	lock (FOR UPDATE)
	 */
	public static function getByConditions($conditions, $forUpdate = false)
	{
		self::initCache();
		
		$sql = "SELECT *
				FROM ugml_ovent
				WHERE ";
		foreach($conditions as $key => $value) {
			$sql .= "`".$key."` = ".$value." AND ";
		}
		
		$result = WCF::getDB()->sendQuery(substr($sql, 0, -5)." ORDER BY `time` ASC".($forUpdate ? " FOR UPDATE" : ""));
		
		$rows = array();
		while($row = WCF::getDB()->fetchArray($result))	{
			$classPath = self::$cache[$row['oventTypeID']]['classPath'];
			$className = StringUtil::getClassName($classPath);
			
			require_once(LW_DIR.$classPath);
			
			$rows[$row['oventID']] = new $className(null, $row);
		}
		
		return $rows;
	}
	
	/**
	 * Reads the cache data.
	 */
	protected static function initCache() {
		if(!count(self::$cache)) {			
			WCF::getCache()->addResource('oventTypes-'.PACKAGE_ID, WCF_DIR.'cache/cache.oventTypes-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderOventTypes.class.php');
			self::$cache = WCF::getCache()->get('oventTypes-'.PACKAGE_ID);
		}
	}
	
	/**
	 * Returns the editor for this ovent.
	 *
	 * @return	OventEditor
	 */
	public function getEditor() {
		return new OventEditor($this);
	}
}
?>