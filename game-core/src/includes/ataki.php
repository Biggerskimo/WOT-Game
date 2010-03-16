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

function walka($attackerData, $defenderData, $attackerTechs, $defenderTechs){
	global $pricelist, $game_config;
	$roundData = array();

	/**
	 * Create ships
	 */

	// Attacker
	$attackerShips = array();
	$attackerShipCount = 0;
	foreach($attackerData as $shipTypeID => $count) {
		$attackerData[$shipTypeID] = array();
		$attackerData[$shipTypeID]['count'] = 0;
		$weapon = $attackerData[$shipTypeID]['weapon'] = $pricelist[$shipTypeID]["attack"] * (1 + (0.1 * $attackerTechs["military_tech"]));
		$shield = $attackerData[$shipTypeID]['shield'] = $pricelist[$shipTypeID]["shield"] * (1 + (0.1 * $attackerTechs["shield_tech"]));
		$hullPlating = $attackerData[$shipTypeID]['hullPlating'] = ($pricelist[$shipTypeID]["metal"] + $pricelist[$shipTypeID]["crystal"]) /10 * (1 + (0.1 * ($attackerTechs["defence_tech"])));
		$units = $attackerData[$shipTypeID]['units'] = ($pricelist[$shipTypeID]['metal'] + $pricelist[$shipTypeID]['crystal']);
		$ship = array('shipTypeID' => $shipTypeID,
			'weapon' => $weapon,
			'shield' => $shield,
			'hullPlating' => $hullPlating,
			'startedShield' => $shield,
			'startedHullPlating' => $hullPlating,
			'explosion' => false,
			'units' => $units,
			'imbibed' => 0,
			'count' => 0,
			'shots' => 0);
		while($attackerData[$shipTypeID]['count'] < $count) {
			$attackerShips[] = $ship;
			$attackerData[$shipTypeID]['count']++;
		}
		$attackerShipCount += $attackerData[$shipTypeID]['count'];
	}

	// Defender
	$defenderShips = array();
	$defenderShipCount = 0;
	foreach($defenderData as $shipTypeID => $count) {
		$defenderData[$shipTypeID] = array();
		$weapon = $defenderData[$shipTypeID]['weapon'] = $pricelist[$shipTypeID]["attack"] * (1 + (0.1 * $defenderTechs["military_tech"]));
		$shield = $defenderData[$shipTypeID]['shield'] = $pricelist[$shipTypeID]["shield"] * (1 + (0.1 * $defenderTechs["shield_tech"]));
		$hullPlating = $defenderData[$shipTypeID]['hullPlating'] = ($pricelist[$shipTypeID]["metal"] + $pricelist[$shipTypeID]["crystal"])/10 * (1 + (0.1 * ($defenderTechs["defence_tech"])));
		$units = $defenderData[$shipTypeID]['units'] = ($pricelist[$shipTypeID]['metal'] + $pricelist[$shipTypeID]['crystal']);
		$ship = array('shipTypeID' => $shipTypeID,
			'weapon' => $weapon,
			'shield' => $shield,
			'hullPlating' => $hullPlating,
			'startedShield' => $shield,
			'startedHullPlating' => $hullPlating,
			'explosion' => false,
			'units' => $units,
			'imbibed' => 0,
			'count' => 0,
			'shots' => 0);
		while($defenderData[$shipTypeID]['count'] < $count) {
			$defenderShips[] = $ship;
			$defenderData[$shipTypeID]['count']++;
		}
		$defenderShipCount += $defenderData[$shipTypeID]['count'];
	}

	// Collect data for kb
	$roundData[0]['attackerData'] = $attackerData;
	$roundData[0]['defenderData'] = $defenderData;

	/**
	 * Execute rounds
	 */
	for ($round = 1; $round <= 6; $round++){

		$roundData[$round-1]['defferShipCount'] = $defenderShipCount;
		if($defenderShipCount == 0) break;

		/**
		 * Reset imbibed and shot data
		 */
		foreach($attackerData as $shipTypeID => $shipData) {
			$shipData['imbibed'] = 0;
			$shipData['shots'] = 0;

			$attackerData[$shipTypeID] = $shipData;
		}
		foreach($defenderData as $shipTypeID => $shipData) {
			$shipData['imbibed'] = 0;
			$shipData['shots'] = 0;

			$defenderData[$shipTypeID] = $shipData;
		}

		/**
		 * Shots
		 */
		// Attacker
		foreach($attackerShips as $attackerShipID => $attackerShipData) {
			do {
				$attackerShips[$attackerShipID]['shots']++;

				// select random ship of the defender
				$defenderShipID = rand(0, $defenderShipCount - 1);
				$defenderShipData = $defenderShips[$defenderShipID];

				// destroy shield
				if($attackerShipData['weapon'] < $defenderShipData['shield'] / 100) continue;
				if($attackerShipData['weapon'] < $defenderShipData['shield']) {
					$defenderShipData['shield'] -= $attackerShipData['weapon'];
				} else {
					$afterShieldAttackerWeapon = $attackerShipData['weapon'] - $defenderShipData['shield'];
					$defenderShipData['shield'] = 0;

					// destroy hull plating
					if($afterShieldAttackerWeapon < $defenderShipData['hullPlating']) {
						$defenderShipData['hullPlating'] -= $afterShieldAttackerWeapon;
						$explosionChance = $afterShieldAttackerWeapon / $defenderShipData['startedHullPlating'];
						if($explosionChance <= 0.3) {
							$explosionChance = 0;
							$defenderShipData['explosion'] = false;
						} else {
							if($explosionChance > 1) $explosionChance = 1;
							$explosion = rand(1, 100);
							if($explosion < $explosionChance * 100) $defenderShipData['explosion'] = true;
							else $defenderShipData['explosion'] = false;
						}
					} else {
						$defenderShipData['hullPlating'] = 0;
						$defenderShipData['explosion'] = true;
					}
				}
				$defenderShips[$defenderShipID] = $defenderShipData;

				// Rapidfire
				if(isset($pricelist[$attackerShipData['shipTypeID']]['sd'][$defenderShipData['shipTypeID']])) {
					$rand = rand(1, 1000000);
					$ship = (1 - (1 / $pricelist[$attackerShipData['shipTypeID']]['sd'][$defenderShipData['shipTypeID']])) * 1000000;
					if($ship > $rand) $newShot = true;
					else $newShot = false;
				} else $newShot = false;

			} while($newShot);
		}

		// Defender
		foreach($defenderShips as $defenderShipID => $defenderShipData) {
			do {
				$defenderShips[$defenderShipID]['shots']++;

				// select random ship of the attacker
				$attackerShipID = rand(0, $attackerShipCount - 1);
				$attackerShipData = $attackerShips[$attackerShipID];

				// destroy shield
				if($defenderShipData['weapon'] < $attackerShipData['shield'] / 100) continue;
				if($defenderShipData['weapon'] < $attackerShipData['shield']) {
					$attackerShipData['shield'] -= $defenderShipData['weapon'];
				} else {
					$afterShieldDefenderWeapon = $defenderShipData['weapon'] - $attackerShipData['shield']; /* log this line: */
					$attackerShipData['shield'] = 0;

					// destroy hull plating
					if($afterShieldDefenderWeapon < $attackerShipData['hullPlating']) { /* log this line: */
						$attackerShipData['hullPlating'] -= $afterShieldDefenderWeapon;
						$explosionChance = $afterShieldDefenderWeapon / $attackerShipData['startedHullPlating'];
						if($explosionChance <= 0.3) {
							$explosionChance = 0;
							$attackerShipData['explosion'] = false;
						} else {
							if($explosionChance > 1) $explosionChance = 1;
							$explosion = rand(1, 100);
							if($explosion < $explosionChance * 100) $attackerShipData['explosion'] = true;
							else $attackerShipData['explosion'] = false;
						}
					} else {
						$attackerShipData['hullPlating'] = 0;
						$attackerShipData['explosion'] = true;
					}
				}
				$attackerShips[$attackerShipID] = $attackerShipData;

				// Rapidfire
				if(isset($pricelist[$defenderShipData['shipTypeID']]['sd'][$attackerShipData['shipTypeID']])) {
					$rand = rand(1, 1000000);
					$ship = (1 - (1 / $pricelist[$defenderShipData['shipTypeID']]['sd'][$attackerShipData['shipTypeID']])) * 1000000;
					if($ship > $rand) $newShot = true;
					else $newShot = false;
				} else $newShot = false;

			} while($newShot);
		}

		/**
		 * Destroy ships and prepare next round
		 */

		// Attacker
		$nextRoundAttackerShipData = array();
		foreach($attackerShips as $shipID => $shipData) {
			if(!$shipData['explosion']) {
				$attackerData[$shipData['shipTypeID']]['imbibed'] += ($shipData['startedShield'] - $shipData['shield']);
				$shipData['shield'] = $shipData['startedShield'];
				$shipData['explosion'] = false;
				$attackerData[$shipData['shipTypeID']]['shots'] += $shipData['shots'];
				$shipData['shots'] = 0;
				$nextRoundAttackerShipData[] = $shipData;
			} else {
				$attackerData[$shipData['shipTypeID']]['count']--;
				$attackerData[$shipData['shipTypeID']]['shots'] += $shipData['shots'];
				$attackerData[$shipData['shipTypeID']]['imbibed'] += ($shipData['startedShield'] - $shipData['shield']);
				$attackerShipCount--;
			}

			unset($attackerShips[$shipID]);
		}

		// Defender
		$nextRoundDefenderShipData = array();
		foreach($defenderShips as $shipID => $shipData) {
			if(!$shipData['explosion']) {
				$defenderData[$shipData['shipTypeID']]['imbibed'] += ($shipData['startedShield'] - $shipData['shield']);
				$shipData['shield'] = $shipData['startedShield'];
				$shipData['explosion'] = false;
				$defenderData[$shipData['shipTypeID']]['shots'] += $shipData['shots'];
				$shipData['shots'] = 0;
				$nextRoundDefenderShipData[] = $shipData;
			} else {
				$defenderData[$shipData['shipTypeID']]['count']--;
				$defenderData[$shipData['shipTypeID']]['shots'] += $shipData['shots'];
				$defenderData[$shipData['shipTypeID']]['imbibed'] += ($shipData['startedShield'] - $shipData['shield']);
				$defenderShipCount--;
			}

			unset($defenderShips[$shipID]);
		}

		$attackerShips = $nextRoundAttackerShipData;
		$defenderShips = $nextRoundDefenderShipData;

		$roundData[$round]['attackerData'] = $attackerData;
		$roundData[$round]['defenderData'] = $defenderData;

		if($attackerShipCount == 0 || $defenderShipCount == 0) break;
	}

	return $roundData;
}

