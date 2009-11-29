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
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

if(!check_user()){ header("Location: login.php"); die();}

includeLang('stat');

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

$parse = $lang;
$who = (isset($_POST["who"]))?$_POST["who"]:$_GET["who"];
$type = (isset($_POST["type"]))?$_POST["type"]:$_GET["type"];
$start = (isset($_POST["start"]))?$_POST["start"]:$_GET["start"];

if($_POST['who'] == 'player' || $_POST['who'] == 'ally') $who = $_POST['who'];
else if($_GET['who'] == 'player' || $_GET['who'] == 'ally') $who = $_GET['who'];
else $who = 'player';

if($_POST['type'] == 'points' || $_POST['type'] == 'fleet' || $_POST['type'] == 'research') $type = $_POST['type'];
else if($_GET['type'] == 'points' || $_GET['type'] == 'fleet' || $_GET['type'] == 'research') $type = $_GET['type'];
else $type = 'points';

if(is_numeric($_POST['start'])) $start = $_POST['start'];
else if(is_numeric($_GET['start'])) $start = $_GET['start'];
else $start = '1';

$start = max(0, $start);

if(empty($start)) $start = $user['rankPoints'];
if($start > 1500) $start = 1;

$start = (floor($start / 100 % 100) * 100) + 1;

$lang['who'] = '<option value="player"'.
	(($who == "player") ? " SELECTED" : "").'>Spieler</option>
  <option value="ally"'.
	(($who == "ally") ? " SELECTED" : "").'>Allianz</option>';


$lang['type'] = '
  <option value="points"'.
	(($type == "points") ? " SELECTED" : "").'>Punkten</option>
<option value="fleet"'.
	(($type == "fleet") ? " SELECTED" : "").'>Flotten</option>
  <option value="research"'.
	(($type == "research") ? " SELECTED" : "").'>Forschungen</option>';

$lang['start'] = '
	   <option value="1"'.
	(($start == "1") ? " SELECTED" : "").'>1-100</option>
	   <option value="101"'.
	(($start == "101") ? " SELECTED" : "").'>101-200</option>
	   <option value="201"'.
	(($start == "201") ? " SELECTED" : "").'>201-300</option>
	   <option value="301"'.
	(($start == "301") ? " SELECTED" : "").'>301-400</option>
	   <option value="401"'.
	(($start == "401") ? " SELECTED" : "").'>401-500</option>
	   <option value="501"'.
	(($start == "501") ? " SELECTED" : "").'>501-600</option>
	   <option value="601"'.
	(($start == "601") ? " SELECTED" : "").'>601-700</option>
	   <option value="701"'.
	(($start == "701") ? " SELECTED" : "").'>701-800</option>
	   <option value="801"'.
	(($start == "801") ? " SELECTED" : "").'>801-900</option>
	   <option value="901"'.
	(($start == "901") ? " SELECTED" : "").'>901-1000</option>
	   <option value="1001"'.
	(($start == "1001") ? " SELECTED" : "").'>1001-1100</option>
	   <option value="1101"'.
	(($start == "1101") ? " SELECTED" : "").'>1101-1200</option>
	   <option value="1201"'.
	(($start == "1201") ? " SELECTED" : "").'>1201-1300</option>
	   <option value="1301"'.
	(($start == "1301") ? " SELECTED" : "").'>1301-1400</option>
	   <option value="1401"'.
	(($start == "1401") ? " SELECTED" : "").'>1401-1500</option>';

$lang['data'] = $game_config['stats_new'];

$start--;

