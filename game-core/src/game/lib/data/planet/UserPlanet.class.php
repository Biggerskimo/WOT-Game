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
 * @copyright	2007 - 2009 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.planet
 */
class UserPlanet extends Planet {
	private $moonID = 0;
	
	/**
	 * Creates a new Planet object of a user
	 *
	 * @param	int		planet id
	 * @param	array	data row
	 */
	public function __construct($planetID = null, $row = null, $updateLastActivity = true) {
		if($row === null) {
			$this->planetID = intval($planetID);

	    	$sql = "SELECT *
	    			FROM ugml".LW_N."_planets
	    			WHERE id = ".$planetID;
	    	$row = WCF::getDB()->getFirstRow($sql);
		} else $this->planetID = intval($row['id']);

    	parent::__construct($row);

    	$this->planetID = intval($this->planetID);
    }

    /**
     * Updates the ressources
     *
     * @param	float	metal
     * @param	float	crystal
     * @param	float	deuterium
     */
    public function addRessources($metal = 0.0, $crystal = 0.0, $deuterium = 0.0) {
    	$this->metal += $metal;
    	$this->crystal += $crystal;
    	$this->deuterium += $deuterium;
    }

    /**
     * @see Planet::getBuildableBuildings()
     */
    public function getBuildableBuildings() {
    	$buildings = array(1, 2, 3, 4, 12, 13, 14, 15, 21, 22, 23, 24, 31, 33, 34, 44);

    	return $buildings;
    }
    
    /**
     * Returns the attached moon.
     */
    public function getMoon() {
    	if($this->moonID == 0) {
	    	$sql = "SELECT *
	    			FROM ugml_planets
	    			WHERE galaxy = ".$this->galaxy.
	    			" AND system = ".$this->system.
	    			" AND planet = ".$this->planet.
	    			" AND planetKind = 3";
	    	$row = WCF::getDB()->getFirstRow($sql);
	    	
	    	if(!$row) {
	    		$this->moonID = -1;
	    		
	    		return null;
	    	}
	    	$moon = Planet::getInstance(null, $row);
	    	$this->moonID = $moon->planetID;
	    	
	    	return $moon; 
    	}
    	else if($this->moonID == -1) {
    		return null;
    	}
    	
    	return Planet::getInstance($this->moonID);
    }
}
?>