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

require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(LW_DIR.'lib/data/stat/generator/StatGeneratorFactory.class.php');

/**
 * This page is able to show the different statistic types.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class StatisticsPage extends AbstractPage {
	public $templateName = 'statistics';
	
	protected $type = 'user';
	protected $name = 'points';
	
	protected $types = array();
	protected $names = array();
	
	protected $start = 1;
	protected $relationalID = false;
	protected $rowCount = 100;	
	
	public $statGenerator = null;
	public $rows = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['type'])) {
			$this->type = StringUtil::trim($_REQUEST['type']);
		}		
		if(isset($_REQUEST['name'])) {
			$this->name = StringUtil::trim($_REQUEST['name']);
		}
		
		if(isset($_REQUEST['startInput']) && !empty($_REQUEST['startInput'])) {
			$this->start = LWUtil::checkInt($_REQUEST['startInput'], 1);
						
			if($this->start >= 1 && $this->start <= 99) {
				// e.g. 5 => 501; 14 => 1401
				$this->start *= 100;
				$this->start++;
			}
		}
		else if(isset($_REQUEST['start'])) {
			$this->start = LWUtil::checkInt($_REQUEST['start'], 0);
		}
		else if(isset($_REQUEST['relationalID'])) {
			$this->relationalID = LWUtil::checkInt($_REQUEST['relationalID']);
		}
		
		if(isset($_REQUEST['rowCount'])) {
			$this->rowCount = LWUtil::checkInt($_REQUEST['rowCount'], 10, 500);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->statGenerator = StatGeneratorFactory::getByTypeName($this->type, $this->name);
		
		if(!$this->relationalID) {
			$this->rows = $this->statGenerator->getRows($this->start, $this->rowCount);
		}
		else {
			$this->rows = $this->statGenerator->getRows(0, $this->rowCount, $this->relationalID);
		}
		$optionName = $this->statGenerator->getOptionName();
		
		$cache = WCF::getCache()->get('statTypes-'.PACKAGE_ID);
		foreach($cache['types'] as $type) {
			if(StatGeneratorFactory::checkTypeName($type, $optionName)) {
				$this->types[] = $type;
			}
		}
		
		foreach($cache['names'] as $name) {
			if(StatGeneratorFactory::checkTypeName($this->type, $name)) {
				$this->names[] = $name;
			}
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array('statGenerator' => $this->statGenerator,
									'rows' => $this->rows,
									'statEntryTemplate' => $this->statGenerator->getTemplateName(),
									'optionName' => $this->statGenerator->getOptionName(),
									'types' => $this->types,
									'names' => $this->names,
									// selected values
									'showStart' => $this->start,
									'showRelationalID' => $this->relationalID,
									'showType' => $this->type,
									'showName' => $this->name,
									'debug' => $this));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
?>