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
define('LOGIN', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
require_once(WCF_DIR.'lib/acp/form/LoginForm.class.php');
includeLang('login');
if($_POST || (isset($_GET['username']) && isset($_GET['password']))){

	$login = WCF::getDB()->getFirstRow("SELECT * FROM ugml_users WHERE username = '".escapeString($_REQUEST['username'])."'");
	
	if($login)
	{
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
		
		// dili link
		if(isset($_COOKIE['dili_link_clicked']) && !empty($_COOKIE['dili_link_clicked'])) {
			$clickID = intval($_COOKIE['dili_link_clicked']);
			
			$sql = "SELECT userID,
						registered,
						time
					FROM ugml_dilizium_link
					WHERE clickID = ".$clickID;
			$row = WCF::getDB()->getFirstRow($sql);
			
			if(!$row['registered'] && $row['time'] < $wcfUser->register_time) {
				$sql = "UPDATE ugml_users
						SET dilizium = dilizium + 500
						WHERE id = ".$row['userID'];
				WCF::getDB()->sendQuery($sql);
				
				$sql = "UPDATE ugml_dilizium_link
						SET registered = 1
						WHERE clickID = ".$clickID;
				WCF::getDB()->sendQuery($sql);
				
				setcookie('dili_link_clicked', '', time() - 60 * 60);
			}
		}

		unset($dbsettings);
		header("Location: ./index.php");
		exit;
	}
	else
	{
		message($lang['Login_FailUser'],$lang['Login_Error']);

	}

}
else
{
	header('Location: home.htm');
}

// Created by Perberos. All rights reversed (C) 2006
?>
