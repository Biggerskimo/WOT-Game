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
  //boddy.php

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
check_field_current($planetrow);

$_GET['bid'] = intval($_GET['bid']);
$_GET['s'] = intval($_GET['s']);
$_GET['u'] = intval($_GET['u']);

includeLang("buddy");

if($_GET['s'] == 1 && isset($_GET['bid']))
{//delete...

	$buddy = doquery("SELECT * FROM {{table}} WHERE id='".$_GET['bid']."'","buddy",true);
	if($buddy["owner"] == $user["id"]){
		if($buddy["active"]==0 && $_GET['a'] == 1){
			doquery("DELETE FROM {{table}} WHERE `id`='".$_GET['bid']."'","buddy");
		}elseif($buddy["active"]==1){
			doquery("DELETE FROM {{table}} WHERE `id`='".$_GET['bid']."'","buddy");
		}elseif($buddy["active"]==0){
			doquery("UPDATE {{table}} SET `active`=1 WHERE `id`='".$_GET['bid']."'","buddy");
		}
	}elseif($buddy["sender"] == $user["id"]){
			doquery("DELETE FROM {{table}} WHERE `id`='".$_GET['bid']."'","buddy");
	}

}
elseif($_POST["s"]==3 && $_POST["a"]==1 && $_POST["e"]==1 && isset($_POST["u"]))
{
/*
  Hacemos la comprobacion de que si existe ya una solicitud, etc...
*/
	$uid = $user["id"];
	$u = intval($_POST["u"]);

	$buddy = doquery("SELECT * FROM {{table}} WHERE sender={$uid} AND owner={$u} OR sender={$u} AND owner={$uid}",'buddy',true);

	if(!$buddy){

		$text = escapeString($_POST['text']);
		if(strlen($text)>5000) {
			message($lang['too_long'], $lang['Buddy_request'], 'javascript:history.back()');
		}
		doquery("INSERT INTO {{table}} SET sender={$uid}, owner={$u}, active=0, text='{$text}'",'buddy');
		message($lang['Request_sent'],$lang['Buddy_request'], "buddy.php");

	}else{ message($lang['A_request_exists_already_for_this_user'],$lang['Buddy_request']);}
/*
  <input type=hidden name=a value=1>
  <input type=hidden name=s value=3>
  <input type=hidden name=e value=1>
  <input type=hidden name=u value=".$u["id"].">

*/

}
/*
	Buddy list -_-U
	Bueno, consiste en una tabla llamada buddy. como la de notes.
	buddy.php se puede llamar comunmente sin variables, y mostrar la lista por default.
	la variable "a"="1" consiste en cambiar el tipo de lista.
	la "a"=2 permite mostrar el formulario para crear una entrada buddy incluyendo el "u"
	como id del usuario.
*/

$page = "<center><br>";

