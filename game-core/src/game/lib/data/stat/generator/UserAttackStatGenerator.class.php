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

require_once(LW_DIR.'lib/data/stat/generator/UserPointsStatGenerator.class.php');

/**
 * This class creates the stats of the points of the users.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 */
class UserAttackStatGenerator extends UserPointsStatGenerator {	
	/**
	 * Changes the points of the user.
	 * 
	 * @param	int		userID
	 * @param	int		points +-
	 */
	public function changePoints($userID, $change) {
		$userID = intval($userID);
		$oldRank = $this->getCurrentRank($userID);
		$points = $this->getPoints($userID);
		$newPoints = $points + intval(round($change));
		
		$sql = "SELECT MAX(rank)
					AS nextRank
				FROM ugml_stat_entry
				WHERE statTypeID = ".$this->statTypeID."
					AND (
						points > ".$newPoints."
						OR (points = ".$newPoints."
							AND relationalID > ".$userID.")
						)
					AND rank != ".$oldRank;
		$row = WCF::getDB()->getFirstRow($sql);
		$nextRank = intval($row['nextRank']);
		$currentRank = $nextRank + 1;
		
		// user outran other players
		if($currentRank < $oldRank) {
			$sql = "UPDATE ugml_stat_entry
					SET rank = rank + 1,
						`change` = `change` - 1
					WHERE statTypeID = ".$this->statTypeID."
						AND rank BETWEEN ".$currentRank." AND ".($oldRank - 1);
			WCF::getDB()->sendQuery($sql);
		}
		// user lost some positions
		else if($currentRank > $oldRank) {
			$sql = "UPDATE ugml_stat_entry
					SET rank = rank - 1,
						`change` = `change` + 1
					WHERE statTypeID = ".$this->statTypeID."
						AND rank BETWEEN ".($oldRank + 1)." AND ".$currentRank;
			WCF::getDB()->sendQuery($sql);			
		}
		// no rank change, only change points
		else {		
			$sql = "UPDATE ugml_stat_entry
					SET points = points + ".$change."
					WHERE statTypeID = ".$this->statTypeID."
						AND relationalID = ".$userID;
			WCF::getDB()->sendQuery($sql);
			
			return;
		}
		
		// update user's rank and points
		$sql = "UPDATE ugml_stat_entry
				SET rank = rank - ".($oldRank - $currentRank).",
					`change` = `change` + ".($oldRank - $currentRank).",
					points = points + ".$change."
				WHERE statTypeID = ".$this->statTypeID."
					AND relationalID = ".$userID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see AbstractStatGenerator::deleteEntries()
	 */
	protected function deleteEntries() {
		
	}
	
	/**
	 * @see UserPointsStatGenerator::generateDummies()
	 */
	protected function generateDummies() {
		$sql = "INSERT IGNORE INTO ugml_stat_entry
				(statTypeID, relationalID, points)
				SELECT ".$this->statTypeID.", id, 1000
				FROM ugml_users";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see AbstractStatGenerator::createTemporaryEntry()
	 */
	protected function createTemporaryEntry($relationalID) {
		$sql = "SELECT MAX(rank)
					AS rank
				FROM ugml_stat_entry
				WHERE statTypeID = ".$this->statTypeID;
		$row = WCF::getDB()->getFirstRow($sql);
		$rank = $row['rank'] + 1;
		
		$sql = "INSERT INTO ugml_stat_entry
				(statTypeID, relationalID, rank, points)
				VALUES
				(".$this->statTypeID.", ".$relationalID.", ".$rank.", 1000)";
		WCF::getDB()->sendQuery($sql);
		
		StatGeneratorFactory::init();
		WCF::getCache()->clearResource('statTypes-'.PACKAGE_ID, true);
	}
	
	/**
	 * @see StatGenerator::generateEntries()
	 */
	protected function generateEntries() {
		$this->generateDummies();
		
		$this->updateEntries();
	}
	
	/**
	 * Updates the entries.
	 */
	protected function updateEntries() {
		global $game_config;
		
		if($game_config['lastAttackStatClean'.$this->statTypeID] + 60 * 60 * 22 < time()) {
			$sql = "UPDATE ugml_stat_entry
					SET	points = points - IF(points = 1000, 0, IF(points > 1000, SMALLER(FLOOR(POW(1.25, (points / 100 - 10))), (points / 10)), -SMALLER(POW(2, ((1000 - points) / 68)), BIGGER((points / 5), 0))))
					WHERE statTypeID = ".$this->statTypeID;
			WCF::getDB()->sendQuery($sql);
			
			$sql = "UPDATE ugml_config
					SET config_value = ".time()."
					WHERE config_name = 'lastAttackStatClean".$this->statTypeID."'";
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>