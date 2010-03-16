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
 //settings.php :: Configuracion del juego


define('INSIDE', true);
$ugamela_root_path = '../';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

if(!check_user()){ header("Location: login.php"); }
if($user['authlevel']!="3"&&$user['authlevel']!="1"){ header("Location: ../login.php");}

//includeLang('options');

$lang['PHP_SELF'] = 'options.'.$phpEx;

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];


if($_POST && $mode == "change"){ //Array ( [db_character] 
	
	//Activa el modo debug.
	if(isset($_POST["debug"])&& $_POST["debug"] == 'on'){
		$game_config['debug'] = "1";
	}else{
		$game_config['debug'] = "0";
	}

	//Nombre del juego
	if(isset($_POST["game_name"])&& $_POST["game_name"] != ''){
		$game_config['game_name'] = $_POST['game_name'];
	}

	//copyright
	if(isset($_POST["copyright"])&& $_POST["copyright"] != ''){
		$game_config['copyright'] = $_POST['copyright'];
	}
	
	//Campos iniciales
	if(isset($_POST["initial_fields"])&&is_numeric($_POST["initial_fields"])){
		$game_config['initial_fields'] = $_POST["initial_fields"];
	}
	
	//Campos iniciales
	if(isset($_POST["resource_multiplier"])&&is_numeric($_POST["resource_multiplier"])){
		$game_config['resource_multiplier'] = $_POST["resource_multiplier"];
	}
	//Campos iniciales
	if(isset($_POST["metal_basic_income"])&&is_numeric($_POST["metal_basic_income"])){
		$game_config['metal_basic_income'] = $_POST["metal_basic_income"];
	}
	//Campos iniciales
	if(isset($_POST["crystal_basic_income"])&&is_numeric($_POST["crystal_basic_income"])){
		$game_config['crystal_basic_income'] = $_POST["crystal_basic_income"];
	}
	//Campos iniciales
	if(isset($_POST["deuterium_basic_income"])&&is_numeric($_POST["deuterium_basic_income"])){
		$game_config['deuterium_basic_income'] = $_POST["deuterium_basic_income"];
	}
	//Campos iniciales
	if(isset($_POST["energy_basic_income"])&&is_numeric($_POST["energy_basic_income"])){
		$game_config['energy_basic_income'] = $_POST["energy_basic_income"];
	}
	
	//configuracion del juego
	doquery("UPDATE {{table}} SET config_value='{$game_config['game_name']}' WHERE config_name='game_name'",config);
	doquery("UPDATE {{table}} SET config_value='{$game_config['copyright']}' WHERE config_name='copyright'",config);
	//opciones de planetas
	doquery("UPDATE {{table}} SET config_value='{$game_config['initial_fields']}' WHERE config_name='initial_fields'",config);
	doquery("UPDATE {{table}} SET config_value='{$game_config['resource_multiplier']}' WHERE config_name='resource_multiplier'",config);
	doquery("UPDATE {{table}} SET config_value='{$game_config['metal_basic_income']}' WHERE config_name='metal_basic_income'",config);
	doquery("UPDATE {{table}} SET config_value='{$game_config['crystal_basic_income']}' WHERE config_name='crystal_basic_income'",config);
	doquery("UPDATE {{table}} SET config_value='{$game_config['deuterium_basic_income']}' WHERE config_name='deuterium_basic_income'",config);
	doquery("UPDATE {{table}} SET config_value='{$game_config['energy_basic_income']}' WHERE config_name='energy_basic_income'",config);
	//miscelaneos
	doquery("UPDATE {{table}} SET config_value='{$game_config['debug']}' WHERE config_name='debug'",config);
	
	
	message('Los datos se guardaron correctamente','Configuracion','?');
}
else
{
	$parse = $game_config;

	$parse['dpath'] = $dpath;
	$parse['debug'] = ($game_config['debug'] == 1) ? " checked='checked'/":'';

	$page .= parsetemplate(gettemplate('admin/options_body'), $parse);

	display($page,'Configuracion');

}

// Created by Perberos. All rights reversed (C) 2006
?>
