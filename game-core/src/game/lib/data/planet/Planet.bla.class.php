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

/**
 * Holds all planet-specific functions
 */
class Planet extends DatabaseObject {
	protected $update = false;
	public $planetID = null;
	public static $planets = array();

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

		if(isset(self::$planets[$planetID])) $planet = self::$planets[$planetID];
		else if(isset(self::$planets[$row['id']])) $planet = self::$planets[$row['id']];
		else if($planetID === null) {
			$planetID = $row['id'];
			$className = $row['className'];
			if(!$row) $className = self::STANDARD_CLASS;

			require_once(LW_DIR.'lib/data/planet/'.$className.'.class.php');

			$planet = new $className($planetID, $row);
		} else {
			$sql = "SELECT *
		    		FROM ugml".LW_N."_planets
		    		WHERE id = ".$planetID;
		    $row = WCF::getDB()->getFirstRow($sql);

		    $className = $row['className'];
		   	if(!$row) $className = self::STANDARD_CLASS;

		    require_once(LW_DIR.'lib/data/planet/'.$className.'.class.php');
			$planet = new $className($planetID, $row);
		}
		// workaround for Orbit [::] Bug
		if(!is_numeric($planetID)) return $planet;

		// update resources and activity
		if(class_exists('LWEventHandler')) {
			if($planet->last_update != LWEventHandler::getTime()) {
				$planet->calculateResources(LWEventHandler::getTime());
				//if($updateLastActivity) $planet->updateLastActivity();
			}
		}

