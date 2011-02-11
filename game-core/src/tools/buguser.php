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

require_once(LW_DIR.'lib/util/SerializeUtil.class.php');

set_time_limit(900);

// planets
$planets = array();

$sql = "SELECT id, galaxy, system, planet, planetTypeID
		FROM ugml_planets";
$result = WCF::getDB()->sendQuery($sql);
while($row = WCF::getDB()->fetchArray($result))
{
	$planets[$row['id']] = $row['galaxy'].':'.$row['system'].':'.$row['planet'].'\''.$row['planetTypeID'];
}
var_dump(count($planets));
$sql = "SELECT *
		FROM ugml_archive_fleet
		WHERE impactTime > UNIX_TIMESTAMP() - 60 * 60 * 24 * 30
		LIMIT 1000000";
$result = WCF::getDB()->sendQuery($sql);

$hitliste = array();
while($row = WCF::getDB()->fetchArray($result))
{
	$data = unserialize(LWUtil::unserialize($row['data']));
	$data = $data[0]['data'];
	$planetID = $data['targetPlanetID'];
	$shouldCoords = $planets[$planetID];
	if(empty($shouldCoords))
		continue;
	$realCoords = $data['targetPlanetCoords'];
	
	if($shouldCoords != $realCoords)
	{
		echo $realCoords." is not ".$shouldCoords." start ".$data['startPlanetCoords'];
		echo "\n<br>\n";
		echo "time ".date('r', $data['impactTime']);
		echo "\n<br>\n";
		echo "time ".$data['impactTime'];
		echo "\n<br>\n";
		echo "done by ".$data['ownerID'];
		echo "\n<br>\n";
		echo "\n<br>\n";
		$hitliste[$data['ownerID']]++;
		//$hitliste[$data['ownerID']][] = $data['impactTime'];
	}
}
var_dump($hitliste);
echo implode(',', array_keys($hitliste));
?>