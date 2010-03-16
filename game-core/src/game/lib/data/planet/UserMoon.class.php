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
 * Holds all functions of user moons
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.planet.moon
 */
class UserMoon extends Planet {
	private $cPlanetID;
	
	/**
	 * Creates a new moon object of a user
	 *
	 * @param	int		planet id
	 * @param	array	data row
	 */
	public function __construct($planetID = null, $row = null, $updateLastActivity = true) {
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
     * Updates the ressources
     *
     * @param	float	metal
     * @param	float	crystal
     * @param	float	deuterium
     */
    public function addRessources($metal = 0.0, $crystal = 0.0, $deuterium = 0.0) {
    	$this->data['metal'] += $metal;
    	$this->data['crystal'] += $crystal;
    	$this->data['deuterium'] += $deuterium;

    	$this->update = true;
    }

    /**
     * @see Planet::checkFields()
     */
    public function checkFields() {
		$fields = $this->getMaxFields();
		$usedFields = $this->getUsedFields();

		if($usedFields < $fields) return true;
		else return false;
	}

	/**
	 * @see Planet::getMaxFields()
	 */
	public function getMaxFields() {
		$lBFields = floor(1 + pow($this->lunar_base, 1.6));
		$mSFields = floor(pow(($this->diameter / 1000), 2));

		$fields = min($lBFields, $mSFields);

		return $fields;
	}

    /**
     * @see Planet::getBuildableBuildings()
     */
    public function getBuildableBuildings() {
    	$buildings = array(14, 21, 41, 42, 43, 44);

    	return $buildings;
    }

	public function __toString() {
		if($this->id_owner == WCF::getUser()->userID) {
			if($this->name == 'Mond') {
				return '<a href="overview.php?cp='.$this->planetID.'" target="Mainframe">Mond</a> <a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
			} else {
				return '<a href="overview.php?cp='.$this->planetID.'" target="Mainframe">Mond '.$this->name.'</a> <a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
			}
		} else {
			if($this->name == 'Mond') {
				return 'Mond <a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
			} else {
				return 'Mond '.$this->name.' <a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
			}
		}
	}
    /**
     * Returns the attached planet.
     */
    public function getPlanet() {
    	if($this->cPlanetID == 0) {
	    	$sql = "SELECT *
	    			FROM ugml_planets
	    			WHERE galaxy = ".$this->galaxy.
	    			" AND system = ".$this->system.
	    			" AND planet = ".$this->planet.
	    			" AND planetKind = 1";
	    	$row = WCF::getDB()->getFirstRow($sql);
	    	
	    	if(!$row) {
	    		$this->cPlanetID = -1;
	    		
	    		return null;
	    	}
	    	$moon = Planet::getInstance(null, $row);
	    	$this->cPlanetID = $moon->planetID;
	    	
	    	return $moon; 
    	}
    	else if($this->cPlanetID == -1) {
    		return null;
    	}
    	
    	return Planet::getInstance($this->cPlanetID);
    }
}
?>