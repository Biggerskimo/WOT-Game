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

require_once(LW_DIR.'lib/data/ProxyObject.class.php');
require_once(LW_DIR.'lib/data/search/Lucene.class.php');

/**
 * Provides functions create, modify or delete lucene indexes.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class LuceneEditor extends ProxyObject {
	protected $luceneObj = null;
	
	/**
	 * Creates a new PlanetEditor object.
	 */
	public function __construct(Lucene $luceneObj) {
		$this->luceneObj = $luceneObj;
	}
	

	/**
	 * Creates a new Lucene index.
	 * 
	 * @param	string	name
	 * @param	array	fields
	 * @return	Lucene
	 */
	public static function create($name, $fields) {
		$luceneID = self::insert($name);
		
		$luceneObj = new Lucene($luceneID);
		
		$luceneObj->getEditor()->addField($fields);
		
		Zend_Search_Lucene::create(LW_DIR.'lucene/'.$name);
		
		return $luceneObj;
	}
	
	/**
	 * Inserts a new row into the db.
	 * 
	 * @param	string	name
	 * @return	int		luceneID
	 */
	public static function insert($name) {
		$sql = "INSERT INTO ugml_lucene
				(name)
				VALUES
				('".escapeString($name)."')";
		WCF::getDB()->sendQuery($sql);
		
		$luceneID = WCF::getDB()->getInsertID();
		
		return $luceneID;
	}
	
	/**
	 * Adds a new field.
	 * 
	 * @param	mixed	name or array
	 * @param	string	type if first argument is string
	 */
	public function addField($name, $type = null) {
		if(!is_array($name)) $array = array($name => $type);
		else $array = $name;
		
		$inserts = "";
		foreach($array as $name => $type) {
			switch($type) {
				case 'Keyword':
				case 'UnIndexed':
				case 'Binary':
				case 'Text':
				case 'UnStored':
					break;
				default:
					$type = 'Text';
			}
			if(!empty($inserts)) {
				$inserts .= ",";
			}
			$inserts .= "(".$this->luceneID.",'".escapeString($name)."', '".escapeString($type)."')";
		}
		
		if(!empty($inserts)) {
			$sql = "INSERT INTO ugml_lucene_field
					(luceneID, fieldName, fieldType)
					VALUES ".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
	}

	/**
	 * @see ProxyObject::getObject()
	 */
	protected function getObject() {
		return $this->luceneObj;
	}
}
?>