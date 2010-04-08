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

require_once(LW_DIR.'lib/data/fleet/NavalFormationAttackFleet.class.php');

/**
 * Creates a report for a foreign user of a planet.
 * 
 * @author		2007-2009 Biggerskimo
 * @copyright	Lost Worlds <http://lost-worlds.net>
 */
class EspionageFleet extends NavalFormationAttackFleet {
	protected $missionID = 6;
	
	protected $estandByFleets = array();	
	protected $blocks = 0;
	public $destroyChance = 0;
	public $ereport = '';
	
	public $fight = false;
	
	protected $resourceTypes = array('metal', 'crystal', 'deuterium', 'energy_max');
	protected $colCount = 2;
	
	const RESOURCES = 0x01;
	const FLEET = 0x02;
	const DEFENSE = 0x04;
	const BUILDINGS = 0x08;
	const RESEARCH = 0x10;

	/**
	 * @see AbstractFleetEventHandler::executeImpact()
	 */
    public function executeImpact() {
    	$this->initStandByFleets();
    	
    	$this->ereport .= '<a id="ereport'.$this->fleetID.'"></a>';
    	$this->create();
    	$this->ereport .= '<div class="ereportbottom" style="text-align: center;">';		
		$this->destroy();
		$this->ereport .= '<a style="display: block;" href="game/index.php?page=FleetStartShips&amp;targetPlanetID='.$this->targetPlanetID.'&amp;backlink=../messages.php%3Ffolder=4%23ereport'.$this->fleetID.'">'.WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.owner.attack').'</a>';
		$this->ereport .= '<a style="display: block;" href="game/index.php?form=Simulator&amp;planetID='.$this->targetPlanetID.'">'.WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.impact.owner.simulate').'</a>';
		$this->ereport .= '</div>';
		
    	$senderName = $this->parse(WCF::getLanguage()->get('wot.mission.mission6.sender.owner'));
		$subject = $this->parse(WCF::getLanguage()->get('wot.mission.mission6.impact.owner.subject'));
		MessageEditor::create($this->ownerID, $subject, $this->ereport, 0, $senderName, 4);
		
		$this->saveData($this->fight);
    }
    
	/**
	 * @see Mission::check()
	 */
	public static function check(FleetQueue $fleetQueue) {
		$probes = isset($fleetQueue->ships[210]);
		$foreignPlanet = ($fleetQueue->getTargetPlanet()->id_owner != WCF::getUser()->userID);
		
		if($probes && $foreignPlanet) {
			return true;
		}
		
		return false;
	}
    
    /**
     * Reads the data that should be displayed in the report and creates it.
     */
    protected function create() {
    	$this->blocks = $this->getDisplayBlocks();
    	
    	Spec::storeData($this->getTargetPlanet(), $this->getOfiara());
    	
    	// header
    	$this->ereport .= '<table>
	    			<tr>
		    			<td class="c" colspan="4">';
    	
    	// TODO: fix
    	if(!defined('TIMEZONE')) define('TIMEZONE', '1');
    	WCF::getUser()->timezone = TIMEZONE;
    	echo "utz:".WCF::getUser()->timezone."gtz:".TIMEZONE;
    	$vars = array(
    		'$targetPlanet' => $this->getTargetPlanet(),
    		'$time' => DateUtil::formatDate(WCF::getLanguage()->get('wot.global.timeFormat'), time())
    	);
		$this->ereport .= WCF::getLanguage()->get('wot.mission.mission'.$this->missionID.'.report.header', $vars);
		
		$this->ereport .= '</td>
					</tr>';
    	
    	// resources
    	if($this->blocks & self::RESOURCES) {
    		$array = array();
    		foreach($this->resourceTypes as $resourceType) {
    			$key = WCF::getLanguage()->get('wot.global.'.$resourceType);
    			$count = $this->getTargetPlanet()->$resoureType;
    			
    			$array[$key] = array(
    				'id' => $resourceType,
    				'name' => WCF::getLanguage()->get('wot.global.'.$resourceType),
    				'count' => intval(floor($this->getTargetPlanet()->$resourceType))
    			);
    		}
    		$this->ereport .= $this->createBlock($array);
    	}
    	
    	$this->addBlock(self::FLEET, 3, 'fleet');
    	$this->addBlock(self::DEFENSE, array(4, 51, 52), 'defense');
    	$this->addBlock(self::BUILDINGS, 1, 'buildings');
    	$this->addBlock(self::RESEARCH, 2, 'research');
    	$this->ereport .= '</table>';
    }
    
