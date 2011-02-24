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

if(!check_user()){ header("Location: login.$phpEx"); die();}

includeLang('resources');
includeLang('tech');

include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);//Esta funcion permite cambiar el planeta actual.

$planetrow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_planet']}",'planets',true);
//$galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet={$planetrow['id']}",'galaxy',true);
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
check_field_current($planetrow);


/*
  Peque�a comprovacion para los almacenes `metal_max`,`crystal_max`,`deuterium_max`
*/
/*$u = 1000000;//Balor basico
$planetrow['metal_max'] = floor($u* pow(1.5,$planetrow[$resource[22]]));
$planetrow['crystal_max'] = floor($u* pow(1.5,$planetrow[$resource[23]]));
$planetrow['deuterium_max'] = floor($u* pow(1.5,$planetrow[$resource[24]]));*/

$query = '';
$percents = array(0,10,20,30,40,50,60,70,80,90,100);
if($_POST && $planetrow['planet'] >= 0 && $planetrow['planet'] <= 15){
	foreach($_POST as $a => $b){
		/*
		  Evitamos si se envian algunos datos innecesarios.
		*/
		if(isset($planetrow["{$a}_porcent"])){
			if(!in_array($b, $percents)) $b = 0;
			$b = $b/10;
			$planetrow["{$a}_porcent"] = $b;
			$query .= ",`{$a}_porcent`='$b'";
		}
	}
}

$parse = $lang;
/*
  Esta array contiene todos los que pueden generar recursos o energia.
  Servira para hacer un loop.
*/
$res_ab = array(1,2,3,4,12,212,13);
//la template row
$row = gettemplate('resources_row');
//looooooooop
$parse['production_level'] = 100;
//
//  A futuro, se cambiara la forma de plasmar los valores en una array. la cual se plasmara en una plantilla
//  armada previamente, segun los datos a plasmar
//
//produccion total
if($planetrow["energy_max"]==0&&$planetrow['energy_used']<0){
	$post_porcent=0;
}elseif($planetrow["energy_max"]>0&&$planetrow['energy_used']>$planetrow["energy_max"]){
	$post_porcent = floor(($planetrow["energy_max"])/$planetrow['energy_used']*100);
}else{$post_porcent=100;}
if($post_porcent>100){$post_porcent=100;}
//
// Ingresos basicos.
//
$planetrow["metal_perhour"]=$planetrow["crystal_perhour"]=$planetrow["deuterium_perhour"]=$planetrow["energy_max"]=0;