/*
  Formulario para enviar mensajes de solicitud de compañeros
*/
if($_GET['a'] == 2 && isset($_GET['u']))
{//formulario de solicitud

	$u = doquery("SELECT * FROM {{table}} WHERE id='".$_GET['u']."'","users",true);

	if(isset($u) && $u["id"] != $user["id"]){
		$page .= "<script src=\"js/LWUtil.class.js\" type=\"text/javascript\"></script>
		<form action=buddy.php method=post>
		<input type=hidden name=a value=1>
		<input type=hidden name=s value=3>
		<input type=hidden name=e value=1>
		<input type=hidden name=u value=".$u["id"].">
		<table width=519><tr>
		<td class=c colspan=2>{$lang['Buddy_request']}</td></tr>
		<tr><th>Jugador</th><th>".$u["username"]."</th></tr>
		<tr><th>{$lang['Request_text']} (<span id=\"CountLetters\">0</span> / 5000 {$lang['characters']})</th>
		<th><textarea name=text id=text cols=60 rows=10 onKeypress=\"lwUtil.checkLength(5000, 'text', 'CountLetters');\" onkeyup=\"lwUtil.checkLength(5000, 'text', 'CountLetters');\"></textarea>
		</th></tr>
		<tr><td class=c><a href=\"javascript:back();\">{$lang['Back']}</a></td>
		<td class=c><input type=submit value='{$lang['Send']}'></td></tr></table>
		</form></center></body></html>";
		display($page,'buddy');
	}elseif($u["id"] == $user["id"]){
		message($lang['You_cannot_ask_yourself_for_a_request'],$lang['Buddy_request']);
	}

}

$page .= "<table width=519><tr><td class=c colspan=6>";

//con a indicamos las solicitudes y con e las distiguimos
if($_GET['a'] ==1) $page .= ($_GET['e'] == 1) ? $lang['My_requests']:$lang['Anothers_requests']; else $page .= $lang['Buddy_list'];


$page .= "</td></tr>";

//Solo se muestra en la lista de compañeros.
if(!isset($_GET['a'])){
	$page .= "<tr><th colspan=6><a href=?a=1>{$lang['Requests']}</a></th></tr>";
	$page .= "<tr><th colspan=6><a href=?a=1&e=1>{$lang['My_requests']}</a></th></tr>";
	$page .= "<tr><td class=c></td>";
	$page .= "<td class=c>{$lang['Name']}</td>";
	$page .= "<td class=c>{$lang['Alliance']}</td>";
	$page .= "<td class=c>{$lang['Coordinates']}</td><td class=c>{$lang['Position']}</td>";
	$page .= "<td class=c></td></tr>";
}
	/*
		Loop para mostrar la lista de buddy
	*/
	if($_GET['a'] == 1) {
		$query = ($_GET['e'] == 1) ? "WHERE active=0 AND sender=".$user["id"] : "WHERE active=0 AND owner=".$user["id"];
	}else{
		$query = "WHERE active=1 AND sender=".$user["id"]." OR active=1 AND owner=".$user["id"];
	}
	$buddyrow = doquery("SELECT * FROM {{table}} ".$query,"buddy");

	while($b = mysql_fetch_array($buddyrow)){
		//para solicitudes
		if(!isset($_GET['i']) && isset($_GET['a'])){
			$page .= "<tr><td class=c></td><td class=c>{$lang['User']}</td><td class=c>{$lang['Alliance']}</td>
			<td class=c>{$lang['Coordinates']}</td><td class=c>{$lang['Text']}</td><td class=c></td></tr>";
		}

		$i++;
		$uid = ($b["owner"] == $user["id"]) ? $b["sender"] : $b["owner"];
		//query del user
		$u = doquery("SELECT {{table}}.id,username,galaxy,system,planet,onlinetime,ally_tag,ugml_alliance.ally_name FROM {{table}} LEFT JOIN ugml_alliance ON ugml_alliance.id = {{table}}.ally_id WHERE {{table}}.id=".$uid,"users",true);

		//$g = doquery("SELECT galaxy, system, planet FROM {{table}} WHERE id_planet=".$u["id_planet"],"galaxy",true);
		//$a = doquery("SELECT * FROM {{table}} WHERE id=".$uid,"aliance",true);

		$page .= "<tr>
		<th width=20>".$i."</th>
		<th><a href=messages.php?mode=write&id=".$u["id"].">".$u["username"]."</a></th>
		<th>";

		if($u["ally_tag"] != ''){//Alianza
			//$allyrow = doquery("SELECT id,ally_tag FROM {{table}} WHERE id=".$u["ally_id"],"alliance",true);
			//if($allyrow){
				$page .= '<a href="game/index.php?page=Alliance&allianceID='.$u['ally_id'].'">'.$u["ally_name"].'</a>';
			//}
		}

		$page .= "</th><th><a href=\"galaxy.php?g=".$u["galaxy"]."&s=".$u["system"]."\">";
		$page .= $u["galaxy"].":".$u["system"].":".$u["planet"]."</a></th>\n<th>";//Coordenadas del planeta principal
		/*
		  Conectado - texto:
		  Dependiendo del tiempo actual y el registrado en la base de datos, este indica si
		  se encuentra conectado o muestra un texto diciendo hace 15 min, o hace 30 min, On/Off
		*/
		if(isset($_GET['a'])){
			$page .= $b["text"];
		}else{
			$page .= "<font color=";

			if($u["onlinetime"] +60*10 >= time()){ $page .= "lime>{$lang['On']}"; }
			elseif($u["onlinetime"] +60*20 >= time()){$page .= "yellow>{$lang['15_min']}"; }
			else{$page .= "red>{$lang['Off']}";}

			$page .= "</font>";
		}

		$page .= "</th><th>";

		if(isset($_GET['a']) && isset($_GET['e'])){
			$page .= "<a href=?s=1&bid=".$b["id"].">{$lang['Delete_request']}</a>";
		}elseif(isset($_GET['a'])){
			$page .= "<a href=?s=1&bid=".$b["id"].">{$lang['Ok']}</a><br/>";
			$page .= "<a href=?a=1&s=1&bid=".$b["id"].">{$lang['Reject']}</a></a>";
		}else{ $page .= "<a href=?s=1&bid=".$b["id"].">{$lang['Delete']}</a>";}
		$page .= "</th></tr>";
	}
if(!isset($_GET['i']) && !isset($_GET['a'])){ $page .= "<th colspan=6>{$lang['There_is_no_request']}</th>";}

if($_GET['a'] ==1) $page .= "<tr><td class=c><a href=buddy.php>{$lang['Back']}</a></td></tr>";

$page .= "</table></center>";

display ($page,$lang['Buddy_list']);

// Created by Perberos. All rights reversed (C) 2006
?>