if($who == 'ally') {

	$lang['body_table'] = parsetemplate(gettemplate('stat_alliancetable_header'), $lang);

	$sql = 'SELECT {{table}}stat.allyRank'.ucfirst($type).' AS rank,
				{{table}}stat.oldAllyRank'.ucfirst($type).' AS oldRank,
				{{table}}users.ally_id AS allyID,
 				{{table}}users.ally_name AS allyName,
 				{{table}}alliance.ally_tag AS allyTag,
 				COUNT(*) AS member,
 				FLOOR(SUM({{table}}stat.'.$type.')) AS '.$type.',
				FLOOR(SUM({{table}}stat.'.$type.') / COUNT(*)) AS average
			FROM {{table}}stat
			LEFT JOIN {{table}}users
				ON {{table}}stat.userID = {{table}}users.id
			LEFT JOIN {{table}}alliance
				ON {{table}}users.ally_id = {{table}}alliance.id
			WHERE {{table}}users.ally_id >= 1
				AND allyRank'.ucfirst($type).' > '.$start.'
				AND allyRank'.ucfirst($type).' <= '.($start + 100).'
			GROUP BY {{table}}users.ally_id
		 	ORDER BY allyRank'.ucfirst($type).' ASC,
		 			allyID DESC';
	$alliances = doquery($sql, '');

	while($alliance = mysql_fetch_assoc($alliances)) {
		$lang['ally_rank'] = $alliance['rank'];

		if($alliance['rank'] == $alliance['oldRank']) $lang['ally_rankplus'] = "<font color=\"#87CEEB\">0</font>";
		else if($alliance['rank'] < $alliance['oldRank']) $lang['ally_rankplus'] = "<font color=\"green\">+".abs($alliance['oldRank'] - $alliance['rank'])."</font>";
		else $lang['ally_rankplus'] = "<font color=\"red\">-".abs($alliance['rank'] - $alliance['oldRank'])."</font>";

		$lang['ally_name'] = $alliance['allyName'];

		$lang['ally_members'] = $alliance['member'];

		if($type == 'points') $lang['ally_points'] = pretty_number($alliance[$type] / 1000);
		else $lang['ally_points'] = pretty_number($alliance[$type]);

		$lang['ally_mes'] = '';

		if($type == 'points') $lang['ally_members_points'] = pretty_number($alliance['average'] / 1000);
		else $lang['ally_members_points'] = pretty_number($alliance['average']);

		$lang['ally_tag'] = $alliance['allyTag'];

		$lang['body_values'] .= parsetemplate(gettemplate('stat_alliancetable'), $lang);
	}
} else {

	$lang['body_table'] = parsetemplate(gettemplate('stat_playertable_header'), $lang);


	$sql = 'SELECT {{table}}stat.rank'.ucfirst($type).' AS rank,
				{{table}}users.username AS userName,
				{{table}}stat.oldRank'.ucfirst($type).' AS oldRank,
				{{table}}users.id AS userID,
 				{{table}}users.ally_name AS allyName,
 				{{table}}alliance.ally_tag AS allyTag,
 				{{table}}stat.allyID AS allyID,
 				{{table}}stat.'.$type.' AS '.$type.',
				buddy1.owner AS buddya,
				buddy2.sender AS buddyb
			FROM {{table}}stat
			LEFT JOIN {{table}}users
				ON {{table}}stat.userID = {{table}}users.id
			LEFT JOIN {{table}}alliance
				ON {{table}}users.ally_id = {{table}}alliance.id
			LEFT JOIN {{table}}buddy
				AS buddy1
				ON {{table}}stat.userID = buddy1.sender
					AND buddy1.owner = '.WCF::getUser()->userID.'
					AND buddy1.active = 1
			LEFT JOIN {{table}}buddy
				AS buddy2
				ON {{table}}stat.userID = buddy2.owner
					AND buddy2.sender = '.WCF::getUser()->userID.'
					AND buddy2.active = 1
			WHERE rank'.ucfirst($type).' > '.$start.'
				AND rank'.ucfirst($type).' <= '.($start + 100).'
		 	ORDER BY rank'.ucfirst($type).' ASC,
		 			userID DESC';
	$players = doquery($sql, '');

	while($player = mysql_fetch_assoc($players)) {
		$lang['player_rank'] = $player['rank'];

		if($player['rank'] == $player['oldRank']) $lang['player_rankplus'] = "<font color=\"#87CEEB\">0</font>";
		else if($player['rank'] < $player['oldRank']) $lang['player_rankplus'] = "<font color=\"green\">+".abs($player['oldRank'] - $player['rank'])."</font>";
		else $lang['player_rankplus'] = "<font color=\"red\">-".abs($player['rank'] - $player['oldRank'])."</font>";

		if($player['userID'] == WCF::getUser()->userID) $lang['player_name'] = '<font color="limegreen">'.$player['userName'].'</font>';
		else if($player['allyID'] == WCF::getUser()->ally_id) $lang['player_name'] = '<font color="#77BEDB">'.$player['userName'].'</font>';
		else if($player['buddya'] || $player['buddyb']) $lang['player_name'] = '<font color="#FFFF99">'.$player['userName'].'</font>';
		else $lang['player_name'] = $player['userName'];


		$lang['player_points'] = pretty_number($player[$type]);

		$lang['player_mes'] = '<a href="messages.php?mode=write&id='.$player['userID'].'"><img src="'.$dpath.'img/m.gif" border="0" alt="Nachricht schreiben" /></a>';

	  	if($player['allyTag'] !== null) $lang['player_alliance'] = '<a href="alliance.php?mode=ainfo&tag='.$player['allyTag'].'">'.$player['allyTag'].'</a>';
		else $lang['player_alliance'] = '';

		$lang['body_values'] .= parsetemplate(gettemplate('stat_playertable'), $lang);
	}
}

display(parsetemplate(gettemplate('stat_body'), $lang), '');
?>
