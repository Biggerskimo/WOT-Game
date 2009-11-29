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

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

include($ugamela_root_path.'includes/planet_toggle.'.$phpEx);//Esta funcion permite cambiar el planeta actual.
includeLang('search');
$i = 0;
//creamos la query
$_POST["searchtext"] = mysql_escape_string($_POST["searchtext"]);
switch($_POST['type']){
	case "playername":
		$table = gettemplate('search_user_table');
		$row = gettemplate('search_user_row');
		$search = doquery("SELECT {{table}}.*, ugml_alliance.ally_tag FROM {{table}} LEFT JOIN ugml_alliance ON ugml_alliance.id = {{table}}.ally_id WHERE username LIKE '%".$_POST['searchtext']."%' LIMIT 30;","users");
	break;
	case "planetname":
		$table = gettemplate('search_user_table');
		$row = gettemplate('search_user_row');
		$search = doquery("SELECT * FROM {{table}} WHERE name LIKE '%".$_POST['searchtext']."%' LIMIT 30",'planets');
	break;
	case "allytag":
		$table = gettemplate('search_ally_table');
		$row = gettemplate('search_ally_row');
		$search = doquery("SELECT * FROM {{table}} WHERE ally_tag LIKE '%".$_POST['searchtext']."%' LIMIT 30","alliance");
	break;
	case "allyname":
		$table = gettemplate('search_ally_table');
		$row = gettemplate('search_ally_row');
		$search = doquery("SELECT * FROM {{table}} WHERE ally_name LIKE '%".$_POST['searchtext']."%' LIMIT 30","alliance");
	break;
	default:
		$table = gettemplate('search_user_table');
		$row = gettemplate('search_user_row');
		$search = doquery("SELECT * FROM {{table}} WHERE username LIKE '%".$_POST['searchtext']."%' LIMIT 30","users");
}
/*
  Esta es la tecnica de, "el ahorro de queries".
  Inventada por Perberos :3
  ...pero ahora no... porque tengo sueño ;P
*/
if(isset($_POST['searchtext']) && isset($_POST['type'])){
	while($r = mysql_fetch_array($search, MYSQL_BOTH)){

		if($_POST['type']=='playername'||$_POST['type']=='planetname'){
			$s=$r;
			//para obtener el nombre del planeta
			if ($_POST['type'] == "planetname")
			{
				$pquery = doquery("SELECT {{table}}.*, ugml_alliance.ally_tag, ugml_alliance.id AS ally_id FROM {{table}} LEFT JOIN ugml_alliance ON ugml_alliance.id = {{table}}.ally_id WHERE {{table}}.id = {$s['id_owner']}","users",true);
				$s['planet_name'] = $s['name'];
				$s['username'] = $pquery['username'];
				//$s['ally_name'] = ($pquery['ally_tag']!='')?"<a href=\"alliance.php?mode=ainfo&tag=".urlencode($pquery['ally_tag'])."\">{$pquery['ally_name']}</a>":'';
				$s['ally_name'] = ($pquery['ally_tag']!='')?"<a href=\"game/index.php?page=Alliance&allianceID=".$pquery['ally_id']."\">{$pquery['ally_name']}</a>":'';
			}else{
				$pquery = doquery("SELECT name FROM {{table}} WHERE id = {$s['id_planet']}","planets",true);
				$s['planet_name'] = $pquery['name'];
				$s['ally_name'] = ($s['ally_tag']!='')?"<a href=\"game/index.php?page=Alliance&allianceID=".$s['ally_id']."\">{$s['ally_tag']}</a>":'';
			}
			//ahora la alianza
			if($s['ally_id']!=0&&$s['ally_request']==0){
				$aquery = doquery("SELECT ally_name FROM {{table}} WHERE id = {$s['ally_id']}","alliance",true);
			}else{
				$aquery = array();
			}


			$s['position'] = ($s['rank']==0)?'':"<a href=\"stat.php?start={$s['rank']}\">{$s['rank']}</a>";
			$s['dpath'] = $dpath;
			$s['coordinated'] = "{$s['galaxy']}:{$s['system']}:{$s['planet']}";
			$s['buddy_request'] = $lang['buddy_request'];
			$s['write_a_messege'] = $lang['write_a_messege'];
			$result_list .= parsetemplate($row, $s);
		}elseif($_POST['type']=='allytag'||$_POST['type']=='allyname'){
			$s=$r;
			//$s['ally_tag'] = "<a href=\"alliance.php?mode=ainfo&tag={$s['ally_tag']}\">{$s['ally_tag']}</a>";
			$s['ally_tag'] = "<a href=\"game/index.php?page=Alliance&amp;allianceID=".$s['id']."\">{$s['ally_tag']}</a>";
			
			if(!WCF::getUser()->ally_id && !WCF::getUser()->ally_request_id) {
				$s['ally_actions'] = '<th><a href="game/index.php?form=AllianceApply&amp;allianceID='.$r['id'].'"><img src="'.DPATH.'pic/key.gif" alt="Diplomatie" /></a></th>';
			// interrelation apply
			} else if(WCF::getUser()->ally_id && LWCore::getAlliance()->getRank(true, 6)) {
				$s['ally_actions'] = '<th><a href="game/index.php?form=AllianceInterrelationApply&amp;allianceID='.$r['id'].'&amp;allianceID2='.WCF::getUser()->ally_id.'"><img src="'.DPATH.'pic/key.gif" alt="Diplomatie" /></a></th>';
			// no actions	
			} else {
				$s['ally_actions'] = '<th></th>';
			}
			$result_list .= parsetemplate($row, $s);
		}
	}
	if($result_list!=''){
		$lang['result_list'] = $result_list;
		$search_results = parsetemplate($table, $lang);
	}
}

//el resto...
$lang['type_playername'] = ($_POST["type"] == "playername") ? " SELECTED" : "";
$lang['type_planetname'] = ($_POST["type"] == "planetname") ? " SELECTED" : "";
$lang['type_allytag'] = ($_POST["type"] == "allytag") ? " SELECTED" : "";
$lang['type_allyname'] = ($_POST["type"] == "allyname") ? " SELECTED" : "";
$lang['searchtext'] = $searchtext;
$lang['search_results'] = $search_results;
//esto es algo repetitivo ... w
$page = parsetemplate(gettemplate('search_body'), $lang);
display($page,$lang['Search']);

/*
  bueno, no se pudo hacer mucho que digamos ... La proxima vez, será~
*/

// Created by Perberos. All rights reversed (C) 2006
?>