function generateCombatData($roundData, $defenderPlanet, $fleetData) {
	global $pricelist, $game_config, $user;
	$rounds = count($roundData) - 1;
	$lastRoundData = $roundData[$rounds];

	/**
	 * Check winner
	 */
	foreach($lastRoundData['attackerData'] as $shipData) {
		if($shipData['count'] != 0) {
			$winner = 'attacker';
			break;
		}
	}
	if($winner == 'attacker') {
		foreach($lastRoundData['defenderData'] as $shipData) {
			if($shipData['count'] != 0) {
				$winner = 'draw';
				break;
			}
		}
	}
	if(!isset($winner)) $winner = 'defender';

	/**
	 * Calculate booty
	 *
	 * Steps: http://www.owiki.de/Beute
	 */
	$booty = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);

	if($winner == 'attacker') {
		// capacity
		$capacity = 0;
		foreach($lastRoundData['attackerData'] as $shipTypeID => $shipData) {
			$capacity += $shipData['count'] * $pricelist[$shipTypeID]['capacity'];
		}
		$capacity -= $fleetData['metal'] + $fleetData['crystal'] + $fleetData['deuterium'];

		// Step 1
		if(($defenderPlanet['metal'] / 2) > ($capacity / 3)) $booty['metal'] = ($capacity / 3);
		else $booty['metal'] = ($defenderPlanet['metal'] / 2);
		$capacity -= $booty['metal'];

		// Step 2
		if($defenderPlanet['crystal'] > $capacity) $booty['crystal'] = ($capacity / 2);
		else $booty['crystal'] = ($defenderPlanet['crystal'] / 2);
		$capacity -= $booty['crystal'];

		// Step 3
		if(($defenderPlanet['deuterium'] / 2) > $capacity) $booty['deuterium'] = $capacity;
		else $booty['deuterium'] = ($defenderPlanet['deuterium'] / 2);
		$capacity -= $booty['deuterium'];

		// Step 4
		$oldMetalBooty = $booty['metal'];
		if($defenderPlanet['metal'] > $capacity) $booty['metal'] += ($capacity / 2);
		else $booty['metal'] += ($defenderPlanet['metal'] / 2);
		$capacity -= $booty['metal'];
		$capacity += $oldMetalBooty;

		// Step 5
		if(($defenderPlanet['crystal'] / 2) > $capacity) $booty['crystal'] += $capacity;
		else $booty['crystal'] += ($defenderPlanet['crystal'] / 2);

		// Reset metal and crystal booty
		if($booty['metal'] > ($defenderPlanet['metal'] / 2)) $booty['metal'] = $defenderPlanet['metal'] / 2;
		if($booty['crystal'] > ($defenderPlanet['crystal'] / 2)) $booty['crystal'] = $defenderPlanet['crystal'] / 2;
	}

	/**
	 * Calculate debris and units
	 */

	$beginningUnits = $lastUnits = $debris = array('metal' => 0, 'crystal' => 0);
	$units = array();

	// Attacker
	foreach($roundData[0]['attackerData'] as $shipTypeID => $shipData) {
		$beginningUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
		$beginningUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
	}

	foreach($lastRoundData['attackerData'] as $shipTypeID => $shipData) {
		$lastUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
		$lastUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
	}

	$units['attacker'] = ($beginningUnits['metal'] - $lastUnits['metal']) + ($beginningUnits['crystal'] - $lastUnits['crystal']);
	$debris['metal'] += ($beginningUnits['metal'] - $lastUnits['metal']) * $game_config['flota_na_zlom'] / 100;
	$debris['crystal'] += ($beginningUnits['crystal'] - $lastUnits['crystal']) * $game_config['flota_na_zlom'] / 100;

	// Defender
	$beginningUnits = $lastUnits = $beginningDefenseUnits = $lastDefenseUnits = array('metal' => 0, 'crystal' => 0);

	foreach($roundData[0]['defenderData'] as $shipTypeID => $shipData) {
		if($shipTypeID < 400) {
			$beginningUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
			$beginningUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
		} else {
			$beginningDefenseUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
			$beginningDefenseUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
		}
	}

	foreach($lastRoundData['defenderData'] as $shipTypeID => $shipData) {
		if($shipTypeID < 400) {
			$lastUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
			$lastUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
		} else {
			$lastDefenseUnits['metal'] += $shipData['count'] * $pricelist[$shipTypeID]['metal'];
			$lastDefenseUnits['crystal'] += $shipData['count'] * $pricelist[$shipTypeID]['crystal'];
		}
	}

	$units['defender'] = ($beginningUnits['metal'] - $lastUnits['metal']) + ($beginningUnits['crystal'] - $lastUnits['crystal']);
	$units['defender'] += ($beginningDefenseUnits['metal'] - $lastDefenseUnits['metal']) + ($beginningDefenseUnits['crystal'] - $lastDefenseUnits['crystal']);

	$debris['metal'] += ($beginningUnits['metal'] - $lastUnits['metal']) * $game_config['flota_na_zlom'] / 100;
	$debris['crystal'] += ($beginningUnits['crystal'] - $lastUnits['crystal']) * $game_config['flota_na_zlom'] / 100;

	$debris['metal'] += ($beginningDefenseUnits['metal'] - $lastDefenseUnits['metal']) * $game_config['obrona_na_zlom'] / 100;
	$debris['crystal'] += ($beginningDefenseUnits['crystal'] - $lastDefenseUnits['crystal']) * $game_config['obrona_na_zlom'] / 100;

	/**
	 * Re-create defense
	 */

	$lostDefense = $recreatedDefense = array();
	foreach($roundData[0]['defenderData'] as $shipTypeID => $shipData) {
		if($shipTypeID < 400) continue;
		$lostDefense[$shipTypeID] = $shipData['count'] - $lastRoundData['defenderData'][$shipTypeID]['count'];
	}
	foreach($lostDefense as $defenseTypeID => $count) {
		$recreatedDefense[$defenseTypeID] = round($count * 0.7);
	}

	/**
	 * Create moon
	 */

	if(($debris['metal'] + $debris['crystal']) > 0 && !$defenderPlanet['moon']) {
		$chanceByBiggest = 0;
		$chanceByUnits = 0;

		if(round((500 - (1 / ($debris['metal'] + $debris['crystal']) * $game_config['biggest_debris'] * 250)) / 25) < 0) $chanceByBiggest = 0;
		else $chanceByBiggest = round((500 - (1 / ($debris['metal'] + $debris['crystal']) * $game_config['biggest_debris'] * 250)) / 25);

		if(round((350 - (0.6 / ($debris['metal'] + $debris['crystal']) * 150000 * 2500)) / 12) < 0) $chanceByUnits = 0;
		else $chanceByUnits = round((350 - (0.6 / ($debris['metal'] + $debris['crystal']) * 150000 * 2500)) / 12);

		$moonChance = ($chanceByBiggest + $chanceByUnits);

		$rand = rand(0, 99);
		if($moonChance > $rand && !$defenderPlanet['moon']) {
			$rand = rand(0, 500);
			$size = round(4750 + ($chanceByUnits * 115) + $rand);

			$rand = rand(20, 60);
			$temp = $defenderPlanet['temp_max'] - $rand;

			$moon = array('size' => $size,
					'temp' => $temp,
					'chance' => $moonChance);
		} else $moon = array('size' => null,
					'temp' => null,
					'chance' => $moonChance);

		// register new biggest debris
		if(($debris['metal'] + $debris['crystal']) > $game_config['biggest_debris'] && $user['authlevel'] == 0) {
			$sql = "UPDATE ugml".LW_N."_config
					SET config_value = '".($debris['metal'] + $debris['crystal'])."'
					WHERE config_name = 'biggest_debris'";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	} else $moon = array('size' => null,
				'temp' => null,
				'chance' => 0);

	return array('booty' => $booty,
		'debris' => $debris,
		'winner' => $winner,
		'lastRoundData' => $lastRoundData,
		'defender' => $defenderPlanet['id_owner'],
		'rounds' => $rounds,
		'units' => $units,
		'recreatedDefense' => $recreatedDefense,
		'moon' => $moon);
}


