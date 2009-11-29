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

define('WCF_DIR', '/srv/www/htdocs/wcf/');
require_once('../game/lib/data/fleet/Fleet.class.php');
require_once('../game/lib/util/LWUtil.class.php');
require_once('../game/lib/system/spec/Spec.class.php');

$fleetStr = "10753267";
$fleetStrs = explode(",", $fleetStr);

$fleetData = array();
/*foreach($fleetStrs as $fleetID) {
	$sql 
	//$fleets += Fleet::getInstance($fleetID);
}*/
$sql = "SELECT *
		FROM ugml_archive_fleet
		WHERE fleetID IN(".$fleetStr.")
		ORDER BY missionID ASC";
$result = WCF::getDB()->sendQuery($sql);
$formationID = 7028;

while($row = WCF::getDB()->fetchArray($result)) {
	$data = unserialize(LWUtil::unserialize($row['data']));
	
	$fleet = FleetEditor::create($data[0]['data']['startPlanetID'],
		$data[0]['data']['targetPlanetID'],
		$data[0]['fleet'],
		$data[0]['data']['galaxy'],
		$data[0]['data']['system'],
		$data[0]['data']['planet'],
		$data[0]['data']['metal'],
		$data[0]['data']['crystal'],
		$data[0]['data']['deuterium'],
		$data[0]['data']['missionID'] == 12 ? 600 : 1200,
		$data[0]['data']['missionID']
		);
		
	/*if($data[0]['data']['missionID'] == 12) {		
		$fleet->changeTime(array('return' => 2400 + time()));
		
		$wakeUpEvent = WOTEventEditor::create(1, $fleet->fleetID, array('state' => 2), time() + 1800);
	
		$fleet->update(array('wakeUpEventID' => $wakeUpEvent->eventID, 'wakeUpTime' => time() + 1800));
	}
	else if($data[0]['data']['missionID'] == 11) {*/
		$formation = new NavalFormation($formationID);
		$formation->getEditor()->addFleet($fleet->fleetID);
	//}
	/*else {
		$formation = NavalFormationEditor::create($fleet->fleetID, $fleet->ownerID);
		$formationID = $formation->formationID;
	}*/
	echo $fleet->fleetID,',';
}


foreach($fleetData as $fleet) {
	//echo $fleet->fleetID,': ',$fleet->ownerID,': ',$fleet->ofiaraID,': ',array_sum($fleet->fleet),': ',$fleet->impactTime,";<br>\n";
}

/*
$a = array(1 => 2);
$b = array(1 => 3);
var_Dump(Spec::diff($a, $b));
var_Dump(array_diff_assoc($b, $a));

$data = '3:1~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';
$array = unserialize(LWUtil::unserialize($data));

var_dump($array);*/

?>