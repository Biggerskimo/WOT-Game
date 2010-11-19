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

require_once(LW_DIR.'lib/data/AbstractDecorator.class.php');
require_once(LW_DIR.'lib/util/PlanetUtil.class.php');

/**
 * Provides functions to edit planets.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class PlanetEditor extends AbstractDecorator {
	protected $planetID = 0;
	
	/**
	 * Creates a new PlanetEditor object.
	 */
	public function __construct($planetID) {
		$this->planetID = $planetID;
	}
	
	/**
	 * Creates a new planet.
	 * 
	 * @param	int		galaxy
	 * @param	int		system
	 * @param	int		planet
	 * @param	string	name
	 * @param	int		user id
	 * @param	float	default metal
	 * @param	float	default crystal
	 * @param	float	default deuterium
	 * @param	int		planet type id
	 * @param	int		timestamp
	 * @param	int		fields
	 * @param	int		max temperature
	 * @param	int		package id
	 * @return	Planet
	 */
	public static function create($galaxy, $system, $planet, $name, $userID, $metal = 0, $crystal = 0, $deuterium = 0, $planetTypeID = 1, $time = TIME_NOW, $fields = null, $maxTemp = 40, $packageID = PACKAGE_ID) {
		// get planet type information
		$sql = "SELECT classPath,
					planetKind,
					forceImage
				FROM ugml_planet_type
				WHERE planetTypeID = ".$planetTypeID;
		$row = WCF::getDB()->getFirstRow($sql);
				
		$classPath = $row['classPath'];
		$className = StringUtil::getClassName($classPath);
		$planetKind = $row['planetKind'];
		
		// collect other data
		$information = PlanetUtil::getLocationEnvironment($planet);
		
		if(empty($row['forceImage'])) {
			$image = $information['image'];
		}
		else {
			$image = $row['forceImage'];
		}
		
		if($fields === null) {
			$fields = $information['fields'];
		}
		$diameter = PlanetUtil::getDiameter($fields);
		
		if($maxTemp === null) {
			$maxTemp = $information['maxTemp'];
		}
		$minTemp = $maxTemp - 40;
		
		// insert
		$planetID = self::insert($galaxy, $system, $planet, $planetKind, $planetTypeID, $className, $name, $userID, $fields, $diameter, $minTemp, $maxTemp, $image, $time, $packageID);
	
		$planet = Planet::getInstance($planetID);
		
		// add resources
		$planet->getEditor()->changeResources($metal, $crystal, $deuterium);
		
		return $planet;
	}
	
	/**
	 * Inserts a new planet row.
	 * 
	 * @param	int		galaxy
	 * @param	int		system
	 * @param	int		planet
	 * @param	int		planetKind
	 * @param	int		planetTypeID
	 * @param	string	className
	 * @param	string	planet name
	 * @param	int		owner user id
	 * @param	int		fields
	 * @param	int		diameter
	 * @param	int		min temperature
	 * @param	int		max temperature
	 * @param	string	image name
	 * @param	int		timestamp
	 * @param	int		package id
	 * @return	int		planet id
	 */
	public static function insert($galaxy, $system, $planet, $planetKind, $planetTypeID, $className, $planetName, $userID, $fields, $diameter, $minTemp, $maxTemp, $image, $time = TIME_NOW, $packageID = PACKAGE_ID) {
		if(!$userID)
			$userID = "NULL";
		
		$sql = "INSERT INTO ugml_planets
				(galaxy, system, planet,
				 planet_type, planetKind, planetTypeID,
				 className, name, id_owner,
				 field_max, diameter, temp_min,
				 temp_max, image, last_update,
				 packageID)
				VALUES
				(".$galaxy.", ".$system.", ".$planet.",
				 ".$planetKind.", ".$planetKind.", ".$planetTypeID.", 
				 '".escapeString($className)."', '".escapeString($planetName)."', ".$userID.",
				 ".$fields.", ".$diameter.", ".$minTemp.",
				 ".$maxTemp.", '".escapeString($image)."', ".$time.",
				 ".$packageID.")";
		WCF::getDB()->sendQuery($sql);
		
		$planetID = WCF::getDB()->getInsertID();
		
		return $planetID;
	}
	
	/**
	 * Changes the resources.
	 * 
	 * @param	float	metal
	 * @param	float	crystal
	 * @param	float	deuterium
	 * @param	bool	absolute
	 */
	public function changeResources($metal = null, $crystal = null, $deuterium = null, $absolute = false) {
		$metal = intval($metal);
		$crystal = intval($crystal);
		$deuterium = intval($deuterium);
		
		// calc relative values
		if($absolute) {
			$metal -= $this->metal;
			$crystal -= $this->crystal;
			$deuterium -= $this->deuterium;
		}
		
		$this->metal += $metal;
		$this->crystal += $crystal;
		$this->deuterium += $deuterium;
		
		// mysql accepts constructs like "metal = metal + -500"
		$sql = "UPDATE ugml_planets
				SET metal = metal + ".$metal.",
					crystal = crystal + ".$crystal.",
					deuterium = deuterium + ".$deuterium."
				WHERE id = ".intval($this->planetID);
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Changes the level of a building or the count of ships on this planet.
	 * 
	 * @param	mixed	array or specid
	 * @param	int		level
	 */
	public function changeLevel($array, $levelChange = null) {
		if(!is_array($array)) {
			$array = array($array => $levelChange);
		}
		
		$updates = "";
		foreach($array as $specID => $levelChange) {
			$colName = Spec::getSpecVar($specID, 'colName');
			
			if(!empty($updates)) {
				$updates .= ",";
			}
			// mysql accepts constructs like "metal = metal + -500"
			$updates .= " `".$colName."` = `".$colName."` + ".intval($levelChange);
			
			$this->$colName += $levelChange;
		}
		
		if(!empty($updates)) {
			$sql = "UPDATE ugml_planets
					SET ".$updates." 
					WHERE id = ".intval($this->planetID);
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Updates variables in the database.
	 * 
	 * @param	array
	 */
	public function update($array) {
		$updates = "";
		foreach($array as $var => $val) {
			if(!empty($updates)) {
				$updates .= ", ";
			}
			$updates .= " `".$var."` = '".escapeString($val)."'";
		}
		if(!empty($updates)) {
			$sql = "UPDATE ugml_planets
					SET ".$updates."
					WHERE id = ".intval($this->planetID);
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Sets a new name.
	 * 
	 * @param	string	name
	 */
	public function rename($name) {
		$sql = "UPDATE ugml_planets
				SET name = '".escapeString($name)."'
				WHERE id = ".$this->planetID;
		WCF::getDB()->sendQuery($sql);
		
		$this->name = $name;
	}
	
	
	/**
	 * Deletes this planet.
	 */
	public function delete() {
		$sql = "DELETE FROM ugml_planets
				WHERE id = ".intval($this->planetID);
		WCF::getDB()->sendQuery($sql);
	}

	/**
	 * @see AbstractDecorator::getObject()
	 */
	protected function getObject() {
		return Planet::getInstance($this->planetID);
	}
}
?>