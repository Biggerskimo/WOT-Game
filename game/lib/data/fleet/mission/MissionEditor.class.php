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

require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(LW_DIR.'lib/data/planet/Planet.class.php');

/**
 * Holds all fleet-specific functions
 */
class Fleet extends DatabaseObject {
	protected static $fleets = array();
	const STANDARD_CLASS = 'Fleet';

	protected $fleet = array();
	protected $invalid = false;

	protected static $viewedFormations = array();
	
	/**
	 * Reads the needed class from the database and returns the fleet object
	 *
	 * @param	int		fleet id
	 * @param	array	fleet row
	 *
	 * @return	Fleet	planet
	 */
	public static function getInstance($fleetID = null, $row = null) {
		if($fleetID !== null) $fleetID = intval($fleetID);

		if(isset(self::$fleets[$fleetID])) $fleet = self::$fleets[$fleetID];
		else if(isset(self::$fleets[$row['id']])) $fleet = self::$fleets[$row['id']];
		else if($fleetID === null) {
			$fleetID = $row['id'];
			$className = $row['className'];
			if(!$row) $className = self::STANDARD_CLASS;

			require_once(LW_DIR.'lib/data/fleet/'.$className.'.class.php');

			$fleet = new $className($fleetID, $row);
		} else {
			$sql = "SELECT *
		    		FROM ugml".LW_N."_fleets
		    		WHERE fleet_id = ".$fleetID;
		    $row = WCF::getDB()->getFirstRow($sql);

		    $className = $row['className'];
		   	if(!$row) $className = self::STANDARD_CLASS;

		    require_once(LW_DIR.'lib/data/fleet/'.$className.'.class.php');
			$fleet = new $className($fleetID, $row);
		}
		
		self::$fleets[$fleetID] = $fleet;
		return $fleet;
	}

	/**
	 * Creates the fleet object
	 *
	 * @param	int		fleet id
	 * @param	array	database row
	 */
	public function __construct($fleetID = null, $row = null) {
		if($row === null) {
		    $sql = "SELECT *
					FROM ugml_fleets
					LEFT JOIN ugml_naval_formation_to_fleets
		    			ON ugml_fleets.fleet_id = ugml_naval_formation_to_fleets.fleetID
		    		LEFT JOIN ugml_naval_formation
		    			ON ugml_naval_formation_to_fleets.formationID = ugml_naval_formation.formationID
		    			WHERE fleet_id = ".$fleetID;
		    $row = WCF::getDB()->getFirstRow($sql);
		}

		parent::__construct($row);

		if($this->fleet_mess != 1) $this->time = $this->fleet_start_time;
		else $this->time = $this->fleet_end_time;

		/*$shipTypeDatas = explode(";", $this->fleet_array);
		foreach($shipTypeDatas as $shipTypeData) {
			$shipArray = explode(",", $shipTypeData);

			if(empty($shipArray[1]) || !$shipArray[1]) continue;

			$this->fleet[$shipArray[0]] = $shipArray[1];
		}*/
		
		$this->fleet = LWUtil::unserialize($this->fleet_array);
		
		$this->fleetID = $this->fleet_id;
	}

	/**
	 * Calls a fleet back
	 */
    public function recall() {
    }

	/**
	 * Returns the string which is printed in the tooltip
	 *
	 * @return	str		ships
	 */
	protected function getShipStr() {
		global $lang;

		includeLang('tech');

		$shipTypeDatas = explode(";", $this->fleet_array);

		$shipCount = 0;
		$shipStr = "";
		if((WCF::getUser()->spy_tech >= 2 || RequestHandler::getActiveRequest()->page == 'PhalanxPage') || $this->fleet_owner == WCF::getUser()->userID) {
			foreach($shipTypeDatas as $shipTypeData) {
				if($shipTypeData == '') continue;

				$shipArray = explode(",", $shipTypeData);

				if((WCF::getUser()->spy_tech >= 4 && WCF::getUser()->spy_tech < 8 && $shipArray[1] && RequestHandler::getActiveRequest()->page != 'PhalanxPage') && $this->fleet_owner != WCF::getUser()->userID) $shipStr .= ", &lt;br&gt;".$lang['tech'][$shipArray[0]];
				else/* if((WCF::getUser()->spy_tech >= 8 && $shipArray[1]) || ($shipArray[1] && RequestHandler::getActiveRequest()->page == 'PhalanxPage') || $this->fleet_owner == WCF::getUser()->userID)*/ $shipStr .= ", &lt;br&gt;".$lang['tech'][$shipArray[0]].": ".number_format($shipArray[1], 0, ',', '.');

				$shipCount += $shipArray[1];

			}

			$shipStr = "Anzahl der Schiffe: ".number_format($shipCount, 0, ',', '.').$shipStr;
		}

		return $shipStr;
	}

