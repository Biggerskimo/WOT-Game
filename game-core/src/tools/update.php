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

set_time_limit(600);

$start = intval($_GET['start']);

$sql = "SELECT *
		FROM ugml_fleets
		ORDER BY fleet_id
		LIMIT ".$start.", 100";
$result = WCF::getDB()->sendQuery($sql);

require_once(LW_DIR.'lib/data/fleet/FleetEditor.class.php');
require_once(LW_DIR.'lib/data/fleet/queue/FleetQueue.class.php');
require_once(LW_DIR.'lib/system/event/WOTEventEditor.class.php');
FleetQueue::readCache();
while($row = WCF::getDB()->fetchArray($result)) {
	$startPlanetID = $row['startPlanetID'];
	
	$targetPlanetID = $row['endPlanetID'];
	
	$ships = array();
	$parts = explode(';', $row['fleet_array']);
	foreach($parts as $part) {
		if(strlen($part) < 2) {
			continue;
		}
		list($specID, $count) = explode(',', $part);		
		$ships[$specID] = $count;
	}
	
	$metal = $row['fleet_resource_metal'];
	
	$crystal = $row['fleet_resource_crystal'];
	
	$deuterium = $row['fleet_resource_deuterium'];
	
	$duration = $row['fleet_end_time'] - $fleet['fleet_start_time'];
	
	$missionID = $row['fleet_mission'];
	
	if($row['fleet_mess'] != 2) {
		$impactTime = $row['fleet_start_time'];
		$wakeUpTime = 0;
	}
	else {
		$impactTime = $row['fleet_start_time'] - $row['standByTime'];
		$wakeUpTime = $row['fleet_start_time'];
	}
	$returnTime = $row['fleet_end_time'];
	
	$startTime = 2 * $impactTime - $returnTime + $row['standByTime'];
	
	$classPath = FleetQueue::$cache[$missionID]['classPath'];
	
	$galaxy = $row['fleet_start_galaxy'];
	
	$system = $row['fleet_start_system'];
	
	$planet = $row['fleet_start_planet'];
	
	$ownerID = $row['fleet_owner'];
	
	$ofiaraID = $row['fleet_ofiara'];
	
	$formationID = $row['formationID'];
	
	$fleetID = FleetEditor::insert(
		$startPlanetID, $targetPlanetID, $ownerID, $ofiaraID,
		$galaxy, $system, $planet,
		$metal, $crystal, $deuterium,
		$startTime, $impactTime, $returnTime,
		$missionID
	);
	
	if($row['fleet_mess'] == 0) {
		$impactEvent = WOTEventEditor::create(1, $fleetID, array('state' => 0), $impactTime);
		$impactEventID = $impactEvent->eventID;
	}
	else {
		$impactEventID = 0;	
	}
	$returnEvent = WOTEventEditor::create(1, $fleetID, array('state' => 1), $returnTime);
	$returnEventID = $returnEvent->eventID;
	if($wakeUpTime) {
		$wakeUpEvent = WOTEventEditor::create(1, $fleetID, array('state' => 2), $wakeUpTime);
		$wakeUpEventID = $wakeUpEvent->eventID;
	}
	else {
		$wakeUpEventID = 0;
	}
	
	$fleetEditor = new FleetEditor($fleetID);
	$fleetEditor->update(array('formationID' => $formationID, 'impactEventID' => $impactEventID, 'returnEventID' => $returnEventID, 'wakeUpEventID' => $wakeUpEventID, 'wakeUpTime' => $wakeUpTime));
	$fleetEditor->updateShips($ships);
}
sleep(5);
?>
done;
<script>
	window.location.href = 'update.php?start=<?php
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
 echo $start+100; ?>';
</script>