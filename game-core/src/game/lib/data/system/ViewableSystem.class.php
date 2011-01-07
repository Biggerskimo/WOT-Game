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

// lw
require_once(LW_DIR.'lib/data/fleet/EspionageFleet.class.php');
require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/system/System.class.php');

class ViewableSystem extends System {

	/**
	 * Returns the number of planets
	 *
	 * @return	int		number of planets
	 */
	public function planetCount() {
		$count = 0;

		foreach($this->planets as $positionData) {
			if($positionData['planet'] !== null) $count++;
		}
		return $count;
	}

	/**
	 * View planet image
	 *
	 * @param	int		position
	 */
	protected function viewPlanetImage($position) {
		global $user;

		$planet = $this->getPlanet($position);
		if($planet === null) return '';

		$lang = array('T_TEMP' => $user["settings_tooltiptime"]*1000,
				'planet_name' => $planet->name,
				'g' => $planet->galaxy,
				's' => $planet->system,
				'i' => $planet->planet,
				'image' => $planet->image,
				'spio_anz' => $user['spio_anz']);

		return parsetemplate(gettemplate('galaxy_row_planet'), $lang);
	}

	/**
	 * View planet
	 *
	 * @param	int		position
	 */
	protected function viewPlanet($position) {
		global $user;

		$planet = $this->getPlanet($position);
		if($planet === null) return '';

		// phalanx check
		$phalanxable = true;

		if($this->galaxy != LWCore::getPlanet()->galaxy) $phalanxable = false;

		$range = (pow(LWCore::getPlanet()->sensor_phalanx, 2) - 1);

		if($this->system < LWCore::getPlanet()->system - $range) $phalanxable = false;

		if($this->system > LWCore::getPlanet()->system + $range) $phalanxable = false;

		if(LWCore::getPlanet()->deuterium < $this->getPhalanxCosts()) $phalanxable = false;

		if($planet->id_owner == WCF::getUser()->userID) $phalanxable = false;

		if(LWCore::getPlanet()->sensor_phalanx <= 0) $phalanxable = false;


		if($phalanxable) $name = '<a href="#" onclick="f(\'game/index.php?page=Phalanx&amp;galaxy='.$planet->galaxy.'&amp;system='.$planet->system.'&amp;planet='.$planet->planet.'\', \'Phalanx\');">'.$planet->name.'</a>';
		else $name = $planet->name;

		if($planet->last_update > (time()- (15 * 60)) && $user['id'] != $planet->userID) {
			$name .= ' (*)';
		} else if($planet->last_update > (time()- (60 * 60)) && $user['id'] != $planet->userID) {
			$seconds = time() - $planet->last_update;
			$minutes = floor(($seconds / 60));
			$name .= ' ('.$minutes.' min)';
		}

		return $name;
	}

	/**
	 * View moon
	 *
	 * @param	int		position
	 */
	protected function viewMoon($position) {
		global $user;

		$planet = $this->getPlanet($position, 'moon');
		if($planet === null) return '';

		$lang = array('T_TEMP' => $user["settings_tooltiptime"]*1000,
				'luna_name' => $planet->name,
				'g' => $planet->galaxy,
				's' => $planet->system,
				'i' => $planet->planet,
				'image' => $planet->image,
				'temp' => $planet->temp_max,
				'spio_anz' => $user['spio_anz'],
				'diameter' => $planet->diameter);

		return parsetemplate(gettemplate('galaxy_row_luna'), $lang);
	}

	/**
	 * View debris
	 *
	 * @param	int		position
	 */
	protected function viewDebris($position) {
		global $user;

		$planet = $this->getPlanet($position, 'debris');
		if($planet === null || (@$planet->metal == 0 && @$planet->crystal == 0)) return '';

		$lang = array('T_TEMP' => $user["settings_tooltiptime"]*1000,
				'planet_name' => $planet->name,
				'g' => $planet->galaxy,
				's' => $planet->system,
				'p' => $planet->planet,
				'image' => $planet->image,
				'debris_metal' => number_format(floor($planet->metal), $decimals, ',', '.'),
				'debris_crystal' => number_format(floor($planet->crystal), $decimals, ',', '.'));

		return parsetemplate(gettemplate('galaxy_row_debris'), $lang);
	}

