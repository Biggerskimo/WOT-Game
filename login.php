<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/
 // login2.php :: Permite identificar al usuario, crear la cookie. Y lo redirige a index.php
//echo ".";
define('INSIDE', true);
define('LOGIN', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
//echo ".";
//try {
	include($ugamela_root_path . 'common.'.$phpEx);
/*} catch(Exception $e) {
	print_r($e);
	die('...');
}*/
//echo ".";
require_once(WCF_DIR.'lib/acp/form/LoginForm.class.php');
//echo ".";
includeLang('login');
//echo ".";
if($_POST || (isset($_GET['username']) && isset($_GET['password']))){ //si no se establecio un post manda al login.php

	//Se realiza una quiery buscando el nombre de usuario
	$login = WCF::getDB()->getFirstRow("SELECT * FROM ugml_users WHERE username = '".escapeString($_REQUEST['username'])."'");
	
	if($login) //Si se encuentra un usuario, $login es una array
	{ //Se identifica la contrase�

		//if($login['password'] == md5($_POST['password']))
		//{
			/*$expiretime = 0;
			$rememberme = 0;
			@include('config.php');
			$cookie = $login["id"] . " " . md5($login["password"] . "--" . $dbsettings["secretword"]) . " " . $rememberme;

			//echo "-";
			LWCore::logout();

			setcookie('LWGAME_REF_N', 1, time() + 24*60*60*365*10);
			setcookie($game_config['COOKIE_NAME'], $cookie, $expiretime);
			*/
			/**
			 * WCF Hack
			 */
			try {
				$wcfUser = UserAuth::getInstance()->loginManually($_REQUEST['username'], $_REQUEST['password']);
				UserAuth::getInstance()->storeAccessData($wcfUser, $_REQUEST['username'], $_REQUEST['password']);
				WCF::getSession()->changeUser($wcfUser);
			} catch(Exception $e) {
				message($lang['Login_FailPassword'],$lang['Login_Error']);
				exit;
			}


			$sql = "UPDATE ugml_users
					SET lastLoginTime = ".TIME_NOW.",
						current_planet = id_planet,
						planetClassName = 'UserPlanet'
					WHERE id = ".$login['id'];
			WCF::getDB()->sendQuery($sql);

			// ugamela
			$expiretime = 0;
			$rememberme = 0;
			@include('config.php');
			$cookie = $wcfUser->userID.' '.md5($_REQUEST['password'].'--'.$dbsettings['secretword']) . " " . $rememberme;
			setcookie('LWGAME_REF_N', 1, time() + 24*60*60*365*10);
			setcookie($game_config['COOKIE_NAME'], $cookie, $expiretime);

			unset($dbsettings);
			header("Location: ./index.php");
			exit;

		//}
		//else
		//{//Muestra un mensaje de error.

		//	message($lang['Login_FailPassword'],$lang['Login_Error']);

		//}

	}
	else
	{ //Cuando $login no contiene datos de jugadores

		message($lang['Login_FailUser'],$lang['Login_Error']);

	}

}
else
{//Vista normal

	header('Location: home.htm');

	/*$parse = $lang;
	//preguntamos quien fue el ultimo en registrarse
	$query = doquery('SELECT username FROM {{table}} ORDER BY register_time DESC','users',true);
	$parse['last_user'] = $query['username'];
	//preguntamos quien fue el ultimo en registrarse
	$query = doquery("SELECT COUNT(DISTINCT(id)) FROM {{table}} WHERE onlinetime>".(time()-900),'users',true);
	$parse['online_users'] = $query[0];
	//$count = doquery(","users",true);
	$parse['users_amount'] = $game_config['users_amount'];

	$page = parsetemplate(gettemplate('login_body'), $parse);

	display($page,$lang['Login']);*/

}

// Created by Perberos. All rights reversed (C) 2006
?>
