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

//
// Esta funcion permite cambiar el planeta actual.
//
include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

if(!defined('BUILDLIST')) define('BUILDLIST', 50);
if(!defined('MESSAGEFOLDERS')) define('MESSAGEFOLDERS', 40);
if(!defined('IMPERIUM')) define('IMPERIUM', 20);
if(!defined('GALAXY_SCANS')) define('GALAXY_SCANS', 30);
if(!defined('NO_ADS')) define('NO_ADS', 55);

$features = unserialize($user['diliziumFeatures']);

if($_POST) {
	//$dilizium = $user['dilizium'];
	//$features = unserialize($user['diliziumFeatures']);

	// build list
	$buildListDays = abs(intval($_POST['buildList']));
	$buildListCosts = $buildListDays * BUILDLIST;

	if($buildListCosts > $user['dilizium']) $buildListDays = floor($user['dilizium'] / BUILDLIST);
	$buildListCosts = $buildListDays * BUILDLIST;
	$user['lostDilizium'] += $buildListCosts;
	$user['dilizium'] -= $buildListCosts;

	if($features['buildList'] < time()) $features['buildList'] = (time() + $buildListDays * 24 * 60 * 60);
	else $features['buildList'] += $buildListDays * 24 * 60 * 60;

	// message folders
	$messageFoldersDays = abs(intval($_POST['messageFolders']));
	$messageFoldersCosts = $messageFoldersDays * MESSAGEFOLDERS;

	if($messageFoldersCosts > $user['dilizium']) $messageFoldersDays = floor($user['dilizium'] / MESSAGEFOLDERS);
	$messageFoldersCosts = $messageFoldersDays * MESSAGEFOLDERS;
	$user['lostDilizium'] += $messageFoldersCosts;
	$user['dilizium'] -= $messageFoldersCosts;

	if($features['messageFolders'] < time()) $features['messageFolders'] = (time() + $messageFoldersDays * 24 * 60 * 60);
	else $features['messageFolders'] += $messageFoldersDays * 24 * 60 * 60;

	// imperium
	$imperiumDays = abs(intval($_POST['imperium']));
	$imperiumCosts = $imperiumDays * IMPERIUM;

	if($imperiumCosts > $user['dilizium']) $imperiumDays = floor($user['dilizium'] / IMPERIUM);
	$imperiumCosts = $imperiumDays * IMPERIUM;
	$user['lostDilizium'] += $imperiumCosts;
	$user['dilizium'] -= $imperiumCosts;

	if($features['imperium'] < time()) $features['imperium'] = (time() + $imperiumDays * 24 * 60 * 60);
	else $features['imperium'] += $imperiumDays * 24 * 60 * 60;

	if($imperiumDays) {
		$page .= '<script type="text/javascript">parent["LeftMenu"].location.reload();</script>';
	}

	// scans in galaxy
	$galaxyScansDays = abs(intval($_POST['galaxyScans']));
	$galaxyScansCosts = $galaxyScansDays * GALAXY_SCANS;

	if($galaxyScansCosts > $user['dilizium']) $galaxyScansDays = floor($user['dilizium'] / GALAXY_SCANS);
	$galaxyScansCosts = $galaxyScansDays * GALAXY_SCANS;
	$user['lostDilizium'] += $galaxyScansCosts;
	$user['dilizium'] -= $galaxyScansCosts;

	if($features['galaxyScans'] < time()) $features['galaxyScans'] = (time() + $galaxyScansDays * 24 * 60 * 60);
	else $features['galaxyScans'] += $galaxyScansDays * 24 * 60 * 60;

	// no ads
	$noAdsDays = abs(intval($_POST['noAds']));
	$noAdsCosts = $noAdsDays * NO_ADS;

	if($noAdsCosts > $user['dilizium']) $noAdsDays = floor($user['dilizium'] / NO_ADS);
	$noAdsCosts = $noAdsDays * NO_ADS;
	$user['lostDilizium'] += $noAdsCosts;
	$user['dilizium'] -= $noAdsCosts;

	if($features['noAds'] < time()) $features['noAds'] = (time() + $noAdsDays * 24 * 60 * 60);
	else $features['noAds'] += $noAdsDays * 24 * 60 * 60;

	// save
	$user['diliziumFeatures'] = serialize($features);

	$sql = "UPDATE ugml".LW_N."_users
			SET diliziumFeatures = '".$user['diliziumFeatures']."',
			lostDilizium = '".$user['lostDilizium']."'
			WHERE id = '".$user['id']."'";
	WCF::getDB()->registerShutdownUpdate($sql);
	
	WCF::getSession()->setUpdate(true);
}

$page .= '<script type="text/javascript">
			var costsM = 0;
			function calculateCosts(container) {
				var old = document.getElementById(container + "C").firstChild.data;
				var days = document.getElementById(container + "V").value;
				if(days < 0) {
					document.getElementById(container + "V").value = 0;
					days = 0;
				}
				var costsPerDay = document.getElementById(container + "D").firstChild.data;
				var costs = days * costsPerDay;
				document.getElementById(container + "C").firstChild.data = costs;
				var diff = costs - old;
				costsM += diff;
				document.getElementById("costs").firstChild.data = costsM;
				if(costsM > document.getElementById("dilizium").firstChild.data) document.getElementById("costs").style.color = "red";
				else document.getElementById("costs").style.color = "";
			}
			</script>';
