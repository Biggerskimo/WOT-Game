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

$start = intval($_GET['start']);
$interval = 500;

$sql = "SELECT ugml_archive_fleet.*
		FROM ugml_planets
		LEFT JOIN ugml_users
		ON ugml_users.id = ugml_planets.id_owner
		LEFT JOIN wcf1_user
		ON ugml_users.id = wcf1_user.userID
		LEFT JOIN ugml_archive_fleet
		ON ugml_planets.id = ugml_archive_fleet.targetPlanetID
		WHERE wcf1_user.userID IS NULL
		LIMIT ".$start.", ".$interval;
$result = WCF::getDB()->sendQuery($sql);

$users = array();
while($row = WCF::getDB()->fetchArray($result)) {
	if($row['data'] === null) {
		continue;
	}
	$data = unserialize(LWUtil::unserialize($row['data']));
	
	$rev = end($data);	
	
	if(!isset($users[$rev['data']['ownerID']])) {
		$metalu = $crystalu = $deuteriumu = 0;
	}
	else {
		extract($users[$rev['data']['ownerID']]);
	}
	
	$date['metalu'] = intval($rev['data']['metal'] + $metalu);
	$date['crystalu'] = intval($rev['data']['crystal'] + $crystalu);
	$date['deuteriumu'] = intval($rev['data']['deuterium'] + $deuteriumu);
	
	$users[$rev['data']['ownerID']] = $date;
}
foreach($users as $userID => $user) {
	$sql = "INSERT INTO ugml_umod_bug_ress
			 (userID, metalu, crystalu, deuteriumu)
			VALUES
			 (".$userID.", ".$user['metalu'].", ".$user['crystalu'].", ".$user['deuteriumu'].")
			ON DUPLICATE KEY UPDATE
				metalu = metalu + ".$user['metalu'].",
				crystalu = crystalu + ".$user['crystalu'].",
				deuteriumu = deuteriumu + ".$user['deuteriumu'];
	WCF::getDB()->sendQuery($sql);
}
$href = 'bug.php?start='.($start+$interval);
sleep(2);
?>
<script type="text/javascript">
	location.href = '<?php
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
 echo $href; ?>';
</script>
<a href="<?php
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
 echo $href; ?>"><?php
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
 echo $href; ?></a>