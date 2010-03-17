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
$ugamela_root_path = '../';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
if(!check_user()){ header("Location: login.php"); die();}

require_once(LW_DIR.'lib/data/ovent/BuildingOvent.class.php');
require_once(LW_DIR.'lib/data/ovent/FleetOvent.class.php');

// fleets
$sql = "SELECT ugml_fleet.*,
			GROUP_CONCAT(
				CONCAT(specID, ',', shipCount) 
				SEPARATOR ';')
			AS fleet
		FROM ugml_fleet
		LEFT JOIN ugml_fleet_spec
			ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
		LEFT JOIN ugml_ovent
			ON oventTypeID = 1
				AND ugml_ovent.relationalID = ugml_fleet.fleetID
		WHERE ugml_ovent.oventID IS NULL
		GROUP BY ugml_fleet.fleetID";
$result = WCF::getDB()->sendQuery($sql);

while($row = WCF::getDB()->fetchArray($result)) {
	try {
		$fleet = Fleet::getInstance(null, $row);
		FleetOvent::create($fleet, true, true);
	} catch(DatabaseException $e) {
		$e->show();
		echo '<br />Continue ...';
	}
}

// buildings
$sql = "SELECT ugml_planets.*
		FROM ugml_planets
		LEFT JOIN ugml_ovent
			ON ugml_ovent.oventTypeID = 2
				AND ugml_ovent.relationalID = ugml_planets.id
		WHERE b_building_id != 0
			AND b_building > UNIX_TIMESTAMP()
			AND ugml_ovent.oventID IS NULL";
$result = WCF::getDB()->sendQuery($sql);

while($row = WCF::getDB()->fetchArray($result)) {
	$planet = Planet::getInstance(null, $row, false);
	
	BuildingOvent::check($planet->planetID);
}
?>
done;