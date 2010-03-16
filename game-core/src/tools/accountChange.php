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

/*
 * displays the sql commands needed to change the accounts of to players
 * 
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 * @author	Biggerskimo
 */

/* config */
$alteID = 4190;
$neueID = 1072;
/* config end */

if(!defined('INSIDE')) define('INSIDE', true);
require('../includes/vars.php');

$tables = array(
	array('ugml_alliance', 'ally_owner'),
	array('ugml_buddy', 'sender'),
	array('ugml_buddy', 'owner'),
	array('ugml_fleets', 'fleet_owner'),
	array('ugml_fleets', 'fleet_ofiara'),
	array('ugml_fleet_queue', 'userID'),
	array('ugml_galactic_jump_queue', 'userID'),
	array('ugml_messages', 'message_owner'),
	array('ugml_messages', 'message_sender'),
	array('ugml_naval_formation_to_users', 'userID'),
	array('ugml_notes', 'owner'),
	array('ugml_planets', 'id_owner'),
	array('ugml_stat', 'userID'),
);

$alteID = @intval($_REQUEST['alteID']) ? @intval($_REQUEST['alteID']) : intval($alteID);
$neueID = @intval($_REQUEST['neueID']) ? @intval($_REQUEST['neueID']) : intval($neueID);

foreach($tables as $table) {
	echo "UPDATE ".$table[0]."
		SET ".$table[1]." = 100
		WHERE ".$table[1]." = ".$alteID.";<br />\n";
	echo "UPDATE ".$table[0]."
		SET ".$table[1]." = ".$alteID."
		WHERE ".$table[1]." = ".$neueID.";<br />\n";
	echo "UPDATE ".$table[0]."
		SET ".$table[1]." = ".$neueID."
		WHERE ".$table[1]." = 100;<br /><br />\n\n";
}

// researches
for($i = 100; $i < 200; ++$i) {
	if(isset($resource[$i])) {
		if(isset($sqlUpdates)) {
			$sqlUpdates .= ", ".$resource[$i];
			$sqlUpdates2 .= ", u1.".$resource[$i]." = u2.".$resource[$i];
		} else {
			$sqlUpdates = $resource;
			$sqlUpdates2 = "u1.".$resource[$i]." = u2.".$resource[$i];
		}
	}
}
echo "INSERT INTO ugml_tmp_users<br />
	SELECT *<br />
	FROM ugml_users<br />
	WHERE id = ".$alteID.";<br /><br />\n\n";

echo "UPDATE ugml_users AS u1,<br />
		ugml_users AS u2<br />
	SET ".$sqlUpdates2.",
		u1.dilizium = u2.dilizium,
		u1.lostDilizium = u2.lostDilizium,
		u1.diliziumFeatures = u2.diliziumFeatures,
		u1.id_planet = u2.id_planet,
		u1.galaxy = u2.galaxy,
		u1.system = u2.system,
		u1.planet = u2.planet<br />
	WHERE u1.id = ".$alteID."<br />
		AND u2.id = ".$neueID.";<br /><br />\n\n";

echo "UPDATE ugml_users AS u1,<br />
		ugml_tmp_users AS u2<br />
	SET ".$sqlUpdates2.",
		u1.dilizium = u2.dilizium,
		u1.lostDilizium = u2.lostDilizium,
		u1.diliziumFeatures = u2.diliziumFeatures,
		u1.id_planet = u2.id_planet,
		u1.galaxy = u2.galaxy,
		u1.system = u2.system,
		u1.planet = u2.planet<br />
	WHERE u1.id = ".$neueID."<br />
		AND u2.id = ".$alteID.";<br /><br />\n\n";
?>