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
 //overview.php

define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

if(!check_user()){ header("Location: login.$phpEx"); die();}

includeLang('imperium');
includeLang('tech');

include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);//Esta funcion permite cambiar el planeta actual.

$planetsrow = doquery("SELECT * FROM {{table}} WHERE id_owner={$user['id']}",'planets');
//$galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet={$planetrow['id']}",'galaxy');
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
check_field_current($planetrow);

$features = unserialize($user['diliziumFeatures']);

if(@$features['imperium'] < time()) exit;

/*
  EMpezamos :S
*/
$planet = array();
$parse = $lang;

while($p = mysql_fetch_array($planetsrow)){
	$planet[] = $p;
}

$parse['mount'] = count($planet)+1;
//primera tabla, con las imagenes y coordenadas

$row = gettemplate('imperium_row');
$row2 = gettemplate('imperium_row2');

foreach ($planet as $p) {


//{file_images}
$data['text'] = '<a href="game/index.php?page=Overview&amp;cp='.$p['id'].'&amp;re=0">
<img src="'.$dpath.'planeten/small/s_'.$p['image'].'.jpg" border="0" height="71" width="75">
</a>';
$parse['file_images'] .=  parsetemplate($row, $data);

//{file_names}
$data['text'] = $p['name'];
$parse['file_names'] .=  parsetemplate($row2, $data);

//{file_coordinates}
$data['text'] = "[<a href=\"galaxy.php?g={$p['galaxy']}&s={$p['system']}\">{$p['galaxy']}:{$p['system']}:{$p['planet']}</a>]";
$parse['file_coordinates'] .=  parsetemplate($row2, $data);

//{file_fields}
$data['text'] = $p['field_current'].'/'.$p['field_max'];
$parse['file_fields'] .=  parsetemplate($row2, $data);

//{file_metal}
$data['text'] = '<a href="resources.php?cp='.$p['id'].'&amp;re=0&amp;planettype='.$p['planet_type'].'">
'.pretty_number($p['metal']).'</a> / '.pretty_number($p['metal_perhour']);
$parse['file_metal'] .=  parsetemplate($row2, $data);

//{file_crystal}
$data['text'] = '<a href="resources.php?cp='.$p['id'].'&amp;re=0&amp;planettype='.$p['planet_type'].'">
'.pretty_number($p['crystal']).'</a> / '.pretty_number($p['crystal_perhour']);
$parse['file_crystal'] .=  parsetemplate($row2, $data);

//{file_deuterium}
$data['text'] = '<a href="resources.php?cp='.$p['id'].'&amp;re=0&amp;planettype='.$p['planet_type'].'">
'.pretty_number($p['deuterium']).'</a> / '.pretty_number($p['deuterium_perhour']);
$parse['file_deuterium'] .=  parsetemplate($row2, $data);

//{file_energy}
$data['text'] = pretty_number($p['energy_max']-$p['energy_used']).' / '.pretty_number($p['energy_max']);
$parse['file_energy'] .=  parsetemplate($row2, $data);

$moreBuildings = unserialize($p['moreBuildings']);
if(!is_array($moreBuildings)) $moreBuildings = array();
foreach ( $resource as $i => $res ) {

	if(in_array($i,$reslist['build'])) {
		$data['text'] = ($p[$resource[$i]]==0)?'-':"<a href=\"buildings.php?cp={$p['id']}&amp;re=0&amp;planettype={$p['planet_type']}\">{$p[$resource[$i]]}</a>";
		// get construction
		if($p['b_building_id'] == $i) {
			$p[$resource[$i]]++;
			$data['text'] .= '&nbsp;<font color="magenta">'.$p[$resource[$i]].'</font>';
		}
		// more buildings
		foreach($moreBuildings as $buildingID) {
			if($buildingID == $i) {
				$p[$resource[$i]]++;
				$data['text'] .= '&nbsp;<font color="sandybrown">('.$p[$resource[$i]].')</font>';
			}
		}

	} elseif(in_array($i,$reslist['tech']))
		$data['text'] = ($user[$resource[$i]]==0)?'-':"<a href=\"buildings.php?mode=tech&cp={$p['id']}&amp;re=0&amp;planettype={$p['planet_type']}\">{$user[$resource[$i]]}</a>";
	elseif(in_array($i,$reslist['fleet']))
		$data['text'] = ($p[$resource[$i]]==0)?'-':"<a href=\"buildings.php?mode=fleet&cp={$p['id']}&amp;re=0&amp;planettype={$p['planet_type']}\">{$p[$resource[$i]]}</a>";
	elseif(in_array($i,$reslist['defense']))
		$data['text'] = ($p[$resource[$i]]==0)?'-':"<a href=\"buildings.php?mode=defense&cp={$p['id']}&amp;re=0&amp;planettype={$p['planet_type']}\">{$p[$resource[$i]]}</a>";

	$r[$i] .=  parsetemplate($row2, $data);

}



}

//{building_row}
foreach ( $reslist['build'] as $a => $i) {

	$data['text'] = $lang['tech'][$i];
	$parse['building_row'] .=  "<tr>".parsetemplate($row2, $data).$r[$i]."</tr>";

}
//{technology_row}
foreach ( $reslist['tech'] as $a => $i) {

	$data['text'] = $lang['tech'][$i];
	$parse['technology_row'] .=  "<tr>".parsetemplate($row2, $data).$r[$i]."</tr>";

}

//{fleet_row}
foreach ( $reslist['fleet'] as $a => $i) {

	$data['text'] = $lang['tech'][$i];
	$parse['fleet_row'] .=  "<tr>".parsetemplate($row2, $data).$r[$i]."</tr>";

}

//{defense_row}
foreach ( $reslist['defense'] as $a => $i) {

	$data['text'] = $lang['tech'][$i];
	$parse['defense_row'] .=  "<tr>".parsetemplate($row2, $data).$r[$i]."</tr>";

}

$page .= parsetemplate(gettemplate('imperium_table'), $parse);


display($page,$lang['Imperium'],false);

// Created by Perberos. All rights reserved (C) 2006
?>
