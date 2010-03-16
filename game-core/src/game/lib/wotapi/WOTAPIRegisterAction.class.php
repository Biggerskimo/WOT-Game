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

require_once(LW_DIR.'lib/wotapi/AbstractWOTAPIAction.class.php');
require_once(LW_DIR.'lib/data/account/AccountEditor.class.php');

/**
 * Registers a new account.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIRegisterAction extends AbstractWOTAPIAction {
	public $userID = 0;
	public $username = '';
	public $email = '';
	
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->userID = intval($this->data['userid']);
		$this->username = WOTAPIUtil::escape($this->data['username']);
		$this->email = WOTAPIUtil::escape($this->data['username']);
	}

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		if(!$this->createUser()) {
			return;
		}
		$this->createPlanet();
		
		parent::execute();
	}
	
	/**
	 * Creates a new user row
	 */
	protected function createUser() {
		// check
		$sql = "SELECT COUNT(*)
					AS count
				FROM ugml_users
				WHERE id = ".$this->userID;
		$result = WCF::getDB()->getFirstRow($sql);
		
		if($result['count'] > 0) {
			$this->wotAPIServerClient->send('user already exists', 300);
			return false;
		}
		
		AccountEditor::create($this->userID, $this->username, $this->email);
		
		return true;
	}
	
	/**
	 * Checks if the position is free.
	 * 
	 * @param	int		galaxy
	 * @param	int		system
	 * @param	int		planet
	 * @return	bool	free
	 */
	protected function isFree($galaxy, $system, $planet) {
		$sql = "SELECT COUNT(*)
					AS count
				FROM ugml_planets
				WHERE galaxy = ".$galaxy."
					AND system = ".$system."
					AND planet = ".$planet;
		$result = WCF::getDB()->getFirstRow($sql);
		
		if($result['count']) return false;
		
		return true;
	}
	
	/**
	 * Finds free coordinates for a planet.
	 * 
	 * @return	array	galaxy, system, planet
	 */
	protected function findCoordinates() {
		global $game_config;
		
		$id_g =& $game_config['id_g'];
		$id_s =& $game_config['id_s'];
		$id_p =& $game_config['id_p'];
		do {
			$newGalaxy = false;
			do {
				$newSystem = false;
				// create planet
				do {
					$newPlanet = false;
					switch($id_p) {
						case 0:
						case 1:
						case 2:
							$id_p++;
							break;
						case 3:
							$newSystem = $newPlanet = true;
					}
					if(!$newPlanet) {
						// make planet
						$planet = rand(4, 12);
						$newPlanet = !$this->isFree($id_g, $id_s, $planet);
						if(!$newPlanet) break 3;
					} else $newPlanet = false;
				} while($newPlanet);
				if($newSystem) {
					// change system
					if($id_s == 499) {
						$newGalaxy = true;
						$newSystem = false;
					} else {
						$id_s++;
						$id_p = 0;
						WCF::getDB()->sendQuery("UPDATE ugml_config SET config_value = '".$id_s."' WHERE config_name = 'id_s'");
					}
				}
			} while($newSystem);
			if($newGalaxy) {
				// change galaxy
				$id_g++;
				$id_p = $id_s = 1;
				WCF::getDB()->sendQuery("UPDATE ugml_config SET config_value = '".$id_g."' WHERE config_name = 'id_g'");
				WCF::getDB()->sendQuery("UPDATE ugml_config SET config_value = '".$id_s."' WHERE config_name = 'id_s'");
			}
		} while($newGalaxy);
		
		WCF::getDB()->sendQuery("UPDATE ugml_config SET config_value = '".$id_p."' WHERE config_name = 'id_p'");
		
		return array($id_g, $id_s, $planet);
	}
	
	/**
	 * Creates a new planet.
	 */
	protected function createPlanet() {
		list($galaxy, $system, $planet) = $this->findCoordinates();
		
		$planetObj = PlanetEditor::create($galaxy, $system, $planet, 'Heimatplanet', $this->data['userid'], 500, 500, 0, 1, time(), 400);
		
		$sql = "UPDATE ugml_users
				SET current_planet = ".$planetObj->planetID.",
					id_planet = ".$planetObj->planetID.",
					galaxy = ".$galaxy.",
					system = ".$system.",
					planet = ".$planet."
				WHERE id = ".$this->data['userid'];
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();
		
		$this->wotAPIServerClient->send('user successful created');
	}
}
?>