if(LWCore::getPlanet()->planet_type == 1) {
	$parse['metal_basic_income'] = $game_config['metal_basic_income']/** $game_config['resource_multiplier']*/;
	$parse['crystal_basic_income'] = $game_config['crystal_basic_income']/** $game_config['resource_multiplier']*/;
	$parse['deuterium_basic_income'] = $game_config['deuterium_basic_income']/** $game_config['resource_multiplier']*/;
	$parse['energy_basic_income'] = $game_config['energy_basic_income']/** $game_config['resource_multiplier']*/;
} else $parse['metal_basic_income'] = $parse['crystal_basic_income'] = $parse['deuterium_basic_income'] = $parse['energy_basic_income'] = 0;
//reset de algunos datos
$planetrow["energy_used"]=0;
$parse['resource_row']='';//un peque�o fix ;P
foreach($res_ab as $a){
	if($planetrow[$resource[$a]]>0&&isset($production[$a])){
		/*
		  Supuestamente, los datos de las formulas, estan en la array $production.
		  Ademas de los factores. etc.
		*/
		$r = array();
		$r['type'] = $lang['tech'][$a];
		//excluimos las naves con el numero mayor a 200 ;)
		$r['level'] =($a>200)?$lang['quantity']:$lang['level'];
		$r['level_type'] = $planetrow[$resource[$a]];
		/*
		  Los datos de las formulas se almacenan en la array $production
		  Se hubica en /includes/vars.php
		*/
		$metal = floor(eval($production[$a]["formular"]["metal"])* $game_config['resource_multiplier']);
		$crystal = floor(eval($production[$a]["formular"]["crystal"])* $game_config['resource_multiplier']);
		$deuterium = floor(eval($production[$a]["formular"]["deuterium"])* $game_config['resource_multiplier']);
		$energy = floor(eval($production[$a]["formular"]["energy"])* $game_config['resource_multiplier']);
		$metal = $crystal = $deuterium = $energy = 0;
		extract(Spec::getSpecObj($a)->getProduction(LWCore::getPlanet()->getProductionHandler()->getProductorObject('resource')));
		$metal *= $game_config['resource_multiplier'] * 0.1 * $planetrow[$resource[$a]."_porcent"];
		$crystal *= $game_config['resource_multiplier'] * 0.1 * $planetrow[$resource[$a]."_porcent"];
		$deuterium *= $game_config['resource_multiplier'] * 0.1 * $planetrow[$resource[$a]."_porcent"];
		$energy *= $game_config['resource_multiplier'] * 0.1 * $planetrow[$resource[$a]."_porcent"];
		
		if(WCF::getUser()->urlaubs_modus) $metal = $crystal = $deuterium = 0;
		$planetrow["metal_perhour"] += $metal;
		$planetrow["crystal_perhour"] += $crystal;
		$planetrow["deuterium_perhour"] += $deuterium;
		if($energy>0){$planetrow["energy_max"] += $energy;}
		else{$planetrow["energy_used"] -= $energy;}
		//es una peque�a suma de porcentajes
		$metal=$metal* 0.01 * $post_porcent;
		$crystal = $crystal* 0.01 * $post_porcent;
		$deuterium = $deuterium* 0.01 * $post_porcent;
		$energy2 = $energy* 0.01 * $post_porcent;
		$r["metal_type"] = pretty_number($metal);
		$r["crystal_type"] = pretty_number($crystal);
		$r["deuterium_type"] = pretty_number($deuterium);
		$r["energy_type"] = pretty_number($energy);
		//Nombre interno
		$r['name'] = $resource[$a];
		//Se establece el porcentaje
		eval('$r["porcent"] = $planetrow["'.$resource[$a].'_porcent"];');
		//_porcent
		//Esto muestra las opciones de porcentaje.
		//
		for ( $i = 10; $i >= 0; $i-- ) {
			$e = $i*10;
			if($i == $r["porcent"]){
				$s=' selected=selected';
			}else{$s='';}
			$r['option'] .= "<option value=\"{$e}\"{$s}>{$e}%</option>";
		}
		//Esto solo colorea los valores.
		$r["metal_type"] = colorNumber($r["metal_type"]);
		$r["crystal_type"] = colorNumber($r["crystal_type"]);
		$r["deuterium_type"] = colorNumber($r["deuterium_type"]);
		$r["energy_type"] = colorNumber($r["energy_type"]);
		//template
		$parse['resource_row'] .= parsetemplate($row, $r);
	}
}

//ahora se actualiza la
/*
  Datos iniciales y porcentaje de produccion
*/
{//Nombre del planeta
//el nombre del planeta
$parse['Production_of_resources_in_the_planet'] =
	str_replace('%s',$planetrow['name'],$lang['Production_of_resources_in_the_planet']);
//produccion total
if($planetrow["energy_max"]==0&&$planetrow['energy_used']<0){
	$parse['production_level']=0;
}elseif($planetrow["energy_max"]>0&&$planetrow['energy_used']>$planetrow["energy_max"]){
	$parse['production_level'] = floor(($planetrow["energy_max"])/$planetrow['energy_used']*100);

}elseif($planetrow["energy_max"]==0&&$planetrow['energy_used']>$planetrow["energy_max"]){
	$parse['production_level'] = 0;

}else{$parse['production_level']=100;}
if($parse['production_level']>100){$parse['production_level']=100;}

//Datos basicos.
if(LWCore::getPlanet()->planet_type == 1) {
	$parse['metal_basic_income'] = $game_config['metal_basic_income']/** $game_config['resource_multiplier']*/;
	$parse['crystal_basic_income'] = $game_config['crystal_basic_income']/** $game_config['resource_multiplier']*/;
	$parse['deuterium_basic_income'] = $game_config['deuterium_basic_income']/** $game_config['resource_multiplier']*/;
	$parse['energy_basic_income'] = $game_config['energy_basic_income']/** $game_config['resource_multiplier']*/;
} else $parse['metal_basic_income'] = $parse['crystal_basic_income'] = $parse['deuterium_basic_income'] = $parse['energy_basic_income'] = 0;
}

