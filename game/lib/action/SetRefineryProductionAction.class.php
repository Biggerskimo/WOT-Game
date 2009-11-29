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

require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Sets the new refinery production.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Wolrds
 */
class SetRefineryProductionAction extends AbstractAction {
	public $production = '';
	
	public $possibleProductions = array('metal', 'crystal', 'deuterium');

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_REQUEST['production'])) {
			$this->production = StringUtil::trim($_REQUEST['production']);
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		if (!WCF::getUser()->userID) {
			message('Einloggen!');
		}
		
		if(in_array($this->production, $this->possibleProductions)) {
			$sql = "UPDATE ugml_planets
					SET refineryProduction = '".escapeString($this->production)."',
						refineryProductionChange = ".time()."
					WHERE id = ".LWCore::getPlanet()->id;
			WCF::getDB()->sendQuery($sql);
		}
		
		header('Location: ../buildings.php');
		exit;
	}
}
?>