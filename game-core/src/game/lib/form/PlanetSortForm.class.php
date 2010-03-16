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

require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(LW_DIR.'lib/data/CombatSimulator.class.php');
require_once(LW_DIR.'lib/data/fleet/PhantomFleet.class.php');
require_once(LW_DIR.'lib/util/LWUtil.class.php');

/**
 * External Page for planet sorting.
 * 
 * @author	Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class PlanetSortForm extends AbstractForm {
	const SIMULATIONS = 100;
	
	public $templateName = 'planetSort';
	
	protected $planetObjs = array();
	protected $planets = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->loadPlanetData();
		
		foreach($this->planetObjs as $planetObj) {
			if(isset($_POST['planet'.$planetObj->planetID])) {
				$sortID = intval($_POST['planet'.$planetObj->planetID]);
				
				while(isset($this->planets[$sortID])) ++$sortID;
				
				$this->planets[$sortID] = $planetObj->planetID;
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		foreach($this->planets as $sortID => $planetID) {
			$sql = "UPDATE ugml_planets
					SET sortID = ".$sortID."
					WHERE id = ".$planetID;
			WCF::getDB()->sendQuery($sql);
			
			Planet::getInstance($planetID)->sortID = $sortID;
		}
		
		$this->planetObjs = array();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->loadPlanetData();
	}
	
	/**
	 * Loads the planet data
	 */
	protected function loadPlanetData() {
		if(count($this->planetObjs)) return;
		
		$sql = "SELECT *
				FROM ugml_planets
				WHERE id_owner = ".WCF::getUser()->userID."
				ORDER BY sortID ASC";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$planetObj = Planet::getInstance(null, $row, false);
			$this->planetObjs[$planetObj->sortID.$planetObj->planetID] = $planetObj;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {		
		parent::assignVariables();
		
		WCF::getTPL()->assign('planets', $this->planetObjs);
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) message('Zutritt nicht erlaubt!');
		
		parent::show();
		echo_foot();
		
	}
}
?>