	/**
	 * View user information
	 *
	 * @param	int		position
	 */
	protected function viewUserInformation($position) {
		global $game_config, $user;

		$planet = $this->getPlanet($position);
		if($planet === null) return '';

		$lang = array('T_TEMP' => $user["settings_tooltiptime"]*1000,
				'user_id' => $planet->id_owner,
				'userID' => $planet->id_owner,
				'username' => $planet->username);

		// ( normal ) ------+
		// inactive			|
		// longinactive		|
		// confederation	|
		// war enemy		|
		// noob/strong		|
		// banned			|
		// vacation			|
		// admin			|
		//		<-----------+

		$class = '';
		$flag = '';

		// inactive
		if($planet->onlinetime < (time() - (24 * 60 * 60 * 7))) {
			if(empty($class)) $class = 'inactive '.$class;
			else $class = 'inactive '.$class;

			if(empty($flag)) $flag .= '(<span class="inactive">i</span>';
			else $flag .= ' <span class="inactive">i</span>';
		}

		// long inactive
		if($planet->onlinetime < (time() - (24 * 60 * 60 * 28))) {
			if(empty($class)) $class = 'longinactive '.$class;
			else $class = 'longinactive '.$class;

			if(empty($flag)) $flag .= '(<span class="longinactive">l</span>';
			else $flag .= ' <span class="longinactive">l</span>';
		}
		
		if(WCF::getUser()->ally_id) {
			// confederation
			if(@LWCore::getAlliance()->getInterrelation($planet->ally_id, 1) || LWCore::getAlliance()->getInterrelation($planet->ally_id, 2)) {
				if(empty($class)) $class = 'confederation '.$class;
				else $class = 'confederation '.$class;
	
				if(empty($flag)) $flag .= '(<span class="confederation">b</span>';
				else $flag .= ' <span class="confederation">b</span>';
			}
			
			// war enemy
			if(@LWCore::getAlliance()->getInterrelation($planet->ally_id, 3)) {
				if(empty($class)) $class = 'enemy '.$class;
				else $class = 'enemy '.$class;
	
				if(empty($flag)) $flag .= '(<span class="enemy">k</span>';
				else $flag .= ' <span class="enemy">k</span>';
			}
		}
		
		if($game_config['noobProtection']) {
			
			// noob
			if(WCF::getUser()->points > $planet->noobProtectionLimit() && $planet->onlinetime > (time() - (24 * 60 * 60 * 7)) && !$planet->banned) {
				if(empty($class)) $class = 'noob '.$class;
				else $class = 'noob '.$class;
	
				if(empty($flag)) $flag .= '(<span class="noob">n</span>';
				else $flag .= ' <span class="noob">n</span>';
			
			// strong
			} else if($planet->points > LWCore::getPlanet()->noobProtectionLimit() && $planet->onlinetime > (time() - (24 * 60 * 60 * 7)) && !$planet->banned) {
				if(empty($class)) $class = 'strong '.$class;
				else $class = 'strong '.$class;
	
				if(empty($flag)) $flag .= '(<span class="strong">s</span>';
				else $flag .= ' <span class="strong">s</span>';
			}
		}

		// banned
		if($planet->banned) {
			if(empty($class)) $class = 'banned '.$class;
			else $class = 'banned '.$class;

			if(empty($flag)) $flag .= '(<a href="banned.php"><span class="banned">g</span></a>';
			else $flag .= ' <a href="banned.php"><span class="banned">g</span></a>';
		}

		// vacation
		if($planet->urlaubs_modus) {
			if(empty($class)) $class = 'vacation '.$class;
			else $class = 'vacation '.$class;

			if(empty($flag)) $flag .= '(<span class="vacation">u</span>';
			else $flag .= ' <span class="vacation">u</span>';
		}

		// vacation
		if($planet->authlevel) {
			if(empty($class)) $class = 'admin '.$class;
			else $class = 'admin '.$class;

			if(empty($flag)) $flag .= '(<span class="admin">a</span>';
			else $flag .= ' <span class="admin">a</span>';
		}

		// normal
		if(!empty($flag)) $flag = '<span class="flag">'.$flag.')</span>';
		else $class .= 'normal '.$class;

		$lang['class'] = $class;
		$lang['flag'] = $flag;
		$lang['rank'] = $planet->getOwner()->wotRank;

		return parsetemplate(gettemplate('galaxy_row_user'), $lang);
	}

	/**
	 * View alliance information
	 *
	 * @param	int		position
	 */
	protected function viewAllianceInformation($position) {
		$planet = $this->getPlanet($position);
		if($planet === null) return '';

		$lang = array('ally_rank' => $planet->allyRankPoints,
				'ally_web' => $planet->ally_web,
				'ally_id' => $planet->ally_id,
				'ally_tag' => $planet->ally_tag,
				'AllyInfoText' => 'Allianz '.$planet->ally_name.' auf Platz '.$planet->allyRankPoints.' mit '.$planet->ally_members.' Mitglieder(n)');

		return parsetemplate(gettemplate('galaxy_row_ally'), $lang);
	}

