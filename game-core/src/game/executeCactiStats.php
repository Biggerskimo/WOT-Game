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
//echo "bla";
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

// dirty :(
if(array_search('usersOnline', $argv))
{
	$sql = "SELECT
			(SELECT COUNT(*) FROM ugml_users WHERE onlinetime > UNIX_TIMESTAMP() - 60 * 15) AS 15m,
			(SELECT COUNT(*) FROM ugml_users WHERE onlinetime > UNIX_TIMESTAMP() - 60 * 60) AS 60m,
			(SELECT COUNT(*) FROM ugml_users WHERE onlinetime > UNIX_TIMESTAMP() - 60 * 60 * 24) AS 24h,
			(SELECT COUNT(*) FROM ugml_users WHERE onlinetime > UNIX_TIMESTAMP() - 60 * 60 * 24 * 30) AS 30d,
			(SELECT COUNT(*) FROM ugml_users WHERE onlinetime > UNIX_TIMESTAMP() - 60 * 60 * 24 * 90) AS 90d";
	$row = WCF::getDB()->getFirstRow($sql);
	
	echo "15m:".$row['15m']." 60m:".$row['60m']." 24h:".$row['24h']." 30d:".$row['30d']." 90d:".$row['90d'];
	exit;
}
if(array_search('requestsMin', $argv))
{
	$sql = "SELECT
			(SELECT COUNT(*) / 15 FROM ugml_request WHERE `time` > UNIX_TIMESTAMP() - 60 * 15) AS 15m,
			(SELECT COUNT(*) / 60 FROM ugml_request WHERE `time` > UNIX_TIMESTAMP() - 60 * 60) AS 60m,
			(SELECT COUNT(*) / (60 * 24) FROM ugml_request WHERE `time` > UNIX_TIMESTAMP() - 60 * 60 * 24) AS 24h";
	$row = WCF::getDB()->getFirstRow($sql);
	
	echo "15m:".$row['15m']." 60m:".$row['60m']." 24h:".$row['24h'];
	exit;
}

if(array_search('fleetState', $argv))
{
	$sql = "SELECT
			(SELECT COUNT(*) FROM ugml_fleet WHERE impactEventID IS NOT NULL) AS flight,
			(SELECT COUNT(*) FROM ugml_fleet WHERE impactEventID IS NULL AND wakeUpEventID IS NOT NULL) AS standBy,
			(SELECT COUNT(*) FROM ugml_fleet WHERE impactEventID IS NULL AND wakeUpEventID IS NULL) AS `return`";
	$row = WCF::getDB()->getFirstRow($sql);
	
	echo "flight:".$row['flight']." standBy:".$row['standBy']." return:".$row['return'];
	exit;
}
$missions = array(
	1 => 'attack',
	3 => 'transport',
	4 => 'deploy',
	5 => 'destroy',
	6 => 'espionage',
	8 => 'harvest',
	9 => 'colonize',
	11 => 'navalFormationAttack',
	12 => 'standBy',
	20 => 'missileAttack');
$sql = "SELECT COUNT(*) AS count, missionID
		FROM ugml_fleet
		GROUP BY missionID";
$result = WCF::getDB()->sendQuery($sql);

while($row = WCF::getDB()->fetchArray($result))
{
	echo $missions[$row['missionID']].":".$row['count']." ";
}
?>
