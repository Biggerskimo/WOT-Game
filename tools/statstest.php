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
 //fleetback.php
define('INSIDE', true);
$ugamela_root_path = '../';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
if(!check_user()){ header("Location: login.php"); die();}

// sub1
$specs = Spec::getByFlag(0x39, true);
$generated = "";

foreach($specs as $specID => $specObj) {
	if(!empty($generated)) {
		$generated .= " + ";
	}
	$costs = ($specObj->costsMetal + $specObj->costsCrystal + $specObj->costsDeuterium);
	
	if($specObj->costsFactor != 1) {
		$generated .= "(".$costs." * (1 - POW(".$specObj->costsFactor.", `".$specObj->colName."`)) / -(".$specObj->costsFactor." - 1))";
	}
	else {
		$generated .= "(".$costs." * `".$specObj->colName."`)";
	}
}

$sub1 = "SELECT SUM(".$generated.")
		FROM ugml_planets
		WHERE ugml_planets.id_owner = ugml_stat_entry.relationalID";

// sub2
$specs = Spec::getByFlag(0x02, true);
$generated = "";

foreach($specs as $specID => $specObj) {
	if(!empty($generated)) {
		$generated .= " + ";
	}
	$costs = ($specObj->costsMetal + $specObj->costsCrystal + $specObj->costsDeuterium);
	
	if($specObj->costsFactor != 1) {
		$generated .= "(".$costs." * (1 - POW(".$specObj->costsFactor.", `".$specObj->colName."`)) / -(".$specObj->costsFactor." - 1))";
	}
	else {
		$generated .= "(".$costs." * `".$specObj->colName."`)";
	}
}

$sub2 = "SELECT ".$generated."
		FROM ugml_users
		WHERE ugml_users.id = ugml_stat_entry.relationalID";
// sub3
$specs = Spec::getByFlag(0x08, true);
$generated = "";

foreach($specs as $specID => $specObj) {
	/*if(!empty($generated)) {
		$generated .= " + ";
	}*/
	$costs = ($specObj->costsMetal + $specObj->costsCrystal + $specObj->costsDeuterium);
	
	$generated .= " WHEN ".$specID." THEN ".$costs;
	/*if($specObj->costsFactor != 1) {
		$generated .= "(".$costs." * (1 - POW(".$specObj->costsFactor.", `".$specObj->colName."`)) / -(".$specObj->costsFactor." - 1))";
	}
	else {
		$generated .= "(".$costs." * `".$specObj->colName."`)";
	}*/
}

$sub3 = "SELECT COALESCE(SUM(CASE specID".$generated." ELSE 0 END * shipCount), 0)
		FROM ugml_fleet
		LEFT JOIN ugml_fleet_spec
			ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
		WHERE ugml_fleet.ownerID = ugml_stat_entry.relationalID";

$sql = "UPDATE ugml_stat_entry
		SET points = ((".$sub1.") + (".$sub2.") + (".$sub3.")) / 1000
		WHERE statTypeID = 1";
echo $sql;
?>
done;