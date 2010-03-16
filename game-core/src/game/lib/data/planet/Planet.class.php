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

// wcf
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

require_once(LW_DIR.'lib/data/planet/PlanetEditor.class.php');
require_once(LW_DIR.'lib/data/planet/PlanetException.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');

/**
 * Holds all planet-specific functions
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2010 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.planet
 */
class Planet extends DatabaseObject {
	protected $update = false;
	public $planetID = null;
	public static $planets = array();
	
	protected $user = null;

	const STANDARD_CLASS = 'Debris';

	/**
	 * Reads the needed class from the database and returns the planet object
	 *
	 * @param	int		planet id
	 * @param	array	planet row
	 * @param	bool	update last activity
	 *
	 * @return	Planet	planet
	 */
	public static function getInstance($planetID = null, $row = null, $updateLastActivity = true) {
		if($planetID !== null) $planetID = intval($planetID);

		$opened = false;
		if(isset(self::$planets[$planetID])) {
			$planet = self::$planets[$planetID];
		}
		else if(isset(self::$planets[$row['id']])) {
			$planet = self::$planets[$row['id']];
		}
		else if($planetID === null) {
			WCF::getDB()->sendQuery("START TRANSACTION");
			$opened = true;
			
			$planetID = $row['id'];
			$sql = "SELECT *
					FROM ugml".LW_N."_planets
					WHERE id = ".intval($planetID)." LIMIT 1 OFFSET 0 FOR UPDATE";
			$row = WCF::getDB()->getFirstRow($sql);
			
			$className = $row['className'];
			if(!$row) $className = self::STANDARD_CLASS;
			
			require_once(LW_DIR.'lib/data/planet/'.$className.'.class.php');
			
			$planet = new $className($planetID, $row);
		} else {
			WCF::getDB()->sendQuery("START TRANSACTION");
			$opened = true;
		
			$sql = "SELECT *
		    		FROM ugml".LW_N."_planets
		    		WHERE id = ".intval($planetID)." LIMIT 1 OFFSET 0 FOR UPDATE";
		    $row = WCF::getDB()->getFirstRow($sql);

		    $className = $row['className'];
		   	if(!$row) $className = self::STANDARD_CLASS;

		    require_once(LW_DIR.'lib/data/planet/'.$className.'.class.php');
			$planet = new $className($planetID, $row);
		}
		// workaround for Orbit [::] Bug
		if(!is_numeric($planetID)) return $planet;

		self::$planets[$planetID] = $planet;
		
		// update resources and activity
		if($updateLastActivity) {
			if(class_exists('WOTEventExecuteDaemon')) {
				if($planet->last_update != time()) {
					$planet->calculateResources(time());
				}
			} else {
				WOTUtil::callAfterInit(array($planet, 'initDoneCallback'));
			}
		}
		
		if($opened) {
			WCF::getDB()->sendQuery("COMMIT");
		}
		
		return $planet;
	}
	
	/**
	 * Sets $planetrow
	 */
	public function setPlanetRow() {
		global $planetrow;
		
		$planetrow = $this->data;
	}

	/**
	 * Cleans the cache
	 */
	public static function clean() {
		self::$planets = array();
	}
	
	/**
	 * Returns the editor of this planet.
	 * 
	 * @return	PlanetEditor
	 */
	public function getEditor() {
		return new PlanetEditor($this->planetID);
	}
	

