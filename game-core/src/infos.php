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

define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

if(!check_user()){ header("Location: login.".$phpEx); die();}

include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);

$planetrow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_planet']}",'planets',true);
$factor = $game_config['resource_multiplier'];

includeLang('tech');
includeLang('infos');

$gid = intval($_GET['gid']);
$info = $lang['info'][$gid];
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

if($gid>=1 &&$gid<=44){$TitleClass = str_replace('%n',$lang['tech'][0],$lang['Information_on']);}
elseif($gid>=106 &&$gid<=199){$TitleClass = str_replace('%n',$lang['tech'][100],$lang['Information_on']);}
elseif($gid>=202 &&$gid<=214){$TitleClass = str_replace('%n',$lang['tech'][200],$lang['Information_on']);}
elseif($gid>=401 &&$gid<=503){$TitleClass = str_replace('%n',$lang['tech'][400],$lang['Information_on']);}

$parse = array();
$parse['TitleClass'] = $TitleClass;
$parse['Name'] = $lang['Name'];
$parse['tech'] = $info['name'];
$parse['dpath'] = $dpath;
$parse['gid'] = $gid;
$parse['description'] = nl2br($info['description']);

/**
 * calculates output and needed energy for the main buildings (1, 2, 3, 4, 12)
 */
function getressource($gid, $level)
{
	global $planetrow, $resource, $factor;
	switch($gid)
	{
		case 1:
			$res['res'] = floor((30 * ($planetrow[$resource[1]]+$level) *  pow((1.1),($planetrow[$resource[1]]+$level)))*$factor);
			$res['energy'] = -ceil((10 * ($planetrow[$resource[1]]+$level) *  pow((1.1),($planetrow[$resource[1]]+$level)))*$factor);
			break;
		case 2:	
			$res['res'] = floor((20 * ($planetrow[$resource[2]]+$level) *  pow((1.1),($planetrow[$resource[2]]+$level)))*$factor);
			$res['energy'] = -ceil((10 * ($planetrow[$resource[2]]+$level) *  pow((1.1),($planetrow[$resource[2]]+$level)))*$factor);
			break;
		case 3:
			$res['res'] = floor((10 * ($planetrow[$resource[3]]+$level) *  pow((1.1),($planetrow[$resource[3]]+$level))*(-0.002*$planetrow["max_tem"]+1.28)*$factor));
			$res['energy'] = -ceil((30 * ($planetrow[$resource[3]]+$level) *  pow((1.1),($planetrow[$resource[3]]+$level)))*$factor);
			break;
		case 4:
			$res['energy'] = floor((20 * ($planetrow[$resource[4]]+$level) *  pow((1.1),($planetrow[$resource[4]]+$level))*$factor));
			break;
		case 12:
			$res['res'] = floor((50 * ($planetrow[$resource[12]]+$level) *  pow((1.1),($planetrow[$resource[12]]+$level)))*$factor);
			$res['energy'] = ceil((10 * ($planetrow[$resource[12]]+$level) *  pow((1.1),($planetrow[$resource[12]]+$level)))*$factor);
			break;
	}
	return $res;
}

/**
 * generates info page
 */
