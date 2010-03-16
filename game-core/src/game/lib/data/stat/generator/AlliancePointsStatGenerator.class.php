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
 * This class creates the stats of the points of the users.
 * 
 * @author		Biggerskimo
 * @copyright	2008-2009 Lost Worlds <http://lost-worlds.net>
 */
class AlliancePointsStatGenerator extends AbstractStatGenerator {
	public $template = 'alliancePointsStatBit';
	protected $userPendant = 'points';
	
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
								ugml_stat_entry.points / COUNT(*)
									AS average,";
		$this->sqlJoins .= " LEFT JOIN ugml_users
								ON ugml_alliance.id = ugml_users.ally_id";
		$this->sqlGroupBy .= " ugml_alliance.id,";
	}
	
	/**
	 * Searches for the stat type id of the equivalent user statistics
	 * 
	 * @return	int		stat type id
	 */
	protected function searchUserStatPendant() {
		$userStatGenerator = StatGeneratorFactory::getByTypeName('user', $this->userPendant);
		
		return $userStatGenerator->statTypeID;
	}
	
	/**
	 * @see AbstractStatGenerator::generateEntries()
	 */
	protected function generateEntries() {
		$sql = "REPLACE INTO ugml_stat_entry
				SELECT ".$this->statTypeID.", ally_id, 0, 0, SUM(ugml_stat_entry.points)
				FROM ugml_stat_entry
				LEFT JOIN ugml_users
					ON ugml_stat_entry.relationalID = ugml_users.id
				LEFT JOIN ugml_alliance
					ON ugml_users.ally_id = ugml_alliance.id
				WHERE ugml_alliance.id != 0
					AND ugml_stat_entry.statTypeID = ".$this->searchUserStatPendant()."
				GROUP BY ugml_alliance.id";
		// lets go!
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see AbstractStatGenerator::getCurrentRelationalID()
	 */
	public function getCurrentRelationalID() {
		return WCF::getUser()->ally_id;
	}
}
?>