$limits = LWCore::getPlanet()->getProductionHandler()->getProductorObject('resource')->getLimits('metal');
$limit = $limits[1];
$planetrow["metal_max"] = $limit;
//Metal maximo
if($limit<$planetrow["metal"]){
	$parse['metal_max'] = '<font color="#ff0000">';
}else{
	$parse['metal_max'] = '<font color="#00ff00">';
}
$parse['metal_max'] .= pretty_number($limit/1000)." {$lang['k']}</font>";


$limits = LWCore::getPlanet()->getProductionHandler()->getProductorObject('resource')->getLimits('crystal');
$limit = $limits[1];
$planetrow["crystal_max"] = $limit;
//Cristal maximo
if($limit<$planetrow["crystal"]){
	$parse['crystal_max'] = '<font color="#ff0000">';
}else{
	$parse['crystal_max'] = '<font color="#00ff00">';
}
$parse['crystal_max'] .= pretty_number($limit/1000)." {$lang['k']}";


$limits = LWCore::getPlanet()->getProductionHandler()->getProductorObject('resource')->getLimits('deuterium');
$limit = $limits[1];
$planetrow["deuterium_max"] = $limit;
//Deuterio maximo
if($limit<$planetrow["deuterium"]){
	$parse['deuterium_max'] = '<font color="#ff0000">';
}else{
	$parse['deuterium_max'] = '<font color="#00ff00">';
}
$parse['deuterium_max'] .= pretty_number($limit/1000)." {$lang['k']}";
//Total de los recursos
$parse['metal_total'] = colorNumber(floor(($planetrow['metal_perhour']*0.01*$parse['production_level'])+$parse['metal_basic_income']));
$parse['crystal_total'] = colorNumber(floor(($planetrow['crystal_perhour']*0.01*$parse['production_level'])+$parse['crystal_basic_income']));
$parse['deuterium_total'] = colorNumber(floor(($planetrow['deuterium_perhour']*0.01* $parse['production_level'])+$parse['deuterium_basic_income']));
$parse['energy_total'] = colorNumber(floor(($planetrow['energy_max']-$planetrow["energy_used"]))+$parse['energy_basic_income']);
//------------------->//$planetrow['energy_used']= $planetrow['energy_used']+$planetrow["energy_max"];

