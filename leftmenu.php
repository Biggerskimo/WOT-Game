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
 //leftmenu.php :: Menu de la izquierda


define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
include('ban.php');

if(!check_user()){ header("Location: login.php"); die();}

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

includeLang('leftmenu');

$features = unserialize($user['diliziumFeatures']);

$mf = "Mainframe";//nombre del frame

$parse = $lang;
$parse['dpath'] = $dpath;
$parse['mf'] = $mf;
$parse['VERSION'] = VERSION;
if($user['authlevel'] > 0) $rank = 1;
else $rank = doquery("SELECT COUNT(*) FROM {{table}} WHERE points_points>={$user['points_points']}","users",true);
$parse['user_rank'] = $rank[0];

if(@$features['imperium'] > time()) $parse['IMPERIUM_LINK'] = '<tr><td><div align="center"><a href="imperium.php" target="'.$mf.'">Imperium</a></div></td></tr>';
else $parse['IMPERIUM_LINK'] = '';
$parse['DILIZIUM_LINK'] = '<tr><td><div align="center"><a href="dilizium.php" target="'.$mf.'"><font color="gold">Dilizium-Vorrat</font></a></div></td></tr>';

$parse['ADMIN_LINK'] = ($user['authlevel'] > 0)?'<tr><td><div align="center"><a href="administrator/leftmenu.php"><font color="lime">Admin</font></a></div></td></tr>':'';
echo parsetemplate(gettemplate('left_menu'), $parse);


// Created by Perberos. All rights reversed (C) 2006
?>