	/**
	 * Updates the activity timestamp
	 */
	public function updateLastActivity() {
		if(!class_exists('LWEventHandler') || !is_numeric($this->planetID)) return;
		
		$sql = "UPDATE ugml_planets
				SET last_update = ".LWEventHandler::getTime()."
				WHERE id = ".$this->planetID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}

	/**
	 * Returns the buildings which can be built on this planet
	 *
	 * @return	array	buildings
	 */
	public function getBuildableBuildings() {
		return array();
	}

	/**
	 * Returns if there is free place on the planet
	 *
	 * @return	bool	free fields
	 */
	public function checkFields() {
		$fields = $this->getMaxFields();
		$usedFields = $this->getUsedFields();
		
		if($usedFields < $fields) return true;
		else return false;
	}

	/**
	 * Returns the fields which can be built
	 *
	 * @return	int		fields
	 */
	public function getMaxFields() {
		$fields = floor(pow(($this->diameter / 1000), 2));
		
		$fields += ($this->terraformer * 5);
		
		return $fields;
	}
	
	/**
	 * Returns the used fields
	 */
	public function getUsedFields() {
		global $resource;
		
		$buildableBuildings = $this->getBuildableBuildings();
		
		$usedFields = 0;
		foreach($buildableBuildings as $buildingID) $usedFields += $this->{$resource[$buildingID]};
		
		return $usedFields;
	}
	
	
	/**
	 * Calculates the produced ressources since the last update
	 */
	public function calculateResources($time = null) {
		$this->getProductionHandler()->produce();
	}
	
	/**
	 * Callback which is called after the initialization.
	 *
	 * @param	boolean		initialization already done
	 */
	public function initDoneCallback($initAlreadyDone) {
		if(!$initAlreadyDone) {
			WCF::getDB()->sendQuery("START TRANSACTION");
			
			$sql = "SELECT *
					FROM ugml_planets
					WHERE id = ".intval($this->planetID)." LIMIT 1 OFFSET 0 FOR UPDATE";
			$row = WCF::getDB()->getFirstRow($sql);
			
			$this->handleData($row);
			
			$this->calculateResources();
			
			WCF::getDB()->sendQuery("COMMIT");
		} else {
			$this->calculateResources();
		}
	}

	public function __toString() {
		if($this->id_owner == WCF::getUser()->userID) {
			return '<a href="overview.php?cp='.$this->planetID.'" target="Mainframe">Planeten '.$this->name.'</a> <a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
		} else {
			return 'Planeten '.$this->name.' <a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
		}
	}
	
	/**
	 * Returns the linked coordinates.
	 * 
	 * @return	string	html code
	 */
	public function getLinkedCoordinates($nameB = false, $prefixB = false) {
		$linkBefore = $prefix = $name = $linkAfter = '';
		if($this->id_owner == WCF::getUser()->userID) {
			$linkBefore = '<a href="overview.php?cp='.$this->planetID.'" target="Mainframe">';
			$linkAfter = '</a>';
		}
		if($nameB) {
			$name = $this->name.' ';
		}
		if($prefixB) {
			$prefix = 'Planeten ';
		}
		
		return $linkBefore.$prefix.$name.$linkAfter.'<a href="galaxy.php?g='.$this->galaxy.'&amp;s='.$this->system.'" target="Mainframe">['.$this->galaxy.':'.$this->system.':'.$this->planet.']</a>';
	}
	
	/**
	 * Returns the upper limit by the noob protection
	 * 
	 * @return	float	limit
	 */
	public function noobProtectionLimit() {
		// LIMIT = POINTS * ((POINTS / 2500) ^ 3 + 2)
		if(intval($this->id_owner)) {
			$owner = new LWUser($this->id_owner);
			
			return $owner->points * (pow($owner->points / 2500, 3) + 2);
		}
		
		return 0;
	}
	
	/**
	 * Returns all fleetcontacts with this planet.
	 * 
	 * @return	array
	 */
	public function getFleets() {
		$sql = "SELECT ugml_fleets.*,
					ugml_naval_formation.formationID
				FROM ugml_fleets
				LEFT JOIN ugml_naval_formation_to_fleets
					ON ugml_fleets.fleet_id = ugml_naval_formation_to_fleets.fleetID
				LEFT JOIN ugml_naval_formation
					ON ugml_naval_formation_to_fleets.formationID = ugml_naval_formation.formationID
				WHERE startPlanetID = ".$this->planetID."
					OR ugml_fleets.endPlanetID = ".$this->planetID;
		$result = WCF::getDB()->sendQuery($sql);
		
		$fleets = array();
		require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleet = Fleet::getInstance(null, $row);
			
			if($fleet->fleet_mess == 0 || $fleet->fleet_mess == 2) {
				$fleets[$fleet->fleet_start_time.$fleet->fleetID] = $fleet;
			} else {
				$fleets[$fleet->fleet_end_time.$fleet->fleetID] = $fleet;
			}
		}
		
		ksort($fleets);
		
		return $fleets;
	}
	
	/**
	 * Returns all fleetcontacts with this planet. For each fleet there can be
	 *  more events (up to three events)
	 * 
	 * @return	array
	 */
	public function getEvents() {
		$fleets = $this->getFleets();
		$events = array();

		foreach($fleets as $fleet) {
			// insert first fleet
			if($fleet->fleet_mess == 1) $time = 'end';
			else $time = 'start';
			$timeName = 'fleet_'.$time.'_time';
			
			if($fleet->fleet_owner == $user['id'] || $time == 'start') $events[$fleet->$timeName.$fleet->fleet_id] = $fleet;
		
			// insert middle (third) fleet in stand by mission with fleet mess 2
			if($fleet->fleet_mission == 12 && $time == 'start') {
				if($fleet->fleet_mess == 0) {
					$fleet->fleet_start_time += $fleet->standByTime;
					$fleet->fleet_mess = 2;
				}
		
				$events[$fleet->fleet_start_time.$fleet->fleet_id] = $fleet;
			}
		
			// insert last fleet
			if($fleet->fleet_owner == $user['id'] && $time == 'start') {
				$fleet->fleet_mess = 1;
				$events[$fleet->fleet_end_time.$fleet->fleet_id] = $fleet;
			}
		}
		
		ksort($events);
		
		return $events;
	}

	/**
	 * Returns the owner of this planet.
	 */
	public function getOwner() {
		if($this->user === null) {
			$this->user = new LWUser($this->id_owner);
		}
		
		return $this->user;
	}
	
	/**
	 * Returns all planets of a user.
	 * 
	 * @param	int		user id
	 * @param	int		planet kind (optional)
	 * @param	boolean	ordered
	 * @return	array	Planets
	 */
	public static function getByUserID($userID, $planetKind = null, $ordered = false) {
		$planets = array();
		
		$sql = "SELECT *
				FROM ugml_planets
				WHERE id_owner = ".$userID
				.(($planetKind !== null) ? " AND planetKind = ".$planetKind : "")
				.(($ordered) ? " ORDER BY sortID" : "");
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$planets[$row['id']] = Planet::getInstance(null, $row);
		}
		
		return $planets;
	}
	
	/**
	 * Returns the planet production handler object for this planet.
	 * 
	 * @return	PlanetProductionHandler
	 */
	public function getProductionHandler() {
		if($this->productionHandlerObj === null) {			
			require_once(LW_DIR.'lib/data/planet/production/PlanetProductionHandler.class.php');		
			$this->productionHandlerObj = new PlanetProductionHandler($this->planetID);
		}
		
		return $this->productionHandlerObj;
	}
	
	/**
	 * Returns the level by a given spec id.
	 * 
	 * @param	int	specID
	 * @param	int	level
	 */
	public function getLevel($specID) {
		$colName = Spec::getSpecVar($specID, 'colName');
		
		return $this->$colName;
	}
}
?>