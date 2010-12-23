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
 //options.php


define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);


if(!check_user()){ header("Location: login.php"); die();}

includeLang('options');

$lang['PHP_SELF'] = 'options.'.$phpEx;

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];


if($_GET['mode'] == "change"){ //Array ( [db_character]

	$iduser = $user["id"];
	$avatar = escapeString($_POST["avatar"]);
	$dpath = escapeString($_POST["dpath"]);
	//Mostrar skin
	if(isset($_POST["design"])&& $_POST["design"] == 'on'){
		$design = "1";
	}else{
		$design = "0";
	}
	//Desactivar comprobaci� de IP
	if(isset($_POST["noipcheck"])&& $_POST["noipcheck"] == 'on'){
		$noipcheck = "1";
	}else{
		$noipcheck = "0";
	}
	//Nombre de usuario
	/*if(isset($_POST["db_character"])&& $_POST["db_character"] != ''){
		$username = $_POST['db_character'];
	}else{*/
		$username = $user['username'];
	//}
	//Cantidad de sondas de espionaje
	if(isset($_POST["spio_anz"])&&is_numeric($_POST["spio_anz"])){
		$spio_anz = $_POST["spio_anz"];
	}else{
		$spio_anz = "1";
	}
	//Mostrar tooltip durante
	if(isset($_POST["settings_tooltiptime"]) && is_numeric($_POST["settings_tooltiptime"])){
		$settings_tooltiptime = $_POST["settings_tooltiptime"];
	}else{
		$settings_tooltiptime = "1";
	}
	//Maximo mensajes de flotas
	if(isset($_POST["settings_fleetactions"]) && is_numeric($_POST["settings_fleetactions"])){
		$settings_fleetactions = $_POST["settings_fleetactions"];
	}else{
		$settings_fleetactions = "1";
	}//
	//Mostrar logos de los aliados
	if(isset($_POST["settings_allylogo"]) && $_POST["settings_allylogo"] == 'on'){
		$settings_allylogo = "1";
	}else{
		$settings_allylogo = "0";
	}
	//Espionaje
	if(isset($_POST["settings_esp"]) && $_POST["settings_esp"] == 'on'){
		$settings_esp = "1";
	}else{
		$settings_esp = "0";
	}
	//Escribir mensaje
	if(isset($_POST["settings_wri"]) && $_POST["settings_wri"] == 'on'){
		$settings_wri = "1";
	}else{
		$settings_wri = "0";
	}
	//A�dir a lista de amigos
	if(isset($_POST["settings_bud"]) && $_POST["settings_bud"] == 'on'){
		$settings_bud = "1";
	}else{
		$settings_bud = "0";
	}
	//Ataque con misiles
	if(isset($_POST["settings_mis"]) && $_POST["settings_mis"] == 'on'){
		$settings_mis = "1";
	}else{
		$settings_mis = "0";
	}
	//Ver reporte
	if(isset($_POST["settings_rep"]) && $_POST["settings_rep"] == 'on'){
		$settings_rep = "1";
	}else{
		$settings_rep = "0";
	}
	//Modo vacaciones
	$umod_meldung = '';
	if(isset($_POST["urlaubs_modus"]) && $_POST["urlaubs_modus"] == 'on'){
		// check fleets
		$sql = "SELECT ownerID
				FROM ugml_fleet
				WHERE ownerID = ".WCF::getUser()->userID;
		$fleet = WCF::getDB()->getFirstRow($sql);
		if($fleet) message('Du hast noch Flotten unterwegs!');

		// check buildings
		$sql = "SELECT *
				FROM ugml_planets
				WHERE (b_building_id != 0
					OR b_tech_id != 0
					OR b_hangar_id != '')
					AND id_owner = ".WCF::getUser()->userID;
		$planet = WCF::getDB()->getFirstRow($sql);
		if($planet) message('Du hast auf dem '.Planet::getInstance(null, $planet).' noch etwas in Bau!');

		if(!$fleet && !$planet && !$user['urlaubs_modus'])
		{
			$urlaubs_modus = (time() + 60 * 60 * 24 * 2/* / $game_config['resource_multiplier']*/);
			$umod_meldung =
'<p style="color: green;"><br>Du hast deinen Account in den Urlaubsmodus versetzt.<br>'.
'Falls du laenger als 30 Tage inaktiv bleibst, kann es sein, dass wir deinen '.
'Account loeschen oder an andere Interessenten weitergeben werden. Falls du das '.
'nicht willst, solltest du dich bei einem Game Operator melden.</p>';
		}
		else $urlaubs_modus = $user['urlaubs_modus'];
	}else{
		if($user['urlaubs_modus']) {
			// umode bug workaround
			$planets = Planet::getByUserID(WCF::getUser()->userID);
			foreach($planets as $planet) {
				$planet->calculateResources();
			}
		}
		
		if(TIME_NOW > $user['urlaubs_modus']) $urlaubs_modus = 0;
		else $urlaubs_modus = $user['urlaubs_modus'];
	}
	//Borrar cuenta
	if(isset($_POST["db_deaktjava"]) && $_POST["db_deaktjava"] == 'on'){
		$db_deaktjava = "1";
	}else{
		$db_deaktjava = "0";
	}

	doquery("UPDATE {{table}} SET
	`email` = '$db_email',
	`avatar` = '$avatar',
	`dpath` = '$dpath',
	`design` = '$design',
	`noipcheck` = '$noipcheck',
	`spio_anz` = '$spio_anz',
	`settings_tooltiptime` = '$settings_tooltiptime',
	`settings_fleetactions` = '$settings_fleetactions',
	`settings_allylogo` = '$settings_allylogo',
	`settings_esp` = '$settings_esp',
	`settings_wri` = '$settings_wri',
	`settings_bud` = '$settings_bud',
	`settings_mis` = '$settings_mis',
	`settings_rep` = '$settings_rep',
	`urlaubs_modus` = '$urlaubs_modus',
	`db_deaktjava` = '$db_deaktjava',
	`kolorminus` = '$kolorminus',
	`kolorplus` = '$kolorplus',
	`kolorpoziom` = '$kolorpoziom'
	WHERE `id` = '$iduser' LIMIT 1","users");

	WCF::getSession()->setUpdate(true);
	
	/*
	if(isset($_POST["db_password"]) && md5($_POST["db_password"]) == $user["password"]){

		if($_POST["newpass1"] == $_POST["newpass2"]){
			$newpass = md5($_POST["newpass1"]);
			doquery("UPDATE {{table}} SET `password` = '{$newpass}' WHERE `id` = '{$user['id']}' LIMIT 1","users");
			setcookie(COOKIE_NAME, "", time()-100000, "/", "", 0);//le da el expire
			message($lang['succeful_changepass'],$lang['changue_pass']);
		}

	}*/
	/*
	if($user['username']!=$_POST["db_character"]){

		$query = doquery("SELECT id FROM {{table}} WHERE username='{$_POST["db_character"]}'",'users',true);
		if(!$query){
			doquery("UPDATE {{table}} SET username='{$username}' WHERE id='{$user['id']}' LIMIT 1","users");
			setcookie(COOKIE_NAME, "", time()-100000, "/", "", 0);//le da el expire
			message($lang['succeful_changename'],$lang['changue_name']);
		}
	}*/
	message($lang['succeful_save'].$umod_meldung,$lang['Options']);
}
else
{

	$parse = $lang;

	$parse['dpath'] = $dpath;
	$parse['user_username'] = $user['username'];
	$parse['user_email'] = $user['email'];
	$parse['user_email_2'] = $user['email_2'];
	$parse['user_dpath'] = $user['dpath'];
	$parse['user_avatar'] = $user['avatar'];
	$parse['user_spio_anz'] = $user['spio_anz'];
	$parse['user_settings_tooltiptime'] = $user['settings_tooltiptime'];
	$parse['user_settings_fleetactions'] = $user['settings_fleetactions'];
	$parse['user_design'] = ($user['design'] == 1) ? " checked='checked'":'';
	$parse['user_noipcheck'] = ($user['noipcheck'] == 1) ? " checked='checked'":'';
	$parse['user_settings_allylogo'] = ($user['settings_allylogo'] == 1) ? " checked='checked'/":'';
	$parse['user_db_deaktjava'] = ($user['db_deaktjava'] == 1) ? " checked='checked'/":'';
	//$parse['user_urlaubs_modus'] = ($user['urlaubs_modus'] != 0)?" checked='checked'/":'';
	$parse['user_settings_rep'] = ($user['settings_rep'] == 1) ? " checked='checked'/":'';
	$parse['user_settings_esp'] = ($user['settings_esp'] == 1) ? " checked='checked'/":'';
	$parse['user_settings_wri'] = ($user['settings_wri'] == 1) ? " checked='checked'/":'';
	$parse['user_settings_mis'] = ($user['settings_mis'] == 1) ? " checked='checked'/":'';
	$parse['user_settings_bud'] = ($user['settings_bud'] == 1) ? " checked='checked'/":'';
	$parse['kolorminus'] = $user['kolorminus'];
	$parse['kolorplus'] = $user['kolorplus'];
	$parse['kolorpoziom'] = $user['kolorpoziom'];

	if($user['urlaubs_modus'] == 0) {
		$parse['urlaubs_modus'] = '<th>
				<a title="{vacations_tip}">Urlaubsmodus</a>
			</th>
	   		<th>
	    		<input name="urlaubs_modus" type="checkbox" />
	   		</th>';
	} elseif($user['urlaubs_modus'] < time()) {
		$parse['urlaubs_modus'] = '<th>
				<a title="{vacations_tip}">Urlaubsmodus</a>
			</th>
	   		<th>
	    		<input name="urlaubs_modus" checked="checked" type="checkbox" />
	   		</th>';
	} else {
		$parse['urlaubs_modus'] = '<th colspan="2">
				Du hast den Urlaubsmodus bis min. '.date('r', $user['urlaubs_modus']).' aktiviert!
			</th>';
	}

	$page .= parsetemplate(gettemplate('options_body'), $parse);

	display($page,$lang['Options']);

	die();
}

// Created by Perberos. All rights reversed (C) 2006
?>