$page .= '<form name="dilizium" action="dilizium.php" method="post">';
$page .= '<table width="470">';
$page .= '<tr>';
$page .= '<td class="c" colspan="4">Infos</td>';
$page .= '</tr>';
$page .= '<tr>';
$page .= '<td class="l" colspan="2">Aktueller Dilizium-Vorrat:</td>';
$page .= '<td class="l" colspan="2"><span id="dilizium">'.$user['dilizium'].'</span></td>';
$page .= '</tr>';
$page .= '<tr>';
$page .= '<td class="l" colspan="2">Dein Werbe-Link:</td>';
$page .= '<td class="l" colspan="2"><input name="linktext" type="text" style="width: 96%;" value="http://lost-worlds.net/ref.php?u='.WCF::getUser()->userID.'" onClick="this.form.linktext.select(); this.form.linktext.focus();" /></td>';
$page .= '</tr>';
$page .= '<tr>';
$page .= '<td class="c" colspan="4">Verwenden</td>';
$page .= '</tr>';
$page .= '<tr>';
$page .= '<td class="c">Zweck</td>';
$page .= '<td class="c">Kosten/Tag</td>';
$page .= '<td class="c">Tage</td>';
$page .= '<td class="c">Effektive Kosten</td>';
$page .= '</tr>';
$page .= '<tr>';
if($features['buildList'] > time()) $page .= '<td class="l"><span title="Noch ca. '.round(($features['buildList'] - time()) / (24 * 60 * 60)).' Tag(e) aktiviert">Bauliste</span></td>';
else $page .= '<td class="l"><span title="Nicht aktiviert">Bauliste</span></td>';
$page .= '<td class="l"><span id="buildListD">'.BUILDLIST.'</span></td>';
$page .= '<td class="l"><input id="buildListV" name="buildList" type="text" size="3" maxlength="2" onKeyUp="calculateCosts(\'buildList\');" /></td>';
$page .= '<td class="l"><span id="buildListC">0</span></td>';
$page .= '</tr>';
$page .= '<tr>';
if($features['messageFolders'] > time()) $page .= '<td class="l"><span title="Noch ca. '.round(($features['messageFolders'] - time()) / (24 * 60 * 60)).' Tag(e) aktiviert">Nachrichten-Ordner</span></td>';
else $page .= '<td class="l"><span title="Nicht aktiviert">Nachrichten-Ordner</span></td>';
$page .= '<td class="l"><span id="messageFoldersD">'.MESSAGEFOLDERS.'</span></td>';
$page .= '<td class="l"><input id="messageFoldersV" name="messageFolders" type="text" size="3" maxlength="2" onKeyUp="calculateCosts(\'messageFolders\');" /></td>';
$page .= '<td class="l"><span id="messageFoldersC">0</span></td>';
$page .= '</tr>';
$page .= '<tr>';
if($features['imperium'] > time()) $page .= '<td class="l"><span title="Noch ca. '.round(($features['imperium'] - time()) / (24 * 60 * 60)).' Tag(e) aktiviert">Imperiums-Ansicht</span></td>';
else $page .= '<td class="l"><span title="Nicht aktiviert">Imperiums-Ansicht</span></td>';
$page .= '<td class="l"><span id="imperiumD">'.IMPERIUM.'</span></td>';
$page .= '<td class="l"><input id="imperiumV" name="imperium" type="text" size="3" maxlength="2" onKeyUp="calculateCosts(\'imperium\');" /></td>';
$page .= '<td class="l"><span id="imperiumC">0</span></td>';
$page .= '</tr>';
$page .= '<tr>';
if($features['galaxyScans'] > time()) $page .= '<td class="l"><span title="Noch ca. '.round(($features['galaxyScans'] - time()) / (24 * 60 * 60)).' Tag(e) aktiviert">Spionageberichte in Galaxie-Ansicht</span></td>';
else $page .= '<td class="l"><span title="Nicht aktiviert">Spionageberichte in Galaxie-Ansicht</span></td>';
$page .= '<td class="l"><span id="galaxyScansD">'.GALAXY_SCANS.'</span></td>';
$page .= '<td class="l"><input id="galaxyScansV" name="galaxyScans" type="text" size="3" maxlength="2" onKeyUp="calculateCosts(\'galaxyScans\');" /></td>';
$page .= '<td class="l"><span id="galaxyScansC">0</span></td>';
$page .= '</tr>';
/*
$page .= '<tr>';
if($features['noAds'] > time()) $page .= '<td class="l"><span title="Noch ca. '.round(($features['noAds'] - time()) / (24 * 60 * 60)).' Tag(e) aktiviert">Werbefreiheit</span></td>';
else $page .= '<td class="l"><span title="Nicht aktiviert">Werbefreiheit</span></td>';
$page .= '<td class="l"><span id="noAdsD">'.NO_ADS.'</span></td>';
$page .= '<td class="l"><input id="noAdsV" name="noAds" type="text" size="3" maxlength="2" onKeyUp="calculateCosts(\'noAds\');" /></td>';
$page .= '<td class="l"><span id="noAdsC">0</span></td>';
$page .= '</tr>';*/
$page .= '<tr>';
$page .= '<td class="l" colspan="3">Gesamt</td>';
$page .= '<td class="l"><span id="costs">0</span></td>';
$page .= '</tr>';
$page .= '<tr>';
$page .= '<th colspan="4"><input type="submit" value="Absenden" /></th>';
$page .= '</tr>';
$page .= '</table>';
$page .= '</form>';
display($page);
?>