/*

  Valores estadisticos.

*/
{//tabla de valores extendidos
//colores de la tabla... no muy necesario creo yo...
$parse['daily_metal'] = round(($planetrow["metal_perhour"])*24*0.01*$parse['production_level']+$parse['metal_basic_income']*24);
$parse['weekly_metal'] = round($planetrow["metal_perhour"]*24*7*0.01*$parse['production_level']+$parse['metal_basic_income']*24*7);
$parse['monthly_metal'] = round($planetrow["metal_perhour"]*24*30*0.01*$parse['production_level']+$parse['metal_basic_income']*24*30);
$parse['daily_crystal'] = round($planetrow["crystal_perhour"]*24*0.01*$parse['production_level']+$parse['crystal_basic_income']*24);
$parse['weekly_crystal'] = floor($planetrow["crystal_perhour"]*24*7*0.01*$parse['production_level']+$parse['crystal_basic_income']*24*7);
$parse['monthly_crystal'] = floor($planetrow["crystal_perhour"]*24*30*0.01*$parse['production_level']+$parse['crystal_basic_income']*24*30);
$parse['daily_deuterium'] = floor($planetrow["deuterium_perhour"]*24*0.01*$parse['production_level']+$parse['deuterium_basic_income']*24);
$parse['weekly_deuterium'] = floor($planetrow["deuterium_perhour"]*24*7*0.01*$parse['production_level']+$parse['deuterium_basic_income']*24*7);
$parse['monthly_deuterium'] = floor($planetrow["deuterium_perhour"]*24*30*0.01*$parse['production_level']+$parse['deuterium_basic_income']*24*30);
$parse['daily_metal'] = colorNumber(pretty_number($parse["daily_metal"]));
$parse['weekly_metal'] = colorNumber(pretty_number($parse["weekly_metal"]));
$parse['monthly_metal'] = colorNumber(pretty_number($parse["monthly_metal"]));
$parse['daily_crystal'] = colorNumber(pretty_number($parse["daily_crystal"]));
$parse['weekly_crystal'] = colorNumber(pretty_number($parse["weekly_crystal"]));
$parse['monthly_crystal'] = colorNumber(pretty_number($parse["monthly_crystal"]));
$parse['daily_deuterium'] = colorNumber(pretty_number($parse["daily_deuterium"]));
$parse['weekly_deuterium'] = colorNumber(pretty_number($parse["weekly_deuterium"]));
$parse['monthly_deuterium'] = colorNumber(pretty_number($parse["monthly_deuterium"]));

//Porcentajes de minerias llenas
$parse['metal_storage'] = floor($planetrow["metal"] / $planetrow["metal_max"] * 100).$lang['o/o'];
$parse['crystal_storage'] = floor($planetrow["crystal"] / $planetrow["crystal_max"] * 100).$lang['o/o'];
$parse['deuterium_storage'] = floor($planetrow["deuterium"] / $planetrow["deuterium_max"] * 100).$lang['o/o'];
//Las barras de porcentaje
$parse['metal_storage_bar'] = floor($planetrow["metal"] / $planetrow["metal_max"] * 100)*2.5;
$parse['crystal_storage_bar'] = floor($planetrow["crystal"] / $planetrow["crystal_max"] * 100)*2.5;
$parse['deuterium_storage_bar'] = floor($planetrow["deuterium"] / $planetrow["deuterium_max"] * 100)*2.5;
//Color de la barra de metal
if($parse['metal_storage_bar'] > (100*2.5)){
	$parse['metal_storage_bar'] = 250;
	$parse['metal_storage_barcolor'] = '#C00000';
}elseif($parse['metal_storage_bar'] > (80*2.5)){
	$parse['metal_storage_barcolor'] = '#C0C000';
}else{
	$parse['metal_storage_barcolor'] = '#00C000';
}
//color de la barra de cristal
if($parse['crystal_storage_bar'] > (100*2.5)){
	$parse['crystal_storage_bar'] = 250;
	$parse['crystal_storage_barcolor'] = '#C00000';
}elseif($parse['crystal_storage_bar'] > (80*2.5)){
	$parse['crystal_storage_barcolor'] = '#C0C000';
}else{
	$parse['crystal_storage_barcolor'] = '#00C000';
}
//color de la barra de deutero
if($parse['deuterium_storage_bar'] > (100*2.5)){
	$parse['deuterium_storage_bar'] = 250;
	$parse['deuterium_storage_barcolor'] = '#C00000';
}elseif($parse['deuterium_storage_bar'] > (80*2.5)){
	$parse['deuterium_storage_barcolor'] = '#C0C000';
}else{
	$parse['deuterium_storage_barcolor'] = '#00C000';
}
//barrita de porcentaje
$parse['production_level_bar'] = $parse['production_level']*2.5;
$parse['production_level'] = "{$parse['production_level']}%";
$parse['production_level_barcolor'] = '#00ff00';


}

//Ahora realizamos la quieri :0
doquery("UPDATE {{table}} SET
	metal_perhour = '{$planetrow['metal_perhour']}',
	crystal_perhour = '{$planetrow['crystal_perhour']}',
	deuterium_perhour = '{$planetrow['deuterium_perhour']}',
	metal_max = '{$planetrow['metal_max']}',
	crystal_max = '{$planetrow['crystal_max']}',
	deuterium_max = '{$planetrow['deuterium_max']}',
	energy_used = '{$planetrow['energy_used']}',
	energy_max = '{$planetrow['energy_max']}'
	{$query}
	WHERE `id`='{$planetrow['id']}'",'planets');

$page = parsetemplate(gettemplate('resources'), $parse);
display($page,$lang['Resources']);

// Created by Perberos. All rights reversed (C) 2006
?>
