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

// define (ugamela) constants
define('INSIDE', true);
define('EVENT_HANDLER', true);
define('WCF_DIR', dirname(__FILE__).'/../wcf/');
define('NO_SHUTDOWN_QUERIES', true);
define('DEFAULT_SKINPATH',"http://xen251.linea7.net/game/game/alysium/");
define('TEMPLATE_DIR',"templates/");
define('TEMPLATE_NAME',"OpenGame");
define('DEFAULT_LANG','de');

// include ugamela
$ugamela_root_path = '/srv/www/htdocs/';
include($ugamela_root_path . 'extension.inc');

// mastercook patch
$_SERVER['REQUEST_METHOD'] = '';
$_SERVER['SERVER_PORT'] = '';

// include wcf
if(!defined('WCF_DIR')) define('WCF_DIR', dirname(__FILE__).'/../wcf/');
require_once($ugamela_root_path.'game/global.php');
//require_once($ugamela_root_path.'wotapi/global.php');
restore_error_handler();

// include some ugamela libs
require($ugamela_root_path.'includes/vars.php');
require($ugamela_root_path.'includes/functions.php');
require($ugamela_root_path.'includes/strings.php');

// load config
$sql = "SELECT *
		FROM ugml_config";
$config = WCF::getDB()->getResultList($sql);
$game_config = array();
foreach($config as $row) {
	$game_config[$row['config_name']] = $row['config_value'];
}

if(!defined('OLD_LOG_TIME')) define('OLD_LOG_TIME', TIME_NOW - 60 * 60 * 24 * 7 * 35);

// hanging fleets
$sql = "SELECT fleetID 
		FROM ugml_fleet
		LEFT JOIN ugml_event
			ON ugml_fleet.returnEventID = ugml_event.eventID
		WHERE ugml_event.eventID IS NULL";
$result = WCF::getDB()->sendQuery($sql);

require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
while($row = WCF::getDB()->fetchArray($result)) {
	Fleet::getInstance($row['fleetID'])->getEditor()->delete();
}

// events without fleets
$sql = "SELECT GROUP_CONCAT(ugml_event.eventID)
			AS eventIDs
		FROM ugml_event
		LEFT JOIN ugml_fleet
			ON ugml_event.specificID = ugml_fleet.fleetID
		WHERE ugml_fleet.fleetID IS NULL";
$row = WCF::getDB()->getFirstRow($sql);

if(!empty($row['eventIDs'])) {
	$sql = "DELETE FROM ugml_event
			WHERE eventID IN (".$row['eventIDs'].")";
	WCF::getDB()->sendQuery($sql);
}


// delete debrises
$sql = "SELECT id FROM ugml_planets
		LEFT JOIN ugml_fleet
			ON ugml_fleet.targetPlanetID = ugml_planets.id
		WHERE ugml_planets.deletionTime < UNIX_TIMESTAMP()
			AND ugml_planets.metal <= 0
			AND ugml_planets.crystal <= 0
			AND ugml_planets.planet_type = 2
			AND ugml_fleet.fleetID IS NULL";
$debrises = WCF::getDB()->sendQuery($sql, "");
$debrisesStr = "";
while($row = mysql_fetch_assoc($debrises)) $debrisesStr .= ",".$row['id'];

if(!empty($debrisesStr)) {
	$sql = "DELETE FROM ugml_planets
			WHERE id IN(".substr($debrisesStr, 1).")";
	WCF::getDB()->sendQuery($sql, "");
}

// move old messages
$sql = "INSERT INTO ugml_archive_messages
		SELECT * FROM ugml_messages
		WHERE message_time < ".(time() - 60 * 60 * 24 * 3)."
		LIMIT 1000";
WCF::getDB()->sendQuery($sql, "");

$sql = "DELETE FROM ugml_messages
		WHERE message_time < ".(time() - 60 * 60 * 24 * 3)."
		LIMIT 1000";
WCF::getDB()->sendQuery($sql, "");

// reset protection shields
$sql = "UPDATE ugml_planets SET small_protection_shield = 1 WHERE small_protection_shield > 1";

WCF::getDB()->sendQuery($sql, "");

$sql = "UPDATE ugml_planets SET big_protection_shield = 1 WHERE big_protection_shield > 1";

WCF::getDB()->sendQuery($sql, "");

$sql = "UPDATE ugml_alliance_to_alliances
		SET interrelationState = 3
		WHERE interrelationType = 3
			AND interrelationState = 1
			AND creationTime < UNIX_TIMESTAMP() - 60 * 60 * 12";
WCF::getDB()->sendQuery($sql, "");

// delete old log data
$sql = "DELETE FROM ugml_archive_fleet
		WHERE returnTime < ".OLD_LOG_TIME."
		LIMIT 10000";
WCF::getDB()->sendQuery($sql, "");

$sql = "DELETE LOW_PRIORITY FROM ugml_archive_messages
		WHERE message_time < ".OLD_LOG_TIME."
		LIMIT 10000";
//doquery($sql, "");

$sql = "DELETE FROM ugml_archive_request
		WHERE message_time < ".OLD_LOG_TIME."
		LIMIT 10000";
//doquery($sql, "");

// update old planet structure
$sql = "UPDATE ugml_planets
		SET planet_type = planetTypeID
		WHERE planet_type = 0";
WCF::getDB()->sendQuery($sql, "");

$sql = "UPDATE ugml_planets
		SET planetTypeID = planet_type,
			planetKind = planet_type
		WHERE planetTypeID = 0";
WCF::getDB()->sendQuery($sql, "");

// delete espionage reports
$sql = "DELETE FROM ugml_espionage_report
		WHERE `time` < UNIX_TIMESTAMP() - 60 * 60 * 24 * 7
		LIMIT 10000";
WCF::getDB()->sendQuery($sql, "");

// delete orphaned fleet queue spec data
$sql = "DELETE FROM ugml_fleet_queue_fleet
		WHERE (SELECT fleetQueueID FROM ugml_fleet_queue WHERE ugml_fleet_queue.fleetQueueID = ugml_fleet_queue_fleet.fleetQueueID) IS NULL";
WCF::getDB()->sendQuery($sql);

// delete old naval formations
$sql = "DELETE FROM ugml_naval_formation
		WHERE impactTime < ".TIME_NOW;
WCF::getDB()->sendQuery($sql);


// check news
require_once(LW_DIR.'lib/data/news/NewsFeed.class.php');
new NewsFeed();
WCF::getCache()->clearResource('news-'.PACKAGE_ID);

// delete orphaned ovents
$sql = "DELETE FROM ugml_ovent
		WHERE `time` < UNIX_TIMESTAMP()";
WCF::getDB()->sendQuery($sql);
echo 'done!';
?>