function generateReport($fleetData, $executeTime, $roundData, $attackerUser, $defenderUser, $combatData) {
	global $lang, $pricelist;

	$combat_rounds = "";

	$lang['attacker'] .= ' '.$attackerUser['username'].' ('.$fleetData['fleet_start_galaxy'].':'.$fleetData['fleet_start_system'].':'.$fleetData['fleet_start_planet'].')';
	$lang['defender'] .= ' '.$defenderUser['username'].' ('.$fleetData['fleet_end_galaxy'].':'.$fleetData['fleet_end_system'].':'.$fleetData['fleet_end_planet'].')';

	foreach($roundData as $round => $data) {
		if(isset($oldsummary)) $lang['summary'] = $oldsummary;
		$oldsummary = $lang['summary'];

		// attacker
		$lang['attacker_names'] = '';
		$lang['attacker_counts'] = '';
		$lang['attacker_weapons'] = '';
		$lang['attacker_shields'] = '';
		$lang['attacker_hull_platings'] = '';
		$attackerShipCount = 0;
		$attackerShotCount = 0;
		$attackerWeaponCount = 0;
		$attackerImbibedCount = 0;
		$capacity = 0;
		foreach($data['attackerData'] as $shipTypeID => $shipData) {
			$attackerShotCount += $roundData[$round + 1]['attackerData'][$shipTypeID]['shots'];
			$attackerImbibedCount += $roundData[$round + 1]['attackerData'][$shipTypeID]['imbibed'];

			if($shipData['count'] == 0) continue;

			$lang['attacker_names'] .= '<th>'.$lang['tech'][$shipTypeID].'</th>';
			$lang['attacker_counts'] .= '<th>'.$shipData['count'].'</th>';
			$lang['attacker_weapons'] .= '<th>'.round($shipData['weapon']).'</th>';
			$lang['attacker_shields'] .= '<th>'.round($shipData['shield']).'</th>';
			$lang['attacker_hull_platings'] .= '<th>'.round($shipData['hullPlating']).'</th>';
			$attackerShipCount += $shipData['count'];
			$attackerWeaponCount += $shipData['weapon'] * $roundData[$round + 1]['attackerData'][$shipTypeID]['shots'];
			$capacity += $shipData['count'] * $pricelist[$shipTypeID]['capacity'];
		}
		$lang['attacker_weapon'] = $attackerUser["military_tech"] * 10;
		$lang['attacker_shield'] = $attackerUser["defence_tech"] * 10;
		$lang['attacker_hull_plating'] = $attackerUser["shield_tech"] * 10;

		// defender
		$lang['defender_names'] = '';
		$lang['defender_counts'] = '';
		$lang['defender_weapons'] = '';
		$lang['defender_shields'] = '';
		$lang['defender_hull_platings'] = '';
		$defenderShipCount = 0;
		$defenderShotCount = 0;
		$defenderWeaponCount = 0;
		$defenderImbibedCount = 0;
		foreach($data['defenderData'] as $shipTypeID => $shipData) {
			$defenderShotCount += $roundData[$round + 1]['defenderData'][$shipTypeID]['shots'];
			$defenderImbibedCount += $roundData[$round + 1]['defenderData'][$shipTypeID]['imbibed'];

			if($shipData['count'] == 0) continue;

			$lang['defender_names'] .= '<th>'.$lang['tech'][$shipTypeID].'</th>';
			$lang['defender_counts'] .= '<th>'.$shipData['count'].'</th>';
			$lang['defender_weapons'] .= '<th>'.round($shipData['weapon']).'</th>';
			$lang['defender_shields'] .= '<th>'.round($shipData['shield']).'</th>';
			$lang['defender_hull_platings'] .= '<th>'.round($shipData['hullPlating']).'</th>';
			$defenderShipCount += $shipData['count'];
			$defenderWeaponCount += $shipData['weapon'] * $roundData[$round + 1]['defenderData'][$shipTypeID]['shots'];
		}

		$lang['defender_weapon'] = $defenderUser["military_tech"] * 10;
		$lang['defender_shield'] = $defenderUser["defence_tech"] * 10;
		$lang['defender_hull_plating'] = $defenderUser["shield_tech"] * 10;

		// summary
		$lang['summary'] = str_replace('{attacker_shot_count}', $attackerShotCount, $lang['summary']);
		$lang['summary'] = str_replace('{attacker_weapons_count}', round($attackerWeaponCount), $lang['summary']);
		$lang['summary'] = str_replace('{defender_imbided}', round($defenderImbibedCount), $lang['summary']);
		$lang['summary'] = str_replace('{defender_shot_count}', $defenderShotCount, $lang['summary']);
		$lang['summary'] = str_replace('{defender_weapons_count}', round($defenderWeaponCount), $lang['summary']);
		$lang['summary'] = str_replace('{attacker_imbided}', round($attackerImbibedCount), $lang['summary']);

		if($defenderShipCount == 0) {
			$templateName = 'combat_last_round_defender';
			$lang['winner'] = $lang['winner_attacker'];
			$lang['booty'] = str_replace('{metal}', round($combatData['booty']['metal']), $lang['booty']);
			$lang['booty'] = str_replace('{crystal}', round($combatData['booty']['crystal']), $lang['booty']);
			$lang['booty'] = str_replace('{deuterium}', round($combatData['booty']['deuterium']), $lang['booty']);
		} else {
			if($attackerShipCount == 0) {
				$templateName = 'combat_last_round_attacker';
				$lang['winner'] = $lang['winner_defender'];
				$lang['booty'] = '';
			} else {
				$templateName = 'combat_round';
				$lang['winner'] = $lang['winner_draw'];
				if($round == 6) {
					$templateName = 'combat_last_round';
					$lang['booty'] = '';
				}
			}
		}

		$combat_rounds .= parsetemplate(gettemplate($templateName), $lang);

	}
	$lang['reportTime'] = str_replace('{time}', date('r', $fleetData['fleet_start_time']), $lang['header']);
	$lang['debris'] = str_replace('{metal}', $combatData['debris']['metal'], $lang['debris']);
	$lang['debris'] = str_replace('{crystal}', $combatData['debris']['crystal'], $lang['debris']);

	// encrypt data
	/*$td = mcrypt_module_open('tripledes', '', 'ecb', '');
	$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, 'encrypteddebugdata', $iv);

	$lang['debugData'] = "<br><br>#### DEBUG ####<br><br>Berechnungszeit:<br>".$executeTime."<br><br>Data1:<br>";
	$lang['debugData'] .= "<textarea name=\"debug1\" cols=\"50\" rows=\"10\" readonly>".base64_encode(mcrypt_generic($td, serialize($roundData)))."</textarea>";
	$lang['debugData'] .= "<br><br>Data2:<br>";
	$lang['debugData'] .= "<textarea name=\"debug2\" cols=\"50\" rows=\"10\" readonly>".base64_encode(mcrypt_generic($td, serialize($combatData)))."</textarea>";
	$lang['debugData'] .= "<br><br>Data3:<br>";
	$lang['debugData'] .= "<textarea name=\"debug3\" cols=\"50\" rows=\"10\" readonly>".base64_encode(mcrypt_generic($td, serialize($fleetData)))."</textarea>";

	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);*/

	$lang['combat_rounds'] = $combat_rounds;

	$lang['units'] = str_replace('{attacker}', $combatData['units']['attacker'], $lang['units']);
	$lang['units'] = str_replace('{defender}', $combatData['units']['defender'], $lang['units']);

	if($combatData['moon']['chance'] !== 0) {
		$lang['moon'] = str_replace('{chance}', $combatData['moon']['chance'], $lang['moon']);
		if($combatData['moon']['size'] === null) $lang['moon_creation'] = '';
	} else $lang['moon'] = $lang['moon_creation'] = '';

	// create defense re-creation string
	foreach($combatData['recreatedDefense'] as $defenseTypeID => $count) {
		if(!isset($recreatedDefense)) $recreatedDefense = $count.' '.$lang['tech'][$defenseTypeID];
		else $recreatedDefense .= ', '.$count.' '.$lang['tech'][$defenseTypeID];
	}
	if(isset($recreatedDefense)) $lang['recreatedDefense'] = str_replace('{defense}', $recreatedDefense, $lang['recreatedDefense']);
	else $lang['recreatedDefense'] = '';

	return parsetemplate(gettemplate('combat'), $lang);
}

function saveData(&$fleetData, $combatData, $report) {
	global $resource, $lang;

	/**
	 * Apply defender ships and booty
	 */

	$ships = "";
	foreach($combatData['lastRoundData']['defenderData'] as $shipTypeID => $shipData) {
		$count = $shipData['count'];
		if(isset($combatData['recreatedDefense'][$shipTypeID])) $count += $combatData['recreatedDefense'][$shipTypeID];
		if(isset($count)) $ships .= $resource[$shipTypeID]." = '".$count."', ";
	}

	doquery("UPDATE {{table}} SET
		".$ships."
		metal = metal - '".$combatData['booty']['metal']."',
		crystal = crystal - '".$combatData['booty']['crystal']."',
		deuterium = deuterium - '".$combatData['booty']['deuterium']."'
		WHERE id = '".$fleetData['endPlanetID']."' LIMIT 1", 'planets');

	$ships = "";
	$attackerShipsCount = 0;
	foreach($combatData['lastRoundData']['attackerData'] as $shipTypeID => $shipData) {
		$ships .= $shipTypeID.",".$shipData['count'].";";
		$attackerShipsCount += $shipData['count'];
	}

	/**
	 * Update fleet of the attacker
	 */
	doquery("UPDATE {{table}} SET
		fleet_amount = '".$shipData['count']."',
		fleet_array = '".$ships."',
		fleet_resource_metal = fleet_resource_metal + '".$combatData['booty']['metal']."',
		fleet_resource_crystal = fleet_resource_crystal + '".$combatData['booty']['crystal']."',
		fleet_resource_deuterium = fleet_resource_deuterium + '".$combatData['booty']['deuterium']."',
		fleet_mess = '1'
		WHERE fleet_id = '".$fleetData['fleet_id']."' LIMIT 1", 'fleets');

	$fleetData['fleet_amount'] = $combatData['count'];
	$fleetData['fleet_array'] = $ships;
	$fleetData['fleet_resource_metal'] = $combatData['booty']['metal'];
	$fleetData['fleet_resource_crystal'] = $combatData['booty']['crystal'];
	$fleetData['fleet_resource_deuterium'] = $combatData['booty']['deuterium'];
	$fleetData['fleet_mess'] = 1;

	/**
	 * Apply debris
	 */
	/*if(WCF::getUser()->userID != 143) {
		doquery("UPDATE {{table}} SET
			metal = metal + '".$combatData['debris']['metal']."',
			crystal = crystal + '".$combatData['debris']['crystal']."'
			WHERE galaxy = '".$fleetData['fleet_end_galaxy']."'
			AND system = '".$fleetData['fleet_end_system']."'
			AND planet = '".$fleetData['fleet_end_planet']."' LIMIT 1", 'galaxy');
	} else {*/
		require_once(LW_DIR.'lib/data/planet/Debris.class.php');

		$debrisID = Debris::exists($fleetData['fleet_end_galaxy'], $fleetData['fleet_end_system'], $fleetData['fleet_end_planet']);
		if($debrisID) {
			Planet::getInstance($debrisID)->addRessources($combatData['debris']['metal'], $combatData['debris']['crystal']);
		} else {
			Debris::create($fleetData['fleet_end_galaxy'], $fleetData['fleet_end_system'], $fleetData['fleet_end_planet'], $combatData['debris']['metal'], $combatData['debris']['crystal']);
		}
	//}

	/**
	 * Delete fleet of the attacker if it have been destroyed
	 */
	if($combatData['winner'] == 'defender') {
		doquery("DELETE FROM {{table}} WHERE fleet_id=".$fleetData["fleet_id"],'fleets');
	}

	/**
	 * Insert moon
	 */
	if($combatData['moon']['size'] !== null) {
		$fields = floor(pow(($combatData['moon']['size'] / 1000), 2));

		$sql = "INSERT INTO ugml".LW_N."_planets
				SET name = 'Mond',
					id_owner = '".$combatData['defender']."',
					galaxy = '".$fleetData['fleet_end_galaxy']."',
					system = '".$fleetData['fleet_end_system']."',
					planet = '".$fleetData['fleet_end_planet']."',
					last_update = '".$fleetData['fleet_start_time']."',
					image = 'mond',
					diameter = '".$combatData['moon']['size']."',
					field_max = '".$fields."',
					temp_min = '".($combatData['moon']['temp'] - 40)."',
					temp_max = '".$combatData['moon']['temp']."',
					className = 'UserMoon',
					planet_type = '3'";
		WCF::getDB()->registerShutdownUpdate($sql);

		$sql = "UPDATE ugml".LW_N."_planets
				SET moon = '1'
				WHERE id = '".$fleetData['endPlanetID']."'";
		WCF::getDB()->registerShutdownUpdate($sql);

	}

	/**
	 * Save report
	 */
	$rid = md5($report);

	if($combatData['winner'] == 'defender' && $combatData['rounds'] == 1) $firstRoundDestroyedAttacker = true;
	else $firstRoundDestroyedAttacker = false;

	doquery("INSERT INTO {{table}} SET
		time = '".$fleetData['fleet_start_time']."',
		id_owner1 = '".$fleetData['fleet_owner']."',
		id_owner2 = '".$combatData['defender']."',
		rid = '".$rid."',
		a_zestrzelona  = '".$firstRoundDestroyedAttacker."',
		raport = '".mysql_escape_string($report)."'", 'rw');

	$message = "<a class=\"thickbox\" href=\"rw.php?raport=".$rid."&keepThis=true&TB_iframe=true&height=400&width=500\"><font color=\"red\">".$lang['combat_report']." [".$fleetData['fleet_end_galaxy'].":".$fleetData['fleet_end_system'].":".$fleetData['fleet_end_planet']."] (V:".$combatData['units']['defender']."), A:(".$combatData['units']['attacker'].")</font></a>";

	doquery("INSERT INTO {{table}} SET
		message_owner = '".$fleetData['fleet_owner']."',
		message_sender = '',
		message_time = '".$fleetData['fleet_start_time']."',
		message_type = '3',
		message_from = '".$lang['combat_report_sender']."',
		message_subject = '".$lang['combat_report_subject']."',
		message_text = '".$message."'", 'messages');
	doquery("UPDATE {{table}} SET new_message = new_message + 1 WHERE id = '".$fleetData['fleet_owner']."'", 'users');

	doquery("INSERT INTO {{table}} SET
		message_owner = '".$combatData['defender']."',
		message_sender = '',
		message_time = '".$fleetData['fleet_start_time']."',
		message_type = '3',
		message_from = '".$lang['combat_report_sender']."',
		message_subject = '".$lang['combat_report_subject']."',
		message_text = '".$message."'", 'messages');
	doquery("UPDATE {{table}} SET new_message = new_message + 1 WHERE id = '".$combatData['defender']."'", 'users');
}

?>