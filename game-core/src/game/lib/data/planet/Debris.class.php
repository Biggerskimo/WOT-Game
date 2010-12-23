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

// lw
require_once(LW_DIR.'lib/data/planet/Planet.class.php');

/**
 * Holds all functions of user planets
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.planet.debris
 */
class Debris extends Planet {
	protected static $createdDebris = array();

	/**
	 * Creates a new Planet object of a user
	 *
	 * @param	int		planet id
	 * @param	array	data row
	 */
	public function __construct($planetID = null, $row = null) {
		if($row === null) {
			$this->planetID = $planetID;

	    	$sql = "SELECT *
	    			FROM ugml".LW_N."_planets
	    			WHERE id = '".$planetID."'";
	    	$row = WCF::getDB()->getFirstRow($sql);
		} else $this->planetID = $row['id'];

    	parent::__construct($row);
    }

    /**
     * Checks if a debris exists
     *
     * @param	int		galaxy
     * @param	int		system
     * @param	int		planet
     * @return	int		planet id
     */
    public static function exists($galaxy, $system, $planet) {

    	if(!isset(self::$createdDebris[$galaxy][$system][$planet])) {
	    	$sql = "SELECT *
	    			FROM ugml".LW_N."_planets
	    			WHERE galaxy = '".$galaxy."'
	    				AND system = '".$system."'
	    				AND planet = '".$planet."'
	    				AND planet_type = '2'";
	    	$row = WCF::getDB()->getFirstRow($sql);

	    	$planet = Planet::getInstance(null, $row);
    	} else $planet = Planet::getInstance(self::$createdDebris[$galaxy][$system][$planet]);

		if($planet === null) return false;
		else return $planet->planetID;
    }

    /**
     * Creates a new debris
     *
     * @param	int		galaxy
     * @param	int		system
     * @param	int		planet
     * @param	float	metal
     * @param	float	crystal
     *
     * @return	Debris	created Object
     */
    public static function create($galaxy, $system, $planet, $metal = 0.0, $crystal = 0.0) {
    	$time = self::getDeletionTime();

    	$sql = "INSERT INTO ugml".LW_N."_planets
				SET name = 'Tr�mmerfeld',
					id_owner = '0',
					galaxy = '".$galaxy."',
					system = '".$system."',
					planet = '".$planet."',
					last_update = '".$time."',
					image = 'debris',
					diameter = '0',
					field_max = '0',
					temp_min = '-140',
					temp_max = '-100',
					className = 'Debris',
					planet_type = '2',
					metal = metal + '".$metal."',
					crystal = crystal + '".$crystal."'";
		WCF::getDB()->sendQuery($sql);

		$row = array('id' => WCF::getDB()->getInsertID(),
				'name' => 'Tr�mmerfeld',
				'id_owner' => 0,
				'galaxy' => $galaxy,
				'system' => $system,
				'planet' => $planet,
				'last_update' => $time,
				'image' => 'debris',
				'diameter' => 0,
				'field_max' => 0,
				'temp_min' => -140,
				'temp_max' => -100,
				'className' => 'Debris',
				'planet_type' => 2,
				'metal' => $metal,
				'crystal' => $crystal);
		$planet = Planet::getInstance(null, $row);

		// register
		self::$createdDebris[$galaxy][$system][$planet->planet] = $planet->planetID;

		return $planet;
    }

    /**
     * Updates the ressources
     *
     * @param	float	metal
     * @param	float	crystal
     */
    public function addRessources($metal = 0.0, $crystal = 0.0) {
    	$this->data['metal'] += $metal;
    	$this->data['crystal'] += $crystal;

    	$time = self::getDeletionTime();

    	$sql = "UPDATE ugml_planets
    			SET metal = '".$this->data['metal']."',
					crystal = '".$this->data['crystal']."',
					last_update = '".$time."'
    			WHERE id = '".$this->planetID."'";

    	WCF::getDB()->registerShutdownUpdate($sql);

    	$this->update = true;
    }
    
    /**
	 * @see Planet::calculateResources()
	 */
    
	public function calculateResources($time = null) {
		if(is_numeric($this->planetID)) {
			$this->getEditor()->update(array('deletionTime' => self::getDeletionTime($time)));
		}
	}
    
    /**
	 * Calculates the time when this debris will be deleted.
	 * 
	 * @return	int		posix timestamp
	 */
    public static function getDeletionTime($time = null) {
    	if($time === null) $time = time();
    
    	$hours = rand((24 * 60 * 60), (48 * 60 * 60));
    	$time += $hours;
    	
    	return $time;
    }

    /**
     * @see Planet::getBuildableBuildings()
     */
    public function getBuildableBuildings() {
    	$buildings = array();

    	return $buildings;
    }

    public function __toString() {
		return 'Orbit <a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
	}

}
?>