	/**
	 * Returns the css class name to view.
	 *
	 * @return	int		class name
	 */
	protected function getClassName($ownFleet = false) {
		if($ownFleet || $this->fleet_owner == WCF::getUser()->userID || RequestHandler::getActiveRequest()->page == 'PhalanxPage') {
			switch($this->fleet_mission) {
				case 1:
					return 'ownattack';
				case 3:
					return 'owntransport';
				case 4:
					return 'owndeploy';
				case 5:
					return 'owndestroy';
				case 6:
					return 'ownespionage';
				case 8:
					return 'ownharvest';
				case 9:
					return 'owncolony';
				case 11:
					return 'ownfederation';
				case 12:
					return 'ownhold';
				case 20:
					return 'ownmissile';
			}
		} else {
			switch($this->fleet_mission) {
				case 1:
					return 'attack';
				case 3:
					return 'transport';
				case 4:
					return 'deploy';
				case 5:
					return 'destroy';
				case 6:
					return 'espionage';
				case 8:
					return null;
				case 9:
					return 'colony';
				case 11:
					return 'federation';
				case 12:
					return 'hold';
				case 20:
					return 'missile';
			}
		}
	}

    /**
     * Views a fleet
     */
    public function view() {
		if($this->formationID && !$this->fleet_mess) {
			if(!isset(self::$viewedFormations[$this->formationID])) {
				self::$viewedFormations[$this->formationID] = true;
				return $this->viewNavalFormation();
			}
			return '';
		}

    	$className = $this->getClassName();
    	$containerName = 'fleet'.$this->fleet_id.$this->fleet_mess;

		if($this->fleet_mess != 1) $fpage .= '<tr class="flight"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';
		else $fpage .= '<tr class="return"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';

		$fpage .= '<script language="Javascript">var '.$containerName.' = new Time("'.$containerName.'", '.($this->time - TIME_NOW).', true);</script>';

		/*if(!$this->fleet_mess) $fpage .= '<span class="flight '.$className.'">';
		else $fpage .= '<span class="return '.$className.'">';

		if($this->fleet_owner == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Eine deiner ';
		else if(RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Eine fremde ';
		else $fpage .= 'Eine ';

		if(!empty($shipStr)) $fpage .= '<a href="#" onmouseover="this.T_WIDTH=150;return escape(\''.$shipStr.'\');" class="'.$className.'">';

		if($this->fleet_owner == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Flotten';
		else $fpage .= 'Flotte';

		if(!empty($shipStr)) $fpage .= '</a> ';

		$fpage .= ' vom '.$this->getStartPlanet();

		if(!$this->fleet_mess && $this->fleet_mission != 8) $fpage .= ' erreicht den ';
		else if($this->fleet_mission != 8) $fpage .= ' kehrt vom ';
		else if(!$this->fleet_mess) $fpage .= ' erreicht den ';
		else $fpage .= ' kehrt vom ';

		if($this->endPlanetID != 0) $fpage .= $this->getEndPlanet();
		else $fpage .= ' unbewohnten Planeten ['.$this->fleet_end_galaxy.':'.$this->fleet_end_system.':'.$this->fleet_end_planet.']';

		if(!$this->fleet_mess) $fpage .= '. Ihr Auftrag lautet: ';
		else $fpage .= ' zur&uuml;ck. Ihr Auftrag lautete: ';

		if(!empty($transport)) $fpage .= '<a href="#" onmouseover="this.T_WIDTH=150; return escape(\'Transport: '.$transport.'\');" class="'.$className.'">';
		$fpage .= LWCore::$missionTypes[$this->fleet_mission];
		if(!empty($transport)) $fpage .= '</a>';

		$fpage .= '</span>';*/
		$fpage .= $this->viewFleet();

		$fpage .= '</th></tr>';

		return $fpage;
    }

