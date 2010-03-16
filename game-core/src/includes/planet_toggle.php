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


if(!defined('INSIDE')){ die("attemp hacking");}

if(isset($_GET["cp"]) && is_numeric($_GET["cp"])) {
	$checking = doquery("SELECT id, planet, className FROM {{table}} WHERE id='".$_GET["cp"]."' AND id_owner={$user['id']}","planets",true);
	if($checking) {
		$user['current_planet'] = $_GET["cp"];

		WCF::getUser()->changePlanet($_GET['cp'], $checking['className']);
	}

	// forward to new site (workaround for resources-bug)
	$newQueryStr = '';
	unset($_GET['cp'], $_GET['re']);
	foreach($_GET as $argName => $argValue) {
		$newQueryStr .= '&'.$argName.'='.$argValue;
	}
	if(!empty($newQueryStr)) $newQueryStr = '?'.substr($newQueryStr, 1);

	$fileName = $_SERVER['PHP_SELF'];
	
	if(substr($fileName, 1, -5) == 'floten') $fileName = 'fleet.php';
	
	$newURL = $fileName.$newQueryStr;
	WCF::getDB()->deleteShutdownUpdates();
	header('Location: '.$newURL);
	exit;

}
?>