    /**
     * Reads the fleets that are in standby on this planet.
     */
    public function initStandByFleets() {
		$sql = "SELECT *,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
				FROM ugml_fleet
		    		LEFT JOIN ugml_fleet_spec
		    		ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
				WHERE ".$this->impactTime." BETWEEN impactTime AND wakeUpTime
					AND impactTime > 0
					AND targetPlanetID = ".$this->targetPlanetID."
		    	GROUP BY ugml_fleet.fleetID";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$fleet = Fleet::getInstance(null, $row);

			$this->estandByFleets[$fleet->fleetID] = $fleet;
		}
    }
    
    /**
     * Returns the spec objects with all ships (stand-by-fleets)
     * 
     * @param	mixed	spectypeid(s)
     * @return	array
     */
    protected function getRealBySpecType($specTypeID) {
    	Spec::storeData($this->getTargetPlanet(), $this->getOfiara);
    	
    	$specs = Spec::getBySpecType($specTypeID, false);
    	
    	// now add the standby fleets
    	foreach($this->estandByFleets as $fleetID => $fleet) {
    		Spec::storeData(false, false, $fleet);
    		
    		Spec::cleanObjCache();
    		$fleetSpecs = Spec::getBySpecType($specTypeID, false);
    		
    		$specs = Spec::add($specs, $fleetSpecs);
    	}
    	
    	return $specs;
    }
    
    /**
     * Reads the names and levels and adds the created block to the report
     * 
     * @param	int		identifier
     * @param	mixed	spectype(s)
     * @param	string	block name
     */
	protected function addBlock($identifier, $specTypeID, $blockName) {
		if(!($this->blocks & $identifier)) {
			return;
		}
		// header
    	$this->ereport .= '<tr>
				    			<td class="c" colspan="4">
				    				'.WCF::getLanguage()->get('wot.spec.'.$blockName).'
								</td>
							</tr>';
    	
    	// specs
    	$specs = $this->getRealBySpecType($specTypeID);
    	$array = array();
	
    	foreach($specs as $specID => $specObj) {    		
    		$array[] = array(
    			'id' => $specID,
    			'name' => WCF::getLanguage()->get('wot.spec.spec'.$specID),
    			'count' => $specObj->level
    		);
    	}
    	$this->ereport .= $this->createBlock($array);
	}
	
	/**
	 * Creates a valid block for the report.
	 * 
	 * @param	array	data
	 * @return	string
	 */
	protected function createBlock($data) {
		$block = '';
		$dataSets = array();
		
		foreach($data as $date) {
			$dataSets[] = '<td>'.$date['name'].'</td><td id="'.$date['id'].'">'.StringUtil::formatInteger($date['count']).'</td>';
		}
		$rows = array_chunk($dataSets, $this->colCount);
		
		foreach($rows as $row) {
			$block .= '<tr>';
			foreach($row as $dataSet) {
				$block .= $dataSet;
			}
			$block .= '</tr>';
		}
		
		return $block;
	}
	
	/**
     * Checks which data blocks should be displayed
     * 
     * @return	int
     */
    protected function getDisplayBlocks() {
    	$blocks = self::RESOURCES;
    	
    	$poweredDiff = pow(($this->getOwner()->spy_tech - $this->getOfiara()->spy_tech), 2) * (($this->getOwner()->spy_tech < $this->getOfiara()->spy_tech) ? -1 : 1) + $this->fleet[210];
    	
    	// fleet
    	if($poweredDiff > 2) {
    		$blocks |= self::FLEET;
    	}
    	else {
    		return $blocks;
    	}
    	
    	// defense
    	if($poweredDiff > 3) {
    		$blocks |= self::DEFENSE;
    	}
    	else {
    		return $blocks;
    	}
    	
    	// buildings
    	if($poweredDiff > 5) {
    		$blocks |= self::BUILDINGS;
    	}
    	else {
    		return $blocks;
    	}
    	
    	// researches
    	if($poweredDiff > 7) {
    		$blocks |= self::RESEARCH;
    	}
    	return $blocks;
    }
	
	/**
	 * @see	AbstractFleetEventHandler::getImpactOwnerMessageData()
	 */
	public function getImpactOwnerMessageData() {
		return null;
	}
	
	/**
	 * @see	AbstractFleetEventHandler::getImpactOfiaraMessageData()
	 */
	public function getImpactOfiaraMessageData() {
		// NavalFormationAttackFleet::getImpactOfiaraMessageData() return null,
		// so we must access AbstractFleetEventHandler::getImpactOfiaraMessageData()
		return AbstractFleetEventHandler::getImpactOfiaraMessageData();
	}
	
	/**
	 * @see	AbstractFleetEventHandler::getReturnMessageData()
	 */
	public function getReturnMessageData() {
		return null;
	}
	
	/**
	 * @see AbstractFleetEventHandler::initArrays()
	 */
	protected function initArrays() {
		parent::initArrays();
		
		$this->searches[] = '{$defenseChance}';
		$this->replaces[] = $this->destroyChance;
	}
	
	/**
	 * Destroys the espionage sondes if they get destroyed.
	 */
	protected function destroy() {
		$shipCount = 0;
		
		Spec::storeData($this->getTargetPlanet(), false);
		$specs = Spec::getBySpecType(3, false);
		
		foreach($specs as $specObj) {
			$shipCount += $specObj->level;
		}
		
		$this->destroyChance = pow($this->getOfiara()->spy_tech / $this->getOwner()->spy_tech, 5) * $this->fleet[210] * pow($shipCount, 0.2) / 10;
		$this->destroyChance = min($this->destroyChance, 100);
		
		$rand = rand(0, 99);

		if($this->destroyChance > $rand) {
			$this->fight = true;
			$this->simulate();
			$this->generateReport();
		}
		
		$this->ereport .= WCF::getLanguage()->get('wot.mission.mission6.impact.owner.defenseChance', array('$defenseChance' => StringUtil::formatNumeric(round($this->destroyChance))));
	}
	
	/**
	 * @see NavalFormationAttackFleet::saveData()
	 */
	protected function saveData($saveReport = false) {
		parent::saveData($saveReport);
		$this->saveDataEspionageReport();
	}
	
	/**
	 * @see NavalFormationAttackFleet::saveDataDebris()
	 */
	protected function saveDataDebris() {
		if($this->fight) {
			parent::saveDataDebris();
		}
	}
	
	/**
	 * Saves this espionage report in the database.
	 */
	protected function saveDataEspionageReport() {
		require_once(WCF_DIR.'lib/system/database/DatabaseException.class.php');
		$sql = "INSERT INTO ugml_espionage_report
				(userID, planetID,
				 `time`, report)
				VALUES
				(".$this->ownerID.", ".$this->targetPlanetID.",
				 ".time().", '".escapeString($this->ereport)."')";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Searches for reports of a user of a planet in the database.
	 * 
	 * @param	int		userid
	 * @param	int		planetid
	 * @param	int		limit
	 * @return	array	reports
	 */
	public static function searchReports($userID, $planetID, $limit = 10) {
		$reports = array();
		
		$sql = "SELECT *
				FROM ugml_espionage_report
				WHERE userID = ".$userID."
					AND planetID = ".$planetID."
				ORDER BY `time` DESC
				LIMIT ".$limit;
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$reports[$row['reportID']] = $row;
		}
		
		return $reports;
	}
}
?>