	/**
	 * View action links
	 *
	 * @param	int		position
	 */
	protected function viewActions($position) {
		global $user;

		$planet = $this->getPlanet($position);
		if($planet === null || $planet->userID == $user['id']) return '';

		$lang = array('g' => $planet->galaxy,
				's' => $planet->system,
				'i' => $planet->planet,
				'spio_anz' => $user['spio_anz'],
				'userID' => $planet->id_owner);
		// reports
		$features = unserialize($user['diliziumFeatures']);
		
		if(@$features['galaxyScans'] > TIME_NOW && count(EspionageFleet::searchReports(WCF::getUser()->userID, $planet->planetID))) {
			$lang['reportLink'] = '<a onclick="f(\'game/index.php?page=viewScan&amp;planetID='.$planet->planetID.'\');"><img src="{dpath}img/s.gif" alt="Spionagebericht anzeigen" title="Spionagebericht anzeigen" border="0"></a>';
		} else $lang['reportLink'] = '';
		
		// interplanetaries
		$maxSystems = WCF::getUser()->impulse_motor_tech * 4;
		$systemsDistance = abs(LWCore::getPlanet()->system - $planet->system);
		if($systemsDistance <= $maxSystems
			&& LWCore::getPlanet()->galaxy == $planet->galaxy
			&& LWCore::getPlanet()->interplanetary_misil)
				$lang['interplanetaryBit'] = "<a style=\"cursor: pointer;\" onclick=\"document.getElementById('interplanetaryrmissilestable').style.display = ''; document.getElementById('ipmplanet').value = '".$planet->planet."';\"><img src=\"{dpath}img/r.gif\" alt=\"Interplanetarraketen-Angriff\" title=\"Interplanetarraketen-Angriff\" border=\"0\"></a>";
		else 
			$lang['interplanetaryBit'] = "";
				
		return parsetemplate(gettemplate('galaxy_row_action'), $lang);
	}

	/**
	 * View position row
	 *
	 * @param	int		position
	 */
	protected function viewPositionRow($position) {
		// count
		$lang['tab'] = '>';
		$lang['i'] = $position;

		// planet image
		$lang['row_planet'] = $this->viewPlanetImage($position);

		// planet name
		$lang['planet_name'] = $this->viewPlanet($position);

		// moon
		$lang['luna_name'] = $this->viewMoon($position);

		// debris
		$lang['row_debris'] = $this->viewDebris($position);

		// user
		$lang['row_user'] = $this->viewUserInformation($position);

		// alliance
		$lang['row_ally'] = $this->viewAllianceInformation($position);

		// actions
		$lang['row_action'] = $this->viewActions($position);

		return parsetemplate(gettemplate('galaxy_row'), $lang);
	}

	/**
	 * View system
	 */
	public function view() {
		global $lang, $user;

		includeLang('galaxy');

		for($i = 0; $i < 15; $i++) {
			$position = ($i + 1);

			$lang['echo_galaxy'] .= $this->viewPositionRow($position);
		}

		$lang['dpath'] = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
		$lang['galaxy'] = $this->galaxy;
		$lang['system'] = $this->system;
		$lang['tg'] = LWCore::getPlanet()->galaxy;
		$lang['ts'] = LWCore::getPlanet()->system;
		$lang['tp'] = LWCore::getPlanet()->planet;
		$lang['tpt'] = LWCore::getPlanet()->planet_type;
		$lang['planet_count'] = $this->planetCount();
		$lang['iraks'] = LWCore::getPlanet()->interplanetary_misil;
		$lang['max_slots'] = (LWCore::getUser()->computer_tech + 1);
		$lang['recycler'] = LWCore::getPlanet()->recycler;
		$lang['probes'] = LWCore::getPlanet()->spy_sonde;
		$lang['spio_anz'] = $user['spio_anz'];
		$lang['phalanx_costs'] = floor($this->getPhalanxCosts());

		$fleets = Fleet::getByUserID(WCF::getUser()->userID);
		$lang['slots'] = count($fleets);

		$lang['Solar_system_at'] = str_replace('%g', $this->galaxy, $lang['Solar_system_at']);
		$lang['Solar_system_at'] = str_replace('%s', $this->system, $lang['Solar_system_at']);

		display(parsetemplate(gettemplate('galaxy_body'), $lang), '', false);
	}

}
?>