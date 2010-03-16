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

require_once(LW_DIR.'lib/data/stat/generator/AbstractStatGenerator.class.php');
require_once(LW_DIR.'lib/data/stat/generator/StatGeneratorFactory.class.php');

/**
 * This class creates the stats of the average points of alliances.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
class AlliancePointsAvgStatGenerator extends AbstractStatGenerator {
	public $template = 'alliancePointsAvgStatBit';
	protected $basicStatType = 'points';
	
	/**
	 * @see AbstractStatGenerator::__construct()
	 */
	public function __construct($statTypeID) {
		parent::__construct($statTypeID);
				
		// alliance
		$this->sqlSelects .= " ugml_alliance.id
									AS allianceID,
								ugml_alliance.ally_name
									AS allianceName,
								ugml_alliance.ally_tag
									AS allianceTag,";
		$this->sqlJoins .= " LEFT JOIN ugml_alliance
								ON ugml_stat_entry.relationalID = ugml_alliance.id";
		
		// members and average
		$this->sqlSelects .= " COUNT(*)
									AS membersCount,
								ugml_stat_entry.points * COUNT(*)
									AS entirePoints,
								ugml_stat_entry.points
									AS average,";
		$this->sqlJoins .= " LEFT JOIN ugml_users
								ON ugml_alliance.id = ugml_users.ally_id";
		$this->sqlGroupBy .= " ugml_alliance.id,";
	}
	
	/**
	 * Searches for the stat type id of base stat type (pointsAvg => points)
	 * 
	 * @return	int		stat type id
	 */
	protected function searchBasicStatType() {
		$basicStatGenerator = StatGeneratorFactory::getByTypeName('alliance', $this->basicStatType);
		
		return $basicStatGenerator->statTypeID;
	}
	
	/**
	 * @see AbstractStatGenerator::generateEntries()
	 */
	protected function generateEntries() {
		$sql = "REPLACE INTO ugml_stat_entry
				SELECT ".$this->statTypeID.", ally_id, 0, 0, ugml_stat_entry.points / COUNT(*)
				FROM ugml_stat_entry
				LEFT JOIN ugml_users
					ON ugml_stat_entry.relationalID = ugml_users.ally_id
				WHERE ugml_stat_entry.statTypeID = ".$this->searchBasicStatType()."
				GROUP BY ugml_stat_entry.relationalID";
		// lets go!
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see AbstractStatGenerator::getCurrentRelationalID()
	 */
	public function getCurrentRelationalID() {
		return WCF::getUser()->ally_id;
	}
	
	/**
	 * @see AbstractStatGenerator::getOptionName()
	 */
	public function getOptionName() {
		return $this->basicStatType;
	}
}
?>