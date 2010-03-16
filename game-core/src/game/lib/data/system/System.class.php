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


/**
 * Represents a system
 */
class System {
	protected $planets = null;
	protected $galaxy = null;
	protected $system = null;

	/**
	 * Loads the data
	 *
	 * @param	int		galaxy
	 * @param	int		system
	 */
    public function __construct($galaxy, $system) {
    	$this->galaxy = $galaxy;
    	$this->system = $system;

    	$sql = "SELECT *,
    				ugml".LW_N."_planets.galaxy AS galaxy,
    				ugml".LW_N."_planets.system AS system,
    				ugml".LW_N."_planets.planet AS planet,
    				ugml".LW_N."_planets.metal AS metal,
    				ugml".LW_N."_planets.crystal AS crystal,
    				ugml".LW_N."_planets.id AS id
    			FROM ugml".LW_N."_planets
    			LEFT JOIN ugml".LW_N."_users
    				ON ugml".LW_N."_users.id = ugml".LW_N."_planets.id_owner
    			LEFT JOIN ugml".LW_N."_alliance
    				ON ugml".LW_N."_users.ally_id = ugml".LW_N."_alliance.id
    			LEFT JOIN ugml".LW_N."_stat
    				ON ugml".LW_N."_users.id = ugml".LW_N."_stat.userID
    				WHERE ugml".LW_N."_planets.galaxy = '".$galaxy."'
    				AND ugml".LW_N."_planets.system = '".$system."'";
    	$planets = WCF::getDB()->getResultList($sql);

    	foreach($planets as $planet) {
    		switch($planet['planet_type']) {
    			case 1:
    				$type = 'planet';
    				break;
    			case 2:
    				$type = 'debris';
    				break;
    			case 3:
    				$type = 'moon';
    				break;
    		}

    		$this->planets[$planet['planet']][$type] = $planet;
    	}


    	for($i = 1; $i <= 15; $i++) {
    		if(!isset($this->planets[$i]['planet']) || @$this->planets[$i]['planet']->className == 'FreePlanet') $this->planets[$i]['planet'] = null;
			if(!isset($this->planets[$i]['moon'])) $this->planets[$i]['moon'] = null;
			if(!isset($this->planets[$i]['debris'])) $this->planets[$i]['debris'] = null;
	   	}

    }

    /**
     * Returns a planet
     *
     * @param	int		position
     * @param	string	type (planet, moon, debris)
     *
     * @return	Planet	extended class of Planet
     */
    public function getPlanet($position, $type = 'planet') {
    	switch($type) {
    		case 'planet':
    		case 'moon':
    		case 'debris':
    			break;
    		case 1:
    			$type = 'planet';
    			break;
    		case 3:
    			$type = 'moon';
    			break;
    		case 2:
    			$type = 'debris';
    			break;
    		default:
    			$type = 'planet';
    	}
    	
    	//print_r($this->planets);
    	if(isset($this->planets[$position][$type])) {
	    	$planetRow = $this->planets[$position][$type];

	    	$className = $planetRow['className'];
	    	$planetID = $planetRow['id'];

			//print_r($planetRow);

	    	require_once(LW_DIR.'lib/data/planet/'.$className.'.class.php');
	    	$planet = new $className(null, $planetRow);

	    	//echo $planet.' = new '.$className.'(null, '.print_r($planetRow, true).');<br />';

	    	return $planet;
    	} else return null;
    }

	/**
	 * Returns the deuterium that is needed to phalanx this system.
	 *
	 * @return	int		needed deuterium
	 */
    public function getPhalanxCosts() {
    	$costs = 500 * (abs(LWCore::getPlanet()->system - $this->system) + 2) * pow(0.9, LWCore::getPlanet()->sensor_phalanx) * pow((1 + 0.001 * pow(0.9, (LWCore::getPlanet()->sensor_phalanx + 3))), (LWCore::getPlanet()->phalanx_views + 1));

		return $costs;
    }
}
?>