    /**
     * Views a naval formation
     */
    protected function viewNavalFormation() {
    	$sql = "SELECT userID
    			FROM ugml_naval_formation_to_users
    			WHERE formationID = ".$this->formationID;
    	$result = WCF::getDB()->sendQuery($sql);
    	$users = array();
    	while($row = WCF::getDB()->fetchArray($result)) {
    		$users[$row['userID']] = true;
    	}
    	if(isset($users[(WCF::getUser()->userID)])) $ownFleet = true;
    	else $ownFleet = false;


		$className = $this->getClassName($ownFleet);
    	$shipStr = $this->getShipStr();
    	$transport = $this->getRessources('string');
    	$containerName = 'fleet'.$this->fleet_id.(!$this->fleet_mess?'a':'b');

		if(!$this->fleet_mess) $fpage .= '<tr class="flight"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';
		else $fpage .= '<tr class="return"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';

		$fpage .= '<script language="Javascript">var '.$containerName.' = new Time("'.$containerName.'", '.($this->time - TIME_NOW).', true);</script>';

		/*if(!$this->fleet_mess) $fpage .= '<span class="flight '.$className.'">';
		else $fpage .= '<span class="return '.$className.'">';

		if($this->fleet_owner == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Eine deiner ';
		else if(RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Eine fremde ';
		else $fpage .= 'Eine ';

		if(!empty($shipStr)) $fpage .= '<a href="#" onmouseover="this.T_WIDTH=150;return escape(\''.$shipStr.'\');" class="'.$className.'">';

		if($this->fleet_owner == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Flotten';
		else $fpage .= 'Flotte';

		if(!empty($shipStr)) $fpage .= '</a> ';

		$fpage .= ' vom '.$this->getStartPlanet();

		if(!$this->fleet_mess && $this->fleet_mission != 8) $fpage .= ' erreicht den ';
		else $fpage .= ' kehrt vom ';

		$fpage .= $this->getEndPlanet();

		if(!$this->fleet_mess) $fpage .= '. Ihr Auftrag lautet: ';
		else $fpage .= ' zur&uuml;ck. Ihr Auftrag lautete: ';

		if(!empty($transport)) $fpage .= '<a href="#" onmouseover="this.T_WIDTH=150; return escape(\'Transport: '.$transport.'\');" class="'.$className.'">';
		$fpage .= LWCore::$missionTypes[$this->fleet_mission];
		if(!empty($transport)) $fpage .= '</a>';

		$fpage .= '</span>';*/
		$sql = "SELECT *
				FROM ugml_naval_formation_to_fleets
				WHERE formationID = ".$this->formationID;
		$result = WCF::getDB()->sendQuery($sql);

		$viewedFleet = false;
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleet = new Fleet($row['fleetID']);
			if(!$fleet->fleet_mess) {
				$text = $fleet->viewFleet($ownFleet);
				if(!empty($text)) {
					if($viewedFleet) $fpage .= '<br /><br />';
					else $viewedFleet = true;
					$fpage .= $text;
				}
			}
		}

		$fpage .= '</th></tr>';

