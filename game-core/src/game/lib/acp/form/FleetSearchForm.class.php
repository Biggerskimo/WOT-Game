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

require_once(WCF_DIR.'lib/acp/form/DynamicOptionListForm.class.php');
require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');

/**
 * Searches for fleets in the archive.
 * 
 * @author		Biggerskimo
 * @copyright	2007-2008 Lost Worlds <http://lost-worlds.net>
 */
class FleetSearchForm extends DynamicOptionListForm {
	public $templateName = 'fleetSearch';
	public $cacheName = 'fleet-option-';
	
	public $fleetID = 0;
	
	public $options = array();
	
	public $matches = array();	

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_REQUEST['fleetID'])) {
			$this->fleetID = LWUtil::checkInt($_REQUEST['fleetID']);
		}
		
		if(isset($_GET['doSearch'])) {		
			try {
				$this->validate();
				// no errors
				$this->save();
			}
			catch (UserInputException $e) {
				$this->errorField = $e->getField();
				$this->errorType = $e->getType();
			}
		}
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		foreach ($this->activeOptions as $name => $option) {
			if (isset($this->values[$name])) {
				$this->activeOptions[$name]['optionValue'] = $this->values[$name];
			}
		}
		
		$this->options = $this->getCategoryOptions();
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'fleetID' => $this->fleetID,
				'options' => $this->options
		));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wot.acp.menu.link.game.fleet.search');
		
		$this->readCache();

		parent::show();
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {		
		$this->matches = array();
		
		$sql = "SELECT option_value.fleetID
				FROM ugml_archive_fleet
					AS option_value";
		
		// build search condition
		$this->conditions = new ConditionBuilder();
		
		if(!empty($this->fleetID)) {
			$this->conditions->add("option_value.fleetID = ".$this->fleetID);
		}
		
		// dynamic fields
		foreach($this->activeOptions as $name => $option) {
			$value = isset($this->values[$option['optionName']]) ? $this->values[$option['optionName']] : null;
			
			$condition = $this->getTypeObject($option['optionType'])->getCondition($option, $value);
			if ($condition !== false) {
				$this->conditions->add($condition);
			}
		}
		
		// call buildConditions event
		EventHandler::fireAction($this, 'buildConditions');

		// do search
		$result = WCF::getDB()->sendQuery($sql.$this->conditions->get().' LIMIT 1000');
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->matches[] = $row['fleetID'];	
		}
		
		if (count($this->matches) == 0) {
			$this->users = array();
			throw new UserInputException('fleetID', 'noMatches');
		}
	}
	
	/**
	 * @see Form::save()
	 */	
	public function save() {
		parent::save();
		
		// store search result in database
		$data = serialize(array(
			'matches' => $this->matches
		));
		
		$sql = "INSERT INTO wcf".WCF_N."_search
				(userID, searchData,
				 searchDate, searchType)
				VALUES
				(".WCF::getUser()->userID.", '".escapeString($data)."',
				 ".TIME_NOW.", 'fleets')";
		unset($data); // save memory
		WCF::getDB()->sendQuery($sql);
		unset($sql); // save memory
		
		// get new search id
		$this->searchID = WCF::getDB()->getInsertID();
		$this->saved();
		
		// forward to result page
		header('Location: index.php?page=FleetList&searchID='.$this->searchID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see SearchableOptionType::getSearchFormElement()
	 */
	protected function getFormElement($type, &$optionData) {
		return $this->getTypeObject($type)->getSearchFormElement($optionData);
	}
	
	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	/*protected function checkOption($optionName) {
		$option = $this->cachedOptions[$optionName];
		return ($option['searchable'] == 1 && !$option['disabled'] && ($option['visible'] == 3 || $option['visible'] < 2));
	}*/
}
?>