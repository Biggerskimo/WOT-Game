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
 //galaxy.php rapaired by DxPpLmOs

define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
include('ban.php');


if(!check_user()){ header("Location: login.php"); die();}

//
// Esta funcion permite cambiar el planeta actual.
//
include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);

$planetrow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_planet']}",'planets',true);
$lunarow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_luna']}",'lunas',true);
$galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet={$planetrow['id']}",'galaxy',true);
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

if(!isset($_POST['galaxy'])) $galaxy = $planetrow['galaxy'];
else $galaxy = intval($_POST['galaxy']);

if(!isset($_POST['system'])) $system = $planetrow['system'];
else $system = intval($_POST['system']);

// links
if(isset($_GET['g']) && isset($_GET['s']) && !count($_POST)){
	$galaxy = intval($_GET['g']);
	$system = intval($_GET['s']);
	if ($g > GALAXIES) $galaxy = GALAXIES;
	if ($s > 499) $system = 499;
}

// navigation
if($_POST["galaxyLeft"]) {
	if($_POST["galaxy"] < 1) $_POST["galaxy"] = 1;
	else if($_POST["galaxy"] == 1) $_POST["galaxy"] = 1;
    else $galaxy = $_POST["galaxy"] - 1;
} else if($_POST["galaxyRight"]){
	if ($_POST["galaxy"] > GALAXIES || $_POST["galaxyRight"] > GALAXIES || $galaxy > GALAXIES) {
		$_POST["galaxy"] = GALAXIES;
		$_POST["galaxyRight"] = GALAXIES;
		$galaxy = GALAXIES;
	} else if($_POST["galaxy"] == GALAXIES) {
		$_POST["galaxy"] = GALAXIES;
	    $galaxy = GALAXIES;
	} else $galaxy = $_POST["galaxy"] + 1;
}

if($_POST["systemLeft"]) {
	if($_POST["system"] < 1) $_POST["system"] = 1;
	else if ($_POST["system"] == 1) $_POST["system"] = 1;
    else $system = $_POST["system"] -1;
} else if($_POST["systemRight"]) {
	if($_POST["system"] > 499 || $_POST["systemRight"] > 499) $_POST["system"] = 499;
	else if($_POST["system"] == 499) $_POST["system"] = 499;
	else $system =  $_POST["system"] +1;
} else $system = (!$system) ? $_POST["system"] : $system;//default

$galaxy = intval($galaxy);
$system = intval($system);

require_once('game/lib/data/system/ViewableSystem.class.php');
$system = new ViewableSystem($galaxy, $system);
$system->view();
// Created by Perberos. All rights reversed (C) 2006
?>