		self::$planets[$planetID] = $planet;
		return $planet;
	}

	/**
	 * Cleans the cache
	 */
	public static function clean() {
		self::$planets = array();
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
	 * Recalculates the production
	 *
	 * @return	bool	production changed
	 */
	public function calculateProduction($sqlUpdate = true) {
		global $game_config, $planetrow, $production, $resource;

		// fix for production bug in eventhandler environment
		if(class_exists('LWEventHandler')) {
			$planetrow = array_merge((array)$planetrow, (array)$this, $this->data);
		}

		$buildableBuildings = $this->getBuildableBuildings();

		$oldMetalPerHour = $this->metal_perhour;
		$oldCrystalPerHour = $this->crystal_perhour;
		$oldDeuteriumPerHour = $this->deuterium_perhour;
		$oldMaxEnergy = $this->energy_max;
		$oldUsedEnergy = $this->energy_used;

		$this->metal_perhour = $this->crystal_perhour = $this->deuterium_perhour = $this->energy_max = $this->energy_used = 0;

		if(!WCF::getUser()->urlaubs_modus) {
			foreach($buildableBuildings as $buildingID) {
				$metal = floor(eval($production[$buildingID]["formular"]["metal"]) * $game_config['resource_multiplier']);
				$crystal = floor(eval($production[$buildingID]["formular"]["crystal"]) * $game_config['resource_multiplier']);
				$deuterium = floor(eval($production[$buildingID]["formular"]["deuterium"]) * $game_config['resource_multiplier']);
				$energy = floor(eval($production[$buildingID]["formular"]["energy"]) * $game_config['resource_multiplier']);

				if($metal != 0) $this->metal_perhour += $metal;
				if($crystal != 0) $this->crystal_perhour += $crystal;
				if($deuterium != 0) $this->deuterium_perhour += $deuterium;

				if($energy > 0) $this->energy_max += $energy;
				else $this->energy_used -= $energy;
			}

			// satellites
			$this->energy_max += floor(eval($production[212]["formular"]["energy"])* $game_config['resource_multiplier']);

		}

		// update old ugamela vars
		$planetrow['metal_perhour'] = $this->metal_perhour;
		$planetrow['crystal_perhour'] = $this->crystal_perhour;
		$planetrow['deuterium_perhour'] = $this->deuterium_perhour;
		$planetrow['energy_used'] = $this->energy_used;
		$planetrow['energy_max'] = $this->energy_max;

		if($oldMetalPerHour == $this->metal_perhour && $oldCrystalPerHour == $this->crystal_perhour && $oldDeuteriumPerHour == $this->deuterium_perhour && $oldMaxEnergy == $this->energy_max && $oldUsedEnergy == $this->energy_used) return false;

		if(!$sqlUpdate || !is_numeric($this->planetID)) return true;

		$sql = "UPDATE ugml".LW_N."_planets
				SET metal_perhour = ".$this->metal_perhour.",
					crystal_perhour = ".$this->crystal_perhour.",
					deuterium_perhour = ".$this->deuterium_perhour.",
					energy_used = ".$this->energy_used.",
					energy_max = ".$this->energy_max."
				WHERE id = ".$this->planetID;
		WCF::getDB()->sendQuery($sql);

		return true;
	}

	/**
	 * Calculates the produced ressources since the last update
	 */
	public function calculateResources($time = null) {
		global $game_config, $planetrow;

		if($time === null) {
			if(class_exists('LWEventHandler')) $time = LWEventHandler::getTime();
			else $time = TIME_NOW;
		}

		$this->calculateProduction(false);

		$timeDiff = $time - $this->last_update;
		$hangarSQL = $this->checkHangar($timeDiff);

		// production level
		@$prodLevel = min(100, (($this->energy_max / $this->energy_used) * 100)) / 100;

		// metal
		$prodMetal = $timeDiff * $this->metal_perhour / 3600 * $prodLevel;

		if(($this->metal + $prodMetal) > $this->metal_max && $this->metal < $this->metal_max) $this->metal = $this->metal_max;
		else $this->metal += $prodMetal;

		// crystal
		$prodCrystal = $timeDiff * $this->crystal_perhour / 3600 * $prodLevel;

		if(($this->crystal + $prodCrystal) > $this->crystal_max && $this->crystal < $this->crystal_max) $this->crystal = $this->crystal_max;
		else $this->crystal += $prodCrystal;

		// deuterium
		$prodDeuterium = $timeDiff * $this->deuterium_perhour / 3600 * $prodLevel;

		if(($this->deuterium + $prodDeuterium) > $this->deuterium_max && $this->deuterium < $this->deuterium_max) $this->deuterium = $this->deuterium_max;
		else $this->deuterium += $prodDeuterium;

		// basic income
		if($this->planet_type == 1) {
			$this->metal += $timeDiff / 3600 * $game_config['metal_basic_income'];
			$this->crystal += $timeDiff / 3600 * $game_config['crystal_basic_income'];
			$this->deuterium += $timeDiff / 3600 * $game_config['deuterium_basic_income'];
		}

		if(is_numeric($this->planetID)) {
			$sql = "UPDATE ugml".LW_N."_planets
					SET metal_perhour = ".$this->metal_perhour.",
						crystal_perhour = ".$this->crystal_perhour.",
						deuterium_perhour = ".$this->deuterium_perhour.",
						metal = ".$this->metal.",
						crystal = ".$this->crystal.",
						deuterium = ".$this->deuterium.",
						energy_used = ".$this->energy_used.",
						last_update = ".$time.",
						".$hangarSQL."
						energy_max = ".$this->energy_max."
					WHERE id = ".$this->planetID;
			WCF::getDB()->sendQuery($sql);

			/*if(WCF::getUser()->userID == 361) {
				echo $sql;
			}*/
		}

		$this->last_update = $time;

		// update old ugamela vars
		$planetrow['metal'] = $this->metal;
		$planetrow['crystal'] = $this->crystal;
		$planetrow['deuterium'] = $this->deuterium;
		$planetrow['last_update'] = $this->last_update;
	}

	/**
	 * Checks the production of the hangar
	 *
	 * @return	string	sql update (set) string
	 */
	protected function checkHangar($timeDiff/* = null*/) {
		global $game_config, $planetrow, $pricelist, $resource;

		$hangarSQL = "";

		if($this->b_hangar_id != '') {

			//if($timeDiff === null) $timeDiff = TIME_NOW;

			$timeDiff += $this->b_hangar;
			$newShips = array();
			$remainingShips = "";
			$stopProduction = false;

			/**
			 * read list
			 */
			// get individual parts
			$parts = explode(';', substr($this->b_hangar_id, 0, -1));

			foreach($parts as $part) {
				// get shiptype and shipcount
				$shipDetails = explode(',', $part);
				$shipTypeID = $shipDetails[0];
				$shipCount = $shipDetails[1];

				// check whether ships can be produced
				if($stopProduction) {
					$remainingShips .= $shipTypeID.','.$shipCount.';';
					continue;
				}

				// check time
				$timePerShip = (($pricelist[$shipTypeID]['metal'] + $pricelist[$shipTypeID]['crystal']) / $game_config['game_speed']) * (1 / ($this->hangar + 1 )) * pow(0.5, $this->nano_factory) * 60 * 60;
				$maxShips = floor($timeDiff / $timePerShip);
				$builtShips = min($maxShips, $shipCount);

				// write to built ships
				if(isset($newShips[$shipTypeID])) $newShips[$shipTypeID] += $this->checkShipsCount($shipTypeID, $builtShips);
				else $newShips[$shipTypeID] = $this->checkShipsCount($shipTypeID, $builtShips);

				// subtract time
				$timeDiff -= ($builtShips * $timePerShip);

				if($shipCount > $builtShips) {
					// write remaining ships to new hangar string
					$remainingShips .= $shipTypeID.','.($shipCount - $builtShips).';';

					// update time that the ship is produced
					$this->b_hangar = $timeDiff;

					// set production flag (no ships can be produced any more)
					$stopProduction = true;
				}
			}

			if(WCF::getUser()->userID == 143) print_r($newShips);

			/**
			 * add ships to planet
			 */
			foreach($newShips as $shipTypeID => $shipCount) {
				// add to actual planet
				$this->{$resource[$shipTypeID]} += $shipCount;
				$planetrow[$resource[$shipTypeID]] += $shipCount;

				// build sql
				$hangarSQL .= $resource[$shipTypeID]." = ".$this->{$resource[$shipTypeID]}.", ";

			}

			/**
			 * handle remaining ships
			 */
			// build sql
			$hangarSQL .= "b_hangar_id = '".$remainingShips."',
					b_hangar = ".$this->b_hangar.", ";
			// update actual planet
			$this->b_hangar_id = $planetrow['b_hangar_id'] = $remainingShips;
			$planetrow['b_hangar'] = $this->b_hangar;

			if(WCF::getUser()->userID == 143) echo $hangarSQL;

			/*


			$this->b_hangar += $timeDiff;
			$b_hangar_id = explode(';',$this->b_hangar_id);

			$planet = array_merge($planetrow, (array)$this, $this->data);

			foreach($b_hangar_id as $n => $array){
				if($array!=''){
					$array = explode(',',$array);
					$buildArray[$n] = array($array[0], $array[1], get_building_time('', $planet, $array[0]));
				}
			}

			// create new list without produced ships and add produced ships to planet
			$this->b_hangar_id = '';
			$endtaillist = false;
			$noWhile = false;
			$summon = array();
			foreach($buildArray as $a => $b){
				while($this->b_hangar >= $b[2] && $b[1] != 0 && !$noWhile){
					$this->b_hangar -= $b[2];
					++$summon[$b[0]];
					++$this->{$resource[$b[0]]};
					++$planetrow[$resource[$b[0]]];
					$b[1]--;
				}
				$hangarSQL .= $resource[$b[0]]." = ".$this->{$resource[$b[0]]}.",";
				if($b[1] != 0) {
					$this->b_hangar_id .= $b[0].','.$b[1].';';
					if($this->b_hangar != 0) $noWhile = true;
				}
			}

			$hangarSQL .= " b_hangar = ".$this->b_hangar.",
					b_hangar_id = '".$this->b_hangar_id."', ";*/
		} else $this->b_hangar = 0;

		// update old ugamela vars
		//$planetrow['b_hangar'] = $this->b_hangar;
		//$planetrow['b_hangar_id'] = $this->b_hangar_id;

		return $hangarSQL;
	}

	/**
	 * Checks for missiles
	 *
	 * @param	int		shiptypeid
	 * @param	int		shipcount
	 * @return	int		corrected shipcount
	 */
	protected function checkShipsCount($shipTypeID, $shipCount) {
		if($shipTypeID != 502 && $shipTypeID != 503) return $shipCount;

		$maxSlots = $this->silo * 10;
		$usedSlots = $this->interceptor_misil + $this->interplanetary_misil * 2;

		if($shipTypeID == 502) {
			$maxShips = $maxSlots - $usedSlots;
			return min($maxShips, $shipCount);
		} else {
			$maxShips = ($maxSlots - $usedSlots) / 2;
			return min($maxShips, $shipCount);
		}

	}

	public function __toString() {
		return 'Planeten '.$this->name.' ['.$this->galaxy.':'.$this->system.':'.$this->planet.']';
	}

	public function valid() {
		return true;
	}


}
?>