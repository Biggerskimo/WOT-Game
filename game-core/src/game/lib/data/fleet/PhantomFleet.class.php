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

require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');

/**
 * Creates a fleet object without a entity
 */
class PhantomFleet extends Fleet {
	protected $startPlanet = null;
	protected $endPlanet = null;
	protected $owner = null;

	/**
	 * Stores a planet object
	 *
	 * @param	obj		Planet
	 */
	public function storeStartPlanet($planetObj) {
		$this->startPlnaet = $planetObj;
	}

	/**
	 * Stores a planet object
	 *
	 * @param	obj		Planet
	 */
	public function storeEndPlanet($planetObj) {
		$this->endPlnaet = $planetObj;
	}
	
	/**
	 * Stores a user object
	 * 
	 * @param	obj		LWUser
	 */
	public function storeOwner($userObj) {
		$this->owner = $userObj;
	}

	/**
     * Returns the planet object of the start planet
     *
     * @return	Planet	instance of Planet
     */
    public function getStartPlanet() {
    	if($this->startPlanet === null) $planet = Planet::getInstance($this->startPlanetID);
		else $planet = $this->startPlanet;

    	return $planet;
    }

    /**
     * Returns the planet object of the end planet
     *
     * @return	Planet	instance of Planet
     */
    public function getEndPlanet() {
    	if($this->endPlanet === null) $planet = Planet::getInstance($this->endPlanetID);
		else $planet = $this->endPlanet;

    	return $planet;
    }
    
    /**
     * Returns the stored user object
     */
   	public function getOwner() {
   		if($this->owner === null) $user = new LWUser($this->fleet_owner);
   		else $user = $this->owner;
   		
   		return $user;
   }
}
?>