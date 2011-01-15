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
		/*global $game_config;
		$galaxies = $game_config['galaxies'];
		$o = $galaxies * ($galaxies + 1) / 2; // Gaussian formula*/
		
		do {
			//$r = rand(1, $o);
			//$galaxy = $galaxies - ceil((-1 + sqrt(1 + 8 * $r)) / 2); // derived from the Gaussian formula
			$galaxy = 1;
			$system = mt_rand(1, 499);
			$planet = mt_rand(4, 12);
		} while(!$this->isFree($galaxy, $system, $planet));
		
		return array($galaxy, $system, $planet);
	}
	
	/**
	 * Creates a new planet.
	 */
	protected function createPlanet() {
		list($galaxy, $system, $planet) = $this->findCoordinates();
		
		$planetObj = PlanetEditor::create($galaxy, $system, $planet, 'Heimatplanet', $this->data['userid'], 500, 500, 500, 1, time(), 400);
		
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