		return $fpage;
    }

    /**
     * Views a fleet without container etc.
     */
    public function viewFleet($ownFleet = false) {
    	if(!count($this->fleet)) return '';

    	$className = $this->getClassName($ownFleet);
    	$shipStr = $this->getShipStr();
    	$transport = $this->getRessources('string');

    	if($this->fleet_mess != 1) $fpage = '<span class="flight '.$className.'">';
		else $fpage = '<span class="return '.$className.'">';

		if($this->fleet_owner == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Eine deiner ';
		else if(RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Eine fremde ';
		else $fpage .= 'Eine ';

		#if(!empty($shipStr)) $fpage .= '<a href="#" onmouseover="this.T_WIDTH=150;return escape(\''.$shipStr.'\');" class="'.$className.'">';
		if(!empty($shipStr)) $fpage .= '<a style="cursor:pointer" onmouseover="return overlib(\'<div id=jslayer>'.$shipStr.'</div>\', WIDTH, 150, MOUSEOFF, DELAY, 200);" onmouseout="return nd();">';		

		if($this->fleet_owner == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') $fpage .= 'Flotten';
		else $fpage .= 'Flotte';

		if(!empty($shipStr)) $fpage .= '</a> ';

		$fpage .= ' vom '.$this->getStartPlanet(false);

		if($this->fleet_mess == 2) $fpage .= ' beginnt den R&uuml;ckflug vom ';
		else if(!$this->fleet_mess/* && $this->fleet_mission != 8*/) $fpage .= ' erreicht den ';
		else $fpage .= ' kehrt vom ';

		if($this->endPlanetID != 0) $fpage .= $this->getEndPlanet(false);
		else $fpage .= ' unbewohnten Planeten ['.$this->fleet_end_galaxy.':'.$this->fleet_end_system.':'.$this->fleet_end_planet.']';
		
		if($this->fleet_mess != 1) $fpage .= '. Ihr Auftrag lautet: ';
		else $fpage .= ' zur&uuml;ck. Ihr Auftrag lautete: ';

		if(!empty($transport)) $fpage .= '<a style="cursor:pointer" onmouseover="return overlib(\'<div id=jslayer>Transport:'.$transport.'</div>\', WIDTH, 150, MOUSEOFF, DELAY, 200);this.T_WIDTH=150;" onmouseout="return nd();" class="'.$className.'">';
		
		$fpage .= LWCore::$missionTypes[$this->fleet_mission];
		if(!empty($transport)) $fpage .= '</a>';

		$fpage .= '</span>';

		return $fpage;
    }

    /**
     * Returns the planet object of the start planet
     *
     * @return	Planet	instance of Planet
     */
    public function getStartPlanet($updateLastActivity = true) {
    	$planet = Planet::getInstance($this->startPlanetID, null, $updateLastActivity);

    	return $planet;
    }

    /**
     * Returns the planet object of the end planet
     *
     * @return	Planet	instance of Planet
     */
    public function getEndPlanet($updateLastActivity = true) {
    	$planet = Planet::getInstance($this->endPlanetID, null, $updateLastActivity);

    	return $planet;
    }
    
    /**
     * Returns the user object of the owner
     */
   public function getOwner() {
   		$user = new LWUser($this->fleet_owner);
   
   		return $user;
   }

    /**
     * Returns the ressources which are transported
     *
     * @param	string	mode
     *
     * @return	int		ressources
     */
    public function getRessources($type = 'all') {
    	switch($type) {
    		case 'all':
    			$ressources = $this->fleet_resource_metal;
    			$ressources += $this->fleet_resource_crystal;
    			$ressources += $this->fleet_resource_deuterium;
    			return $ressources;
    		case 'array':
    			$ressources = array('metal' => $this->metal,
    					'crystal' => $this->crystal,
    					'deuterium' => $this->deuterium);
    			return $ressources;
    		case 'string':
    		case 'str':
    			$ressources = '';
				if((!$this->fleet_mess && $this->fleet_mission == 3) || $this->fleet_mission != 3) {
					if($this->fleet_resource_metal != 0) $ressources .= '&lt;br&gt;Metall: '.number_format($this->fleet_resource_metal, 0, ',', '.');
					if($this->fleet_resource_crystal != 0) $ressources .= '&lt;br&gt;Kristall: '.number_format($this->fleet_resource_crystal, 0, ',', '.');
					if($this->fleet_resource_deuterium != 0) $ressources .= '&lt;br&gt;Deuterium: '.number_format($this->fleet_resource_deuterium, 0, ',', '.');
				}
    			return $ressources;
    		case 'stringWBR':
    		case 'strWBR':
    			$ressources = '';
				$ressources .= 'Metall: '.number_format($this->fleet_resource_metal, 0, ',', '.');
				$ressources .= ', Kristall: '.number_format($this->fleet_resource_crystal, 0, ',', '.');
				$ressources .= ', Deuterium: '.number_format($this->fleet_resource_deuterium, 0, ',', '.');
    			return $ressources;
    		case 'metal':
    		case 'crystal':
    		case 'deuterium':
    			$ressources = $this->$type;
    			return $ressources;
    	}
    }

    public function getMissionName() {
    	switch($this->fleet_mission) {
			case 1:
				return 'Angreifen';
			case 3:
				return 'Transport';
			case 4:
				return 'Stationieren';
			case 5:
				return 'Transport';
			case 6:
				return 'Spionage';
			case 8:
				return 'Abbau';
			case 9:
				return 'Kolonisieren';
			case 11:
				return 'Verbandsangriff';
			case 20:
				return 'Interplanetarraketen-Angriff';
		}
    }
}
?>