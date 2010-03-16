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
require_once(LW_DIR.'lib/data/fleet/log/LoggableFleet.class.php');
require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');
require_once(LW_DIR.'lib/data/fleet/FleetEditor.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOventFleet.class.php');
require_once(LW_DIR.'lib/data/planet/Planet.class.php');
require_once(LW_DIR.'lib/system/event/WOTEventSingleton.class.php');

/**
 * Holds all fleet-specific functions.
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class Fleet extends DatabaseObject implements LoggableFleet, WOTEventSingleton {
	public static $fleets = array();
	const STANDARD_CLASS = 'Fleet';

	protected $invalid = false;
	public $ships = array();

	protected static $viewedFormations = array();
	
	public $fleet = array();
	
	const OWNER = 0x01;
	const OFIARA = 0x02;
	
	protected static $containerNo = 0;
	
	/**
	 * Reads the needed class from the database and returns the fleet object
	 *
	 * @param	int		fleet id
	 * @param	array	fleet row
	 *
	 * @return	Fleet	planet
	 */
	public static function getInstance($fleetID/* = null */, $row = null) {
		if($fleetID !== null) {
			$fleetID = intval($fleetID);
		}

		FleetQueue::readCache();
		
		if(isset(self::$fleets[$fleetID])) {
			$fleet = self::$fleets[$fleetID];
		}
		else if(isset(self::$fleets[$row['fleetID']])) {
			$fleetID = $row['fleetID'];
			$fleet = self::$fleets[$row['fleetID']];
		}
		else if($fleetID === null) {
			$fleetID = $row['fleetID'];
			$classPath = FleetQueue::$cache[$row['missionID']]['classPath'];
			$className = StringUtil::getClassName($classPath);
			if(!$row) {
		   		$className = self::STANDARD_CLASS;
		   		$classPath = 'lib/data/fleet/'.self::STANDARD_CLASS.'.class.php';
		   	}

			require_once(LW_DIR.'lib/data/fleet/'.$className.'.class.php');

			$fleet = new $className($fleetID, $row);
		}
		else {
			$sql = "SELECT ugml_fleet.*,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
					FROM ugml_fleet
		    		LEFT JOIN ugml_fleet_spec
		    			ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
		    		WHERE ugml_fleet.fleetID = ".$fleetID."
		    		GROUP BY ugml_fleet.fleetID";
		    $row = WCF::getDB()->getFirstRow($sql);
		    
			$classPath = FleetQueue::$cache[$row['missionID']]['classPath'];
			$className = StringUtil::getClassName($classPath);
		   	if(!$row) {
		   		$className = self::STANDARD_CLASS;
		   		$classPath = 'lib/data/fleet/'.self::STANDARD_CLASS.'.class.php';
		   	}
		   	
		    require_once(LW_DIR.$classPath);
			$fleet = new $className($fleetID, $row);
		}
		
		self::$fleets[$fleetID] = $fleet;
		return $fleet;
	}
	
	/**
	 * Cleans the cache.
	 */
	public static function clean() {
		self::$fleets = array();
	}

	/**
	 * Creates the fleet object
	 *
	 * @param	int		fleet id
	 * @param	array	database row
	 */
	public function __construct($fleetID = null, $row = null) {
		if($row === null) {
		    $sql = "SELECT ugml_fleet.*,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
					FROM ugml_fleet
		    		LEFT JOIN ugml_fleet_spec
		    			ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
					WHERE ugml_fleet.fleetID = ".$fleetID."
		    		GROUP BY ugml_fleet.fleetID";
		    $row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
				
		$this->fleet = Spec::strToArray($row['fleet']);
	}
	
	/**
	 * @param	string	$name
	 * @param	mixed	$value
	 */
	public function __set($name, $value) {
		$this->data[$name] = $value;
	}
	
	/**
	 * Returns fleets by user id.
	 * 
	 * @param	int		user id
	 * @param	int		owner/ofiara
	 * @return	array	fleets
	 */
	public static function getByUserID($userID, $flag = self::OWNER) {
		$sql = "SELECT *
				FROM ugml_fleet
				WHERE ".
				($flag & self::OWNER ? "ownerID = ".$userID : "").
				($flag & self::OFIARA ? ($flag & self::OWNER ? " AND " : "")."ofiaraID = ".$userID : "");
		
		$sql = "SELECT ugml_fleet.*,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
					FROM ugml_fleet
		    		LEFT JOIN ugml_fleet_spec
		    			ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
					WHERE ".
						($flag & self::OWNER ? "ugml_fleet.ownerID = ".$userID : "").
						($flag & self::OFIARA ? ($flag & self::OWNER ? " AND " : "")."ugml_fleet.ofiaraID = ".$userID : "").
		    		" GROUP BY ugml_fleet.fleetID";						
		$result = WCF::getDB()->sendQuery($sql);
		
		$fleets = array();
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleets[$row['fleetID']] = Fleet::getInstance(null, $row);
		}
		
		return $fleets;
	}
	/**
	 * Returns fleets by planet id.
	 * 
	 * @param	int		planet id
	 * @param	int		owner/ofiara
	 * @return	array	fleets
	 */
	public static function getByPlanetID($planetID, $flag = self::OWNER) {
		$sql = "SELECT *
				FROM ugml_fleet
				WHERE ".
				($flag & self::OWNER ? "startPlanetID = ".$planetID : "").
				($flag & self::OFIARA ? ($flag & self::OWNER ? " OR " : "")."targetPlanetID = ".$planetID : "");
		
		$sql = "SELECT ugml_fleet.*,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
					FROM ugml_fleet
		    		LEFT JOIN ugml_fleet_spec
		    			ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
					WHERE ".
						($flag & self::OWNER ? "ugml_fleet.startPlanetID = ".$planetID : "").
						($flag & self::OFIARA ? ($flag & self::OWNER ? " OR " : "")."ugml_fleet.targetPlanetID = ".$planetID : "").
		    		" GROUP BY ugml_fleet.fleetID";						
		$result = WCF::getDB()->sendQuery($sql);
		
		$fleets = array();
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleets[$row['fleetID']] = Fleet::getInstance(null, $row);
		}
		
		return $fleets;
	}
	
	/**
	 * Returns the editor of this fleet.
	 * 
	 * @return	FleetEditor
	 */
	public function getEditor() {
		return new FleetEditor($this->fleetID);
	}
	
	/**
	 * Returns false if fleet can not be canceled.
	 * Returns the time until new return event, if the fleet would be cancelled now.
	 * 
	 * @return	mixed
	 */
	public function getCancelDuration() {
		if(isset($this->data['displayTime']) && $this->displayTime == $this->returnTime) {
			return false;
		}
		if($this->impactTime < microtime(true)) {
			return false;
		}
		
		return (microtime(true) - $this->startTime);
	}
	
	/**
	 * Returns a set with displayable fleets.
	 * 
	 * @param	bool	from outsite (phalanx)
	 * @return	array	timesorted
	 */
	public function getFleetSet($planetID = false) {
		$fleetArray = array();
	
		// impact
		$isOutbound = ($this->getCancelDuration() !== false);
		$timeOK = (!$this->wakeUpTime || $this->impactTime > TIME_NOW);
		$requestOK = (!$planetID || $this->targetPlanetID == $planetID);
		if($isOutbound && $timeOK && $requestOK) {
			$fleet = clone $this;
			
			$fleet->displayTime = $this->impactTime;
	
			$fleetArray[$this->impactTime.$this->fleetID] = $fleet;
		}
		
		// return
		$requestOwnerOK = ($this->ownerID == WCF::getUser()->userID && !$planetID);
		$requestPhalanxOK = ($planetID == $this->startPlanetID);
		if($requestOwnerOK || $requestPhalanxOK) {
			$fleet = clone $this;
			$fleet->displayTime = $this->returnTime;
			
			$fleetArray[$this->returnTime.$this->fleetID] = $fleet;
		}
		
		return $fleetArray;
	}

	/**
	 * Returns the string which is printed in the tooltip
	 *
	 * @return	str		ships
	 */
	protected function getShipStr() {
		$shipCount = 0;
		$shipStr = "";
		if((WCF::getUser()->spy_tech >= 2 || RequestHandler::getActiveRequest()->page == 'PhalanxPage') || $this->ownerID == WCF::getUser()->userID) {
			foreach($this->fleet as $specID => $count) {
				$shipArray = explode(",", $shipTypeData);

				if((WCF::getUser()->spy_tech >= 4 && WCF::getUser()->spy_tech < 8 && $shipArray[1] && RequestHandler::getActiveRequest()->page != 'PhalanxPage') && $this->fleet_owner != WCF::getUser()->userID) {
					$shipStr .= ", &lt;br&gt;".WCF::getLanguage()->get('wot.spec.spec'.$specID);
				}
				else {
					$shipStr .= ", &lt;br&gt;".WCF::getLanguage()->get('wot.spec.spec'.$specID).": ".number_format($count, 0, ',', '.');
				}

				$shipCount += $count;
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
	public function getClassName($ownFleet = false) {
		if($ownFleet || $this->ownerID == WCF::getUser()->userID || RequestHandler::getActiveRequest()->page == 'PhalanxPage') {
			switch($this->data['missionID']) {
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
			switch($this->data['missionID']) {
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
		if($this->formationID && $this->getCancelDuration()) {
			if(!isset(self::$viewedFormations[$this->formationID])) {
				self::$viewedFormations[$this->formationID] = true;
				return $this->viewNavalFormation();
			}
			return '';
		}

    	$className = $this->getClassName();
    	$containerName = 'fleet'.$this->fleetID.self::$containerNo++;

    	if($this->getCancelDuration()) {
			$fpage .= '<tr class="flight"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';
		}
		else {
			$fpage .= '<tr class="return"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';
		}

		$fpage .= '<script language="Javascript">var '.$containerName.' = new Time("'.$containerName.'", '.($this->displayTime - TIME_NOW).', true);</script>';

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
    	if(isset($users[(WCF::getUser()->userID)])) {
    		$ownFleet = true;
    	}
    	else {
    		$ownFleet = false;
    	}


		$className = $this->getClassName($ownFleet);
    	$shipStr = $this->getShipStr();
    	$transport = $this->getRessources('string');
    	$containerName = 'fleet'.$this->fleetID.self::$containerNo++;

		if($this->getCancelDuration()) {
			$fpage .= '<tr class="flight"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';
		}
		else {
			$fpage .= '<tr class="return"><th><div id="'.$containerName.'" class="z"></div></th><th colspan="3">';
		}

		$fpage .= '<script language="Javascript">var '.$containerName.' = new Time("'.$containerName.'", '.($this->displayTime - TIME_NOW).', true);</script>';

		$sql = "SELECT ugml_fleet.*,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
				FROM ugml_fleet
    			LEFT JOIN ugml_fleet_spec
    				ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
				WHERE formationID = ".$this->formationID."
				GROUP BY ugml_fleet.fleetID";
		$result = WCF::getDB()->sendQuery($sql);

		$viewedFleet = false;
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleet = Fleet::getInstance(null, $row);
			if($fleet->getCancelDuration()) {
				$text = $fleet->viewFleet($ownFleet);
				if(!empty($text)) {
					if($viewedFleet) {
						$fpage .= '<br /><br />';
					}
					else {
						$viewedFleet = true;
					}
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

    	if($this->getCancelDuration()) {
    		$fpage = '<span class="flight '.$className.'">';
    	}
		else {
			$fpage = '<span class="return '.$className.'">';
		}

		if($this->ownerID == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') {
			$fpage .= 'Eine deiner ';
		}
		else if(RequestHandler::getActiveRequest()->page != 'PhalanxPage') {
			$fpage .= 'Eine fremde ';
		}
		else $fpage .= 'Eine ';

		if(!empty($shipStr)) {
			$fpage .= '<a style="cursor:pointer" onmouseover="return overlib(\'<div id=jslayer>'.$shipStr.'</div>\', WIDTH, 150, MOUSEOFF, DELAY, 200);" onmouseout="return nd();">';		
		}

		if($this->ownerID == WCF::getUser()->userID && RequestHandler::getActiveRequest()->page != 'PhalanxPage') {
			$fpage .= 'Flotten';
		}
		else {
			$fpage .= 'Flotte';
		}

		if(!empty($shipStr)) {
			$fpage .= '</a> ';
		}

		$fpage .= ' vom '.$this->getStartPlanet(false);

		if($this->wakeUpTime && $this->wakeUpTime == $this->displayTime) {
			$fpage .= ' beginnt den R&uuml;ckflug vom ';
		}
		else if($this->getCancelDuration()) {
			$fpage .= ' erreicht den ';
		}
		else {
			$fpage .= ' kehrt vom ';
		}

		if($this->targetPlanetID != 0) {
			$fpage .= $this->getTargetPlanet(false);
		}
		else {
			$fpage .= ' unbewohnten Planeten ['.$this->galaxy.':'.$this->system.':'.$this->planet.']';
		}
		
		if($this->getCancelDuration()) {
			$fpage .= '. Ihr Auftrag lautet: ';
		}
		else {
			$fpage .= ' zur&uuml;ck. Ihr Auftrag lautete: ';
		}

		if(!empty($transport)) {
			$fpage .= '<a style="cursor:pointer" onmouseover="return overlib(\'<div id=jslayer>Transport:'.$transport.'</div>\', WIDTH, 150, MOUSEOFF, DELAY, 200);this.T_WIDTH=150;" onmouseout="return nd();" class="'.$className.'">';
		}
		
		$fpage .= WCF::getLanguage()->get('wot.mission.mission'.$this->data['missionID']);
		
		if(!empty($transport)) {
			$fpage .= '</a>';
		}

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
     * Returns the planet object of the target planet
     *
     * @return	Planet	instance of Planet
     */
    public function getTargetPlanet($updateLastActivity = true) {
    	$planet = Planet::getInstance($this->targetPlanetID, null, $updateLastActivity);

    	return $planet;
    }
    
    /**
     * Returns the user object of the owner
     */
   public function getOwner() {
   		$user = new LWUser($this->ownerID);
   
   		return $user;
   }
    
    /**
     * Returns the user object of the ofiara
     */
   public function getOfiara() {
   		$user = new LWUser($this->ofiaraID);
   
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
    			$ressources = $this->metal;
    			$ressources += $this->crystal;
    			$ressources += $this->deuterium;
    			return $ressources;
    		case 'array':
    			$ressources = array('metal' => $this->metal,
    					'crystal' => $this->crystal,
    					'deuterium' => $this->deuterium);
    			return $ressources;
    		case 'string':
    		case 'str':
    			$ressources = '';
				if(($this->getCancelDuration() && $this->data['missionID'] == 3) || $this->data['missionID'] != 3) {
					if($this->metal != 0) {
						$ressources .= '&lt;br&gt;Metall: '.number_format($this->metal, 0, ',', '.');
					}
					if($this->crystal != 0) {
						$ressources .= '&lt;br&gt;Kristall: '.number_format($this->crystal, 0, ',', '.');
					}
					if($this->deuterium != 0) {
						$ressources .= '&lt;br&gt;Deuterium: '.number_format($this->deuterium, 0, ',', '.');
					}
				}
    			return $ressources;
    		case 'stringWBR':
    		case 'strWBR':
    			$ressources = '';
				$ressources .= 'Metall: '.number_format($this->metal, 0, ',', '.');
				$ressources .= ', Kristall: '.number_format($this->crystal, 0, ',', '.');
				$ressources .= ', Deuterium: '.number_format($this->deuterium, 0, ',', '.');
    			return $ressources;
    		case 'metal':
    		case 'crystal':
    		case 'deuterium':
    			$ressources = $this->$type;
    			return $ressources;
    	}
    }
    
    /**
     * Returns a string with the ship information.
     *
     * @param	bool	viewtype
     * @return 	string
     */
    public function getShips($type) {
    	switch($type) {
    		case 'stringWBR':
    		case 'strWBR':
    			$string = '';
    			foreach($this->fleet as $specID => $shipCount) {
    				if(!empty($string)) {
    					$string .= ', ';
    				}
    				$string .= WCF::getLanguage()->get('wot.spec.spec'.$specID);
    				$string .= ': '.$shipCount;
    			}
    			return '('.$string.')';
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
    
    /**
     * Returns the name of the current passage (flight, return, standBy, ...)
     */
    public function getPassageName($time = TIME_NOW)
    {
    	if(!is_numeric($time))
    	{
    		$time = $this->{$time.'Time'};
    	}
    	
    	if($this->impactTime < $time)
    	{
    		return "return";
    	}
    	return "flight";
    }
    
    /**
     * @see LoggableFleet::getData()
     */
    public function getData() {
    	return $this->data;
    }
    
    /**
     * @see LoggableFleet::addData()
     */
    public function addData($array) {
    	$this->data += $array;
    }
    
    /**
     * @see LoggableFleet::getFleetArray()
     */
    public function getFleetArray() {
    	return $this->fleet;
    }
}
?>