$array = array(1,2,3,4,12);
if(in_array($gid, $array)) {
	$start = $planetrow[$resource[$gid]];
	$dif = getressource($gid, $level);
	$level = -2;
	if($start <= 1) {
		$level = 0;
	}elseif($start == 2){
		$level = -1;
	}

	switch($gid) {
		case 4:
			$page = '<table><tr>
				<td class="c">Level</td>
				<td class="c">Produktion/Stunde</td>
				<td class="c">Differenz</td></tr>';
			break;
		case 12:
			$page = '<table><tr>
				<td class="c">Level</td>
				<td class="c">Produktion/Stunde</td>
				<td class="c">Differenz</td>
				<td class="c">Benötigt Deut.</td>
				<td class="c">Differenz</td></tr>';
			break;
		default:
			$page = '<table><tr>
				<td class="c">Level</td>
				<td class="c">Produktion/Stunde</td>
				<td class="c">Differenz</td>
				<td class="c">Energiebilanz</td>
				<td class="c">Differenz</td></tr>';
			break;
	}
	
	for($i=1; $i<=15; $i++) {
		$res = getressource($gid, $level);
		$page .= '<tr><th>';
		if(($planetrow[$resource[$gid]]+$level)<$planetrow[$resource[$gid]]) {
			$page .= $planetrow[$resource[$gid]]+$level.'</th><th>';
			if($gid!=4)	{
				$page .= pretty_number($res['res']).'</th><th>';
				$page .= colorNumber(pretty_number($res['res']-$dif['res'])).'</th><th>';
			}
			$page .= pretty_number($res['energy']).'</th><th>';
			if($gid==12) {
				$page .= colorNumber(pretty_number(-($res['energy']-$dif['energy']))).'</th><th>';
			}else{
				$page .= colorNumber(pretty_number($res['energy']-$dif['energy'])).'</th><th>';
			}
		}elseif(($planetrow[$resource[$gid]]+$level)>$planetrow[$resource[$gid]]){
			$page .= $planetrow[$resource[$gid]]+$level.'</th><th>';
			if($gid!=4)	{
				$page .= pretty_number($res['res']).'</th><th>';
				$page .= colorNumber(pretty_number($res['res']-$dif['res'])).'</th><th>';
			}
			$page .= pretty_number($res['energy']).'</th><th>';
			if($gid==12) {
				$page .= colorNumber(pretty_number(-($res['energy']-$dif['energy']))).'</th><th>';
			}else{
				$page .= colorNumber(pretty_number($res['energy']-$dif['energy'])).'</th><th>';
			}
		}else{
			$page .= '<font color="#FF0000">';
			$page .= $planetrow[$resource[$gid]]+$level.'</font></th><th>';
			if($gid!=4)	{
				$page .= pretty_number($res['res']).'</th><th>';
				$page .= pretty_number($res['res']-$dif['res']).'</th><th>';
			}
			$page .= pretty_number($res['energy']).'</th><th>';
			$page .= pretty_number($res['energy']-$dif['energy']).'</th><th>';
		}
		$page .= '</th></tr>';
		$level++;
	}
	$page .= '</table>';
}

/**
 * knock down link for buildings
 */
if($gid < 100 && LWCore::getPlanet()->{$resource[$gid]} >= 1 && $gid != 33 && $gid != 41) {
	$parse['knockDown'] = '<span><a href="game/index.php?action=BuildingKnockDown&buildingID='.$gid.'">Abrei&szlig;en</a></span>';
} else if($gid == 502 && LWCore::getPlanet()->interceptor_misil > 1) {

	$parse['knockDown'] = '<form name="interceptormissileknockdown" action="game/index.php?action=InterceptorMissileKnockDown" method="post">
			<input name="interceptorMissiles" type="text" size="2" /><input type="submit" value="Abfangraketen abrei&szlig;en" />
			</form>';
} else $parse['knockDown'] = '';

// refinery
if($gid == 13) {
	$page .= '<fieldset>
		<legend>
			Produktion
		</legend><form name="refineryProductionForm" action="game/index.php?action=SetRefineryProduction" method="post">';
	if(LWCore::getPlanet()->refineryProductionChange > time() - 60 * 60 * 48) {
		$page .= '<p class="error">Wechsel erst am '.DateUtil::formatTime(null, LWCore::getPlanet()->refineryProductionChange + 60 * 60 * 48).' möglich!</p>';
	}
	else {
		$select = '<select name="production" onchange="if(confirm(\'Willst du wirklich die Raffinerie-Produktion festsetzen? Sie lässt sich danach für 48 Stunden nicht mehr verändern!\')) document.forms.refineryProductionForm.submit()">';
		$select .= '<option value="metal"'.((LWCore::getPlanet()->refineryProduction == 'metal') ? ' selected = "selected"' : '').'>Metall</option>';
		$select .= '<option value="crystal"'.((LWCore::getPlanet()->refineryProduction == 'crystal') ? ' selected = "selected"' : '').'>Kristall</option>';
		$select .= '<option value="deuterium"'.((LWCore::getPlanet()->refineryProduction == 'deuterium') ? ' selected = "selected"' : '').'>Deuterium</option>';
		$select .= '</select>';
		$page .= 'Ich möchte mit Hilfe der Raffinerie die '.$select.'-Produktion steigern.';
	}
	
	$page .= '</form></fieldset>';
}

$parse['datas'] = $page;
$page = parsetemplate(gettemplate('infos_body'), $parse);

display($page, $lang['Information']);

?>
