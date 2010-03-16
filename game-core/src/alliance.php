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

$planetrow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_planet']}",'planets',true);
$galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet={$planetrow['id']}",'galaxy',true);
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
check_field_current($planetrow);

includeLang('alliance');

function MessageForm($title,$mes,$dest='',$button_name='Aceptar',$useRE=false){

	return "<center><table width=519><form action=\"$dest\" method=POST>
	<tr><td class=c colspan=2>$title</td></tr><tr><th colspan=2>$mes".
	(($useRE)?'</th></tr><tr><th><center>':'')
	."<input type=submit value=\"$button_name\"></th></tr></table></form></center>";

}

/*
  Alianza consiste en tres partes.
  La primera es la comun. Es decir, no se necesita comprobar si se esta en una alianza o no.
  La segunda, es sin alianza. Eso implica las solicitudes.
  La ultima, seria cuando ya se esta dentro de una.
*/

/**
 * Allianzinfo
 */

if($_GET['mode'] == 'ainfo'){
	//Evitamos errores casuales xD
	//query
	if(isset($_GET['tag'])){
		$allyrow = doquery("SELECT * FROM {{table}} WHERE ally_tag='".$_GET['tag']."'","alliance",true);
	}elseif(is_numeric($_GET['a'])){
		$allyrow = doquery("SELECT * FROM {{table}} WHERE id='".$_GET['a']."'","alliance",true);
	}else{message($lang['There_is_not_alliance'],$lang['Alliance_information']);}
	//Si no existe
	if(!$allyrow){
		message($lang['There_is_not_alliance'],$lang['Alliance_information']);
	}
	extract($allyrow);

	//imagen
	if($ally_image != ""){
		$ally_image = "<tr><th colspan=2><img src=\"{$ally_image}\"></td></tr>";
	}
	//Descripcion
	if($ally_description != ""){
		$ally_description = "<tr><th colspan=2 height=100>{$ally_description}</th></tr>";
	}
	//Pagina web (link)
	if($ally_web != ""){
		$ally_web ="<tr>
		<th>{$lang['Initial_page']}</th>
		<th><a href=\"{$ally_web}\">{$ally_web}</a></th>
		</tr>";
	}

	$lang['ally_member_scount'] = $ally_members;
	$lang['ally_name'] = $ally_name;
	$lang['ally_tag'] = $ally_tag;

	//codigo raro
	$patterns[]     = "#\[fc\]([a-z0-9\#]+)\[/fc\](.*?)\[/f\]#Ssi";
	$replacements[] = '<font color="\1">\2</font>';
	$patterns[]     = '#\[img\](.*?)\[/img\]#Smi';
	$replacements[] = '<img src="\1" alt="\1" style="border:0px;" />';
	$patterns[]     = "#\[fc\]([a-z0-9\#\ \[\]]+)\[/fc\]#Ssi";
	$replacements[] = '<font color="\1">';
	$patterns[]     = "#\[/f\]#Ssi";
	$replacements[] = '</font>';
	$ally_description = preg_replace($patterns, $replacements, $ally_description);

	$lang['ally_description'] = nl2br($ally_description);
	$lang['ally_image'] = $ally_image;
	$lang['ally_web'] = $ally_web;

	$page .= parsetemplate(gettemplate('alliance_ainfo'), $lang);
	display($page,str_replace('%s',$ally_name,$lang['Info_of_Alliance']));

}

if($user['ally_id'] == 0){

/**
 * create alliance
 */
if($_GET['mode'] == 'make' && $user['ally_request'] == 0){
	/*
	  Aca se crean las alianzas...
	*/
	if($_GET["yes"] == 1 && $_SERVER['REQUEST_METHOD'] == "POST"){
		/*
		  Por el momento solo estoy improvisando, luego se perfeccionara el sistema :)
		  Creo que aqui se realiza una query para comprovar el nombre, y luego le pregunta si es el tag correcto...
		*/
		$_POST['atag'] = escapeString($_POST['atag']);
		$_POST['aname'] = escapeString($_POST['aname']);
		if(!$_POST['atag']){ message($lang['have_not_tag'],$lang['make_alliance']);}
		if(!$_POST['aname']){ message($lang['have_not_name'],$lang['make_alliance']);}

		$tagquery = doquery("SELECT * FROM {{table}} WHERE ally_tag='{$_POST['atag']}'",'alliance',true);

		if($tagquery){

			message(str_replace('%s',$_POST['atag'],$lang['always_exist']),$lang['make_alliance']);

		}

		doquery("INSERT INTO {{table}} SET
			`ally_name`='".escapeString($_POST['aname'])."',
			`ally_tag`='".escapeString($_POST['atag'])."',
			`ally_owner`='{$user['id']}',
			`ally_owner_range`='Fundador',
			 ally_points={$user['points_points']},
			 ally_points_tech='{$user['points_tech']}',
			 ally_points_fleet='{$user['points_fleet']}',
			`ally_members`='1',
			`ally_register_time`=".time()
			,"alliance");

		$allyquery = doquery("SELECT * FROM {{table}} WHERE ally_tag='{$_POST['atag']}'",'alliance',true);

		doquery("UPDATE {{table}} SET
			`ally_id`='{$allyquery['id']}',
			`ally_name`='{$allyquery['ally_name']}',
			`ally_register_time`='".time()."'
			WHERE `id`='{$user['id']}'","users");

		$page = MessageForm(str_replace('%s',$_POST['atag'],$lang['ally_maked']),

		str_replace('%s',$_POST['atag'],$lang['alliance_has_been_maked'])."<br><br>","",$lang['Ok']);

	}else{
		$page .= parsetemplate(gettemplate('alliance_make'), $lang);
	}

	display($page,$lang['make_alliance']);

}

/**
 * search alliances
 */

if($_GET['mode'] == 'search' && $user['ally_request'] == 0){//search one
	/*
	  Buscador de alianzas
	*/
	$parse = $lang;
	$lang['searchtext'] = $_POST['searchtext'];
	$page = parsetemplate(gettemplate('alliance_searchform'), $lang);

	if($_POST){//esta parte es igual que el buscador de search.php...

		//searchtext
		$_POST['searchtext'] = mysql_real_escape_string($_POST['searchtext']);
		$search = doquery("SELECT * FROM {{table}} WHERE ally_name LIKE '%{$_POST['searchtext']}%' or ally_tag LIKE '%{$_POST['searchtext']}%' LIMIT 30","alliance");

		if(mysql_num_rows($search)!=0){

			$template = gettemplate('alliance_searchresult_row');

			while($s = mysql_fetch_array($search)){
				$entry = array();
				$entry['ally_tag'] = "[<a href=\"alliance.php?mode=apply&allyid={$s['id']}\">{$s['ally_tag']}</a>]";
				$entry['ally_name'] = $s['ally_name'];
				$entry['ally_members'] = $s['ally_members'];

				$parse['result'] .= parsetemplate($template, $entry);

			}

			$page .= parsetemplate(gettemplate('alliance_searchresult_table'), $parse);
		}

	}

	display($page,$lang['search_alliance']);

}
/**
 * applications
 */
if($_GET['mode'] == 'apply' && $user['ally_request'] == 0){

	if(!is_numeric($_GET['allyid']) || !$_GET['allyid'] || $user['ally_request'] != 0 || $user['ally_id'] != 0){
		message($lang['it_is_not_posible_to_apply'],$lang['it_is_not_posible_to_apply']);
	}
	//pedimos la info de la alianza
	$allyrow = doquery("SELECT ally_tag, ally_request FROM {{table}} WHERE id='{$_GET['allyid']}'","alliance",true);

	if(!$allyrow){message($lang['it_is_not_posible_to_apply'],$lang['it_is_not_posible_to_apply']);}

	extract($allyrow);

	if($_POST['further'] == $lang['Send']){//esta parte es igual que el buscador de search.php...

		doquery("UPDATE {{table}} SET ally_request='".intval($_GET['allyid'])."', ally_request_text='".escapeString($_POST['text'])."', ally_register_time='".time()."' WHERE `id`='{$user['id']}'","users");
		//mensaje de cuando se envia correctamente el mensaje
		message($lang['apply_registered'],$lang['your_apply']);
		//mensaje de cuando falla el envio
		//message($lang['apply_cantbeadded'], $lang['your_apply']);

	}else{
		$text_apply = ($ally_request) ? $ally_request : $lang['There_is_no_a_text_apply'];
	}

	$parse = $lang;
	$parse['allyid'] = $_GET['allyid'];
	$parse['chars_count'] = strlen($text_apply);
	$parse['text_apply'] = $text_apply;
	$parse['Write_to_alliance'] = str_replace('%s',$ally_tag,$lang['Write_to_alliance']);

	$page = parsetemplate(gettemplate('alliance_applyform'), $parse);

	display($page,$lang['Write_to_alliance']);

}

/**
 * waiting for answer of a request
 */

if($user['ally_request'] != 0){//Esperando una respuesta

	//preguntamos por el ally_tag
	//$allyquery = doquery("SELECT ally_tag FROM {{table}} WHERE id='{$user['ally_request']}' ORDER BY `id`","alliance",true);

	$sql = "SELECT *
			FROM ugml_alliance
			WHERE id = ".$user['ally_request'];
	$allyquery = WCF::getDB()->getFirstRow($sql);
	
	extract($allyquery);
	if($_POST['bcancel']){

		doquery("UPDATE {{table}} SET `ally_request`=0 WHERE `id`=".$user['id'],"users");

		$lang['request_text'] = str_replace('%s',$ally_tag,$lang['Canceled_a_request_text']);
		$lang['button_text'] = $lang['Ok'];
		$page = parsetemplate(gettemplate('alliance_apply_waitform'), $lang);

	}else{

		$lang['request_text'] = str_replace('%s',$ally_tag,$lang['Waiting_a_request_text']);
		$lang['button_text'] = $lang['Delete_apply'];
		$page = parsetemplate(gettemplate('alliance_apply_waitform'), $lang);

	}

	display($page,"Tu solicitud");
}

/**
 * no alliance
 */

else{//Vista sin allianza
	/*
	  Vista normal de cuando no se tiene ni solicitud ni alianza
	*/
	$page .= parsetemplate(gettemplate('alliance_defaultmenu'), $lang);
	display($page,$lang['alliance']);
}

}//---------------------------------------------------------------------------------------------------------------------------------------------------

//
//  Parte de adentro de la alianza
//

/**
 * alliance deleted
 */

elseif($user['ally_id'] != 0 && $user['ally_request'] == 0){//Con alianza
	//query para la allyrow
	$ally = doquery("SELECT * FROM {{table}} WHERE id='{$user['ally_id']}'","alliance",true);

	if(!$ally){
		/*
		  Cuando una alianza no existe. Se arregla y redirige
		*/
		doquery("UPDATE {{table}} SET `ally_id`=0 WHERE `id`='{$user['id']}'","users");
		message($lang['ally_notexist'],$lang['your_alliance'],'alliance.php');

	}
	/*
		En esta parte comienzan las secciones cuando se esta dentro de una alianza.
		Creo que habria que cambiar un poquito el sistema, para mantener ordenada las diferentes
		secciones dentreo de cada mode... Aunque eso evitaria el andar probando bugz xD
	*/

/**
 * leave alliance
 */

if($_GET['mode'] == 'exit'){//Dejar alianza

	if($ally['ally_owner'] == $user['id']){
		message($lang['Owner_cant_go_out'],$lang['Alliance']);
	}
	//se sale de la alianza
	if($_GET['yes']==1){
		doquery("UPDATE {{table}} SET `ally_id`=0 WHERE `id`='{$user['id']}'","users");
		$lang['Go_out_welldone'] = str_replace("%s",$ally_name,$lang['Go_out_welldone']);
		$page = MessageForm($lang['Go_out_welldone'],"<br>",$PHP_SELF,$lang['Ok']);
		//Se quitan los puntos del user en la alianza
		doquery("UPDATE {{table}} SET
			ally_points=ally_points-{$user['points_points']}
			WHERE id={$user['ally_id']}","alliance");
	}else{
		//se pregunta si se quiere salir
		$lang['Want_go_out'] = str_replace("%s",$ally_name,$lang['Want_go_out']);
		$page = MessageForm($lang['Exit_of_this_alliance'],"<br>","?mode=exit&yes=1",$lang['Ok']);
	}
	display($page);

}
/**
 * members list
 */

if($_GET['mode'] == 'memberslist'){//Lista de miembros.
	/*
	  Lista de miembros.
	  Por lo que parece solo se hace una query fijandose los usuarios con el mismo ally_id.
	  seguido del query del planeta principal de cada uno para sacarle la posicion, pero
	  voy a ver si tambien agrego las cordenadas en el id user...
	*/
	//obtenemos el array de los rangos
	$ally_ranks = unserialize($ally['ally_ranks']);

	//comprobamos el permiso
	if($ally['ally_owner'] != $user['id']&&$ally_ranks[$user['ally_rank_id']-1][4]!=1){
		message($lang['Denied_access'],$lang['Members_list']);
	}

	//El orden de aparicion
	if($_GET['sort2']){
		if($_GET['sort1'] == 1){$sort = " ORDER BY `username`";}
		elseif($_GET['sort1'] == 2){$sort = " ORDER BY `username`";}
		elseif($_GET['sort1'] == 3){$sort = " ORDER BY `points`";}
		elseif($_GET['sort1'] == 4){$sort = " ORDER BY `ally_register_time`";}
		elseif($_GET['sort1'] == 5){$sort = " ORDER BY `onlinetime`";}
		else{$sort = " ORDER BY `id`";}

		if($_GET['sort2'] == 1){ $sort .= " DESC;";}
		elseif($_GET['sort2'] == 2){ $sort .= " ASC;";}
		$listuser = doquery("SELECT * FROM {{table}}users LEFT JOIN {{table}}stat ON {{table}}stat.userID = {{table}}users.id WHERE ally_id='{$user['ally_id']}'{$sort}",'');
	}else{
		$listuser = doquery("SELECT * FROM {{table}}users LEFT JOIN {{table}}stat ON {{table}}stat.userID = {{table}}users.id WHERE ally_id={$user['ally_id']}",'');
	}

	//contamos la cantidad de usuarios.
	$i=0;

	//Como es costumbre. un row template
	$template = gettemplate('alliance_memberslist_row');
	$page_list='';
	while($u = mysql_fetch_array($listuser)){
		$i++;
		$u['i'] = $i;
		//Online Offline Orine xD
		if($ally['ally_owner'] == $user['id'] || $ally_ranks[$user['ally_rank_id']-1][7]==1)  {
			if($u["onlinetime"] +60*10 >= time()){ $u["onlinetime"] = "lime>{$lang['On']}<"; }
			elseif($u["onlinetime"] +60*20 >= time()){$u["onlinetime"] = "yellow>{$lang['15_min']}<"; }
			else{$u["onlinetime"] = "red>{$lang['Off']}<";}
		} else $u["onlinetime"] = '';
		//Nombre de rango
		if($ally['ally_owner'] == $u['id']){
			$u["ally_range"] = ($ally['ally_owner_range']=='')?$lang['Founder']:$ally['ally_owner_range'];
		}elseif(isset($ally_ranks[$u['ally_rank_id']-1])){
			$u["ally_range"] = $ally_ranks[$u['ally_rank_id']-1][0];
		}else{
			$u["ally_range"] = $lang['Novate'];
		}

		$u["dpath"] = $dpath;
		$u['points'] = "".pretty_number($u['points'])."";

		$u['ally_register_time'] = date("Y-m-d h:i:s",$u['ally_register_time']);
		$page_list .= parsetemplate($template, $u);

	}
	//para cambiar el link de ordenar.
	if($_GET['sort2']==1){$s=2;}elseif($_GET['sort2']==2){$s=1;}else{$s=1;}

	if($i!= $ally['ally_members']){
		doquery("UPDATE {{table}} SET `ally_members`='{$i}' WHERE `id`='{$ally['id']}'",'alliance');
	}

	$parse = $lang;
	$parse['i'] = $i;
	$parse['s'] = $s;
	$parse['list'] = $page_list;

	$page .= parsetemplate(gettemplate('alliance_memberslist_table'), $parse);

	display($page,$lang['Members_list']);

}
/**
 * Create circular messages
 */

if($_GET['mode'] == 'circular'){
	/*
	  Mandar un correo circular.
	  creo que aqui tendria que ver yo como crear el sistema de mensajes...
	*/
	//un loop para mostrar losrangos
	$ally_ranks = unserialize($ally['ally_ranks']);
	if(!is_array($ally_ranks)) $ally_ranks = array();

	//comprobamos el permiso
	if($ally['ally_owner'] != $user['id']&&$ally_ranks[$user['ally_rank_id']-1][8]!=1){
		message($lang['Denied_access'],$lang['Send_circular_mail']);
	}

	if($_GET['sendmail'] == 1){
		/*
		  aca se envia el correo circular.
		  y se hace una comprobacion de quienes lo recibieron, ademas de mostrar su nombre.
		  "Los siguientes jugadores han recibido tu correo circular"
		  y un botoncito de aceptar que lleva al principio.
		*/
		if($_POST['r'] == 0){
			$sq = doquery("SELECT id,username FROM {{table}} WHERE ally_id='{$user['ally_id']}'","users");
		}else{
			$sq = doquery("SELECT id,username FROM {{table}} WHERE ally_id='{$user['ally_id']}' AND ally_rank_id='{$_POST['r']}'","users");
		}
		//looooooop
		$list = '';
		while($u = mysql_fetch_array($sq)){
			/*
			  Esta query lo hago precariamente, luego lo cambiare con un while o for en lenguage sql.
			*/
			require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
			$parser = MessageParser::getInstance();
			$parser->setOutputType('text/html');
			$message = $parser->parse($_POST['text'], false, false, false);
			doquery("INSERT INTO {{table}} SET
				`message_owner`='{$u['id']}',
				`message_sender`='{$user['id']}' ,
				`message_time`='".time()."',
				`message_type`='2',
				`message_from`='{$ally['ally_tag']}',
				`message_subject`='{$user['username']}',
				`message_text`='".escapeString($message)."'
				","messages");
			$list .= "{$u['username']} ";
		}
		//`message_subject`='{$ally['ally_tag']}',

		//doquery("SELECT id,username FROM {{table}} WHERE ally_id='{$user['ally_id']}' ORDER BY `id`","users");

		//$allyquery = doquery("SELECT * FROM {{table}} WHERE ally_tag='".$_POST['atag']."'",'alliance',true);
		//Agregamos uno al contador de mensajes nuevos
		doquery("UPDATE {{table}} SET new_message = new_message + 1 WHERE ally_id = '".$user['ally_id']."' AND ally_rank_id='".$_POST['r']."'","users");
		/*
		  Aca un mensajito diciendo que a quien se mando.
		*/
		$page = MessageForm($lang['Circular_sended'],$list,"alliance.php",$lang['Ok'],true);
		display($page,$lang['Send_circular_mail']);

	}

	$lang['r_list'] = "<option value=\"0\">{$lang['All_players']}</option>";
	foreach($ally_ranks as $a => $rangue){
		$lang['r_list'] .= "<option value=\"{$a}\">$rangue[0]</option>";
	}

	$page .= parsetemplate(gettemplate('alliance_circular'), $lang);

	display($page,$lang['Send_circular_mail']);

}

/**
 * ADMIN: edit rights
 */

if($_GET['mode'] == 'admin' && $_GET['edit'] == 'rights'){//Administrar leyes
	/*
	  El mio, lo quiero largo... xDDD
	  permite crear y borrar los diferentes rangos de miembros
	  con $d se borra un rango
	*/
	//obtenemos el array de los rangos
	$ally_ranks = unserialize($ally['ally_ranks']);
	//comprobamos el permiso
	if($ally['ally_owner'] != $user['id']&&$ally_ranks[($user['ally_rank_id']-1)][5]!=1){
		message($lang['Denied_access'],$lang['Members_list']);
	}
	//crear un nuevo rango
	elseif($_POST['newrangname']){
		$ally_ranks[] = array($_POST['newrangname'],0,0,0,0,0,0,0,0,0);
		$ally['ally_rank'] = serialize($ally_ranks);

		doquery("UPDATE {{table}} SET `ally_ranks`='{$ally['ally_rank']}' WHERE `id`={$ally['id']}","alliance");
	}
	//guardar los datos...
	elseif(!empty($_POST)&&$add!='name'){
		/*
		  Uf... la lista de rangos es una array dentro de un row, pero almacenada como
		  texto a travez de un serialize.
		*/
		//echo "".count($ally_ranks);
		$ally_ranks_new = $ally_ranks;
		foreach($ally_ranks as $u => $rank){
			foreach($rank as $r => $v){
				if($r!=9){$ally_ranks_new[$u][($r+1)] = ($_POST["u{$u}r{$r}"]=='on')?1:0;}
			}
		}
		$ally_ranks = $ally_ranks_new;
		$ally['ally_rank'] = escapeString(serialize($ally_ranks_new));

		doquery("UPDATE {{table}} SET `ally_ranks`='{$ally['ally_rank']}' WHERE `id`={$ally['id']}","alliance");
	}
	//borrar una entrada
	elseif(isset($_GET['d']) && $_GET['d'] != '') {

		unset($ally_ranks[$_GET['d']]);
		$ally['ally_rank'] = escapeString(serialize($ally_ranks));

		doquery("UPDATE {{table}} SET `ally_ranks`='{$ally['ally_rank']}' WHERE `id`={$ally['id']}","alliance");
	}

	if(count($ally_ranks)==0 || $ally_ranks==''){//si no hay rangos
		$list = "<th>{$lang['There_is_not_range']}</th>";
	}
	else{//Si hay rangos
		//cargamos la template de tabla
		$list = parsetemplate(gettemplate('alliance_admin_laws_head'), $lang);
		$template = gettemplate('alliance_admin_laws_row');
		//Creamos la lista de rangos
		$i=0;
		foreach($ally_ranks as $a => $b){

			//if($ally['ally_owner'] == $user['id']){

				//$i++;u2r5
				$lang['id'] = $a;
				if($ally['ally_owner'] == $user['id'] || $ally_ranks[($user['ally_rank_id']-1)][($i + 1)] != 0) {
					$lang['delete'] = "<a href=\"alliance.php?mode=admin&edit=rights&d={$a}\"><img src=\"{$dpath}pic/abort.gif\" alt=\"{$lang['Delete_range']}\" border=0></a>";
				} else $lang['delete'] = "";
				$lang['r0'] = $b[0];
				for($i = 0; $i < 9; $i++) {
					if($ally['ally_owner'] == $user['id'] || $ally_ranks[($user['ally_rank_id']-1)][($i + 1)] != 0) {
						if($b[($i + 1)]) $checked = 'checked="checked"';
						else $checked = '';

						$lang['r'.($i + 1)] = '<input type="checkbox" name="u'.$a.'r'.$i.'"'.$checked.' />';
					} else {
						$lang['r'.($i + 1)] = '-';
					}
				}
				/*$lang['r1'] = "<input type=checkbox name=\"u{$a}r0\"".(($b[1]==1)?' checked="checked"':'').">";//{$b[1]}
				$lang['r2'] = "<input type=checkbox name=\"u{$a}r1\"".(($b[2]==1)?' checked="checked"':'').">";
				$lang['r3'] = "<input type=checkbox name=\"u{$a}r2\"".(($b[3]==1)?' checked="checked"':'').">";
				$lang['r4'] = "<input type=checkbox name=\"u{$a}r3\"".(($b[4]==1)?' checked="checked"':'').">";
				$lang['r5'] = "<input type=checkbox name=\"u{$a}r4\"".(($b[5]==1)?' checked="checked"':'').">";
				$lang['r6'] = "<input type=checkbox name=\"u{$a}r5\"".(($b[6]==1)?' checked="checked"':'').">";
				$lang['r7'] = "<input type=checkbox name=\"u{$a}r6\"".(($b[7]==1)?' checked="checked"':'').">";
				$lang['r8'] = "<input type=checkbox name=\"u{$a}r7\"".(($b[8]==1)?' checked="checked"':'').">";
				$lang['r9'] = "<input type=checkbox name=\"u{$a}r8\"".(($b[9]==1)?' checked="checked"':'').">";
				*/
				$list .= parsetemplate($template, $lang);
			/*
			}else{
				$lang['id'] = $a;
				$lang['r0'] = $b[0];
				$lang['delete'] = '';
				$lang['r1'] = (($b[1]==1)?"<input type=checkbox name=\"u{$a}r0\" checked=\"checked\">":'-');
				$lang['r2'] = (($b[2]==1)?"<input type=checkbox name=\"u{$a}r1\" checked=\"checked\">":'-');
				$lang['r3'] = (($b[3]==1)?"<input type=checkbox name=\"u{$a}r2\" checked=\"checked\">":'-');
				$lang['r4'] = (($b[4]==1)?"<input type=checkbox name=\"u{$a}r3\" checked=\"checked\">":'-');
				$lang['r5'] = (($b[5]==1)?"<input type=checkbox name=\"u{$a}r4\" checked=\"checked\">":'-');
				$lang['r6'] = (($b[6]==1)?"<input type=checkbox name=\"u{$a}r5\" checked=\"checked\">":'-');
				$lang['r7'] = (($b[7]==1)?"<input type=checkbox name=\"u{$a}r6\" checked=\"checked\">":'-');
				$lang['r8'] = (($b[8]==1)?"<input type=checkbox name=\"u{$a}r7\" checked=\"checked\">":'-');
				$lang['r9'] = (($b[9]==1)?"<input type=checkbox name=\"u{$a}r8\" checked=\"checked\">":'-');

				$list .= parsetemplate($template, $lang);
			*/
			//}

		}
		//pie
		if(count($ally_ranks)!=0){
			$list .= parsetemplate(gettemplate('alliance_admin_laws_feet'), $lang);
		}
	}

	$lang['list'] = $list;
	$lang['dpath'] = $dpath;
	$page .= parsetemplate(gettemplate('alliance_admin_laws'), $lang);

	display($page,$lang['Law_settings']);

}

/**
 * ADMIN: edit alliance pages
 */
if($_GET['mode'] == 'admin' && $_GET['edit'] == 'ally'){//Administrar la alianza *pendiente urgente*

	if($_GET['t'] != 1 && $_GET['t'] != 2 && $_GET['t'] != 3) $_GET['t']= 1;

	require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
	$parser = MessageParser::getInstance();
	$parser->setOutputType('text/html');

	//post!
	if($_POST['options']){
		$ally['ally_owner_range'] = escapeString($parser->parse($_POST['owner_range'], false, false, false));;
		$ally['ally_web'] = escapeString($parser->parse($_POST['web'], false, false, false));
		$ally['ally_image'] = escapeString($parser->parse($_POST['image'], false, false, false));
		$ally['ally_request_notallow'] = escapeString($parser->parse($_POST['request_notallow'], false, false, false));;

		doquery("UPDATE {{table}} SET
			`ally_owner_range`='{$ally['ally_owner_range']}',
			`ally_image`='{$ally['ally_image']}',
			`ally_web`='{$ally['ally_web']}',
			`ally_request_notallow`='{$ally['ally_request_notallow']}'
			WHERE `id`='{$ally['id']}'","alliance");
	}elseif($_POST['t']){
		if($_POST['t'] == 3) $textToParse = $ally['ally_request'];
		else if($_POST['t'] == 2) $textToParse = $ally['ally_text'];
		else $textToParse = $ally['ally_description'];

		$message = $parser->parse($_POST['text'], false, false, false);
		$text = escapeString($message);
		$text = str_replace('<br />', '', $text);

		if($_POST['t'] == 3){
			$ally['ally_request'] = $_POST['text'];
			doquery("UPDATE {{table}} SET
				`ally_request`='".$text."'
				WHERE `id`='{$ally['id']}'","alliance");
		}elseif($_POST['t'] == 2){
			$ally['ally_text'] = $_POST['text'];
			doquery("UPDATE {{table}} SET
				`ally_text`='".$text."'
				WHERE `id`='{$ally['id']}'","alliance");
		}else{
			$ally['ally_description'] = $_POST['text'];
			doquery("UPDATE {{table}} SET
				`ally_description`='".$text."'
				WHERE `id`='{$ally['id']}'","alliance");
		}

	}
	$lang['dpath'] = $dpath;
	/*
	  Depende del $t, muestra el formulario para cada tipo de texto.
	*/
	if($_GET['t'] == 3){
		$lang['request_type'] = $lang['Request_text'];
	}elseif($_GET['t'] == 2){
		$lang['request_type'] = $lang['Internal_text'];
	}else{
		$lang['request_type'] = $lang['External_text'];
	}
	/*
	  Aqui se peticiona el texto de las alianzas
	*/
	if($_GET['t'] == 3){
		$lang['text'] = $ally['ally_request'];
	}elseif($_GET['t'] == 2){
		$lang['text'] = $ally['ally_text'];
	}else{
		$lang['text'] = $ally['ally_description'];
	}

	if($_GET['t'] == 3){
	}
	$lang['t'] = $_GET['t'];

	$lang['ally_web'] = $ally['ally_web'];
	$lang['ally_image'] = $ally['ally_image'];
	$lang['ally_request_notallow_0'] = (($ally['ally_request_notallow'] == 1) ? ' SELECTED' : '');
	$lang['ally_request_notallow_1'] = (($ally['ally_request_notallow'] == 0) ? ' SELECTED' : '');
	$lang['ally_owner_range'] = $ally['ally_owner_range'];
	$lang['Transfer_alliance'] = MessageForm($lang['Transfer_alliance'],"","?mode=admin&edit=give",$lang['Continue']);
	$lang['Disolve_alliance'] = MessageForm($lang['Disolve_alliance'],"","?mode=admin&edit=exit",$lang['Continue']);

	$page .= parsetemplate(gettemplate('alliance_admin'), $lang);
	display($page,$lang['Alliance_admin']);

}

/**
 * ADMIN: edit members
 */

if($_GET['mode'] == 'admin' && $_GET['edit'] == 'members'){//Administrar a los miembros
	/*
	  En la administrar a los miembros se pueden establecer los rangos
	  para dar los diferentes derechos "Leyes"
	*/
	$ally_ranks = unserialize($ally['ally_ranks']);
	if(!is_array($ally_ranks)) $ally_ranks = array();
	//comprobamos el permiso
	if($ally['ally_owner'] != $user['id']&&$ally_ranks[$user['ally_rank_id']-1][5]!=1){
		message($lang['Denied_access'],$lang['Members_list']);
	}

	/*
	  Kickear usuarios requiere el permiso numero 1
	*/
	if(isset($_GET['kick'])){
		if($ally['ally_owner'] != $user['id']&&$ally_ranks[$user['ally_rank_id']-1][1]!=1){
			message($lang['Denied_access'],$lang['Members_list']);
		}

		$u = doquery("SELECT * FROM {{table}} WHERE id='".$_GET['kick']."' LIMIT 1",'users',true);
		//kickeamos!
		if($u['ally_id']==$ally['id']&&$u['id']!=$ally['ally_owner']){
			doquery("UPDATE {{table}} SET `ally_id`='0' WHERE `id`='{$u['id']}'",'users');
		}
	}elseif(isset($_POST['newrang'])){
		//comprobamos la existencia del usuario...
		$q = doquery("SELECT * FROM {{table}} WHERE id='".$_GET['id']."' LIMIT 1",'users',true);
		//comprobacion de seguridad
		if((isset($ally_ranks[$_POST['newrang']-1]) || $_POST['newrang'] == 0) && $q['id'] != $ally['ally_owner']){
			doquery("UPDATE {{table}} SET `ally_rank_id`='".$_POST['newrang']."' WHERE id ='".$_GET['id']."'",'users');
		}

		//set owner...
		if($_POST['newrang'] == 'owner') {
			doquery("UPDATE {{table}} SET ally_owner = '".$_GET['id']."' WHERE id ='".$ally['id']."'", 'alliance');
		}
	}

	//obtenemos las template row
	$template = gettemplate('alliance_admin_members_row');
	$f_template = gettemplate('alliance_admin_members_function');
	//El orden de aparicion
	if($sort2){
		//agregar el =0 para las coordenadas...
		if($sort1 == 1){$sort = " ORDER BY `username`";}
		elseif($sort1 == 2){$sort = " ORDER BY `username`";}
		elseif($sort1 == 3){$sort = " ORDER BY `points`";}
		elseif($sort1 == 4){$sort = " ORDER BY `ally_register_time`";}
		elseif($sort1 == 5){$sort = " ORDER BY `onlinetime`";}
		else{$sort = " ORDER BY `id`";}

		if($sort2 == 1){ $sort .= " DESC;";}
		elseif($sort2 == 2){ $sort .= " ASC;";}
		$listuser = doquery("SELECT * FROM {{table}}users LEFT JOIN {{table}}stat ON {{table}}stat.userID = {{table}}users.id WHERE ally_id={$user['ally_id']}{$sort}",'');
	}else{
		$listuser = doquery("SELECT * FROM {{table}}users LEFT JOIN {{table}}stat ON {{table}}stat.userID = {{table}}users.id WHERE ally_id={$user['ally_id']}",'');
	}

	//contamos la cantidad de usuarios.
	$i=0;

	//Como es costumbre. un row template
	$page_list='';
	while($u = mysql_fetch_array($listuser)){
		$i++;
		$u['i'] = $i;
		//Dias de inactivos
		$u['points'] = "".pretty_number($u['points'])."";
		$days = floor(round(time()-$u["onlinetime"]) / (60 * 60 * 24));
		$u["onlinetime"] = $days.'d';
		//Nombre de rango
		if($ally['ally_owner'] == $u['id']){
			$ally_range = ($ally['ally_owner_range']=='')?$lang['Founder']:$ally['ally_owner_range'];
		}elseif($u['ally_rank_id']==0){
			$ally_range = $lang['Novate'];
		}else{//if(isset($ally_ranks[$u['ally_rank_id']])&&!isset($rank)){
			$ally_range = $ally_ranks[$u['ally_rank_id']-1][0];
		}

		/*
		  Aca viene la parte jodida...
		*/
		if($ally['ally_owner'] == $u['id']||$_GET['eank']==$u['id']){
			$u["functions"] = '';
		}
		elseif($ally_ranks[$user['ally_rank_id']-1][5]==1||$ally['ally_owner'] == $user['id']){
			$f['dpath'] = $dpath;
			$f['Expel_user'] = $lang['Expel_user'];
			$f['Set_range'] = $lang['Set_range'];
			$f['You_are_sure_want_kick_to'] = str_replace("%s",$u['username'],$lang['You_are_sure_want_kick_to']);
			$f['id'] = $u['id'];
			$u["functions"] = parsetemplate($f_template, $f);
		}else{
			$u["functions"] = '';
		}
		$u["dpath"] = $dpath;
		//por el formulario...
		if($_GET['rank']!=$u['id']){
			$u['ally_range'] = $ally_range;
		}else{
			$u['ally_range'] = '';
		}
		$u['ally_register_time'] = date("Y-m-d h:i:s",$u['ally_register_time']);
		$page_list .= parsetemplate($template, $u);
		if($_GET['rank']==$u['id']){
			$r['Rank_for'] = str_replace("%s",$u['username'],$lang['Rank_for']);
			$r['options'] .= "<option value=\"0\">{$lang['Novate']}</option>";
			if($ally['ally_owner'] == $user['id']) $r['options'] .= "<option value=\"owner\">".$ally['ally_owner_range']."</option>";

			foreach($ally_ranks as $a => $b){
				$r['options'] .= "<option value=\"".($a+1)."\"";
				if($u['ally_rank_id']-1==$a){$r['options'] .= ' selected=selected';}
				$r['options'] .= ">{$b[0]}</option>";
			}
			$r['id'] = $u['id'];
			$r['Save'] = $lang['Save'];
			$page_list .= parsetemplate(gettemplate('alliance_admin_members_row_edit'), $r);

		}


	}
	//para cambiar el link de ordenar.
	if($sort2==1){$s=2;}elseif($sort2==2){$s=1;}else{$s=1;}

	if($i!= $ally['ally_members']){
		doquery("UPDATE {{table}} SET `ally_members`='{$i}' WHERE `id`='{$ally['id']}'",'alliance');
	}

	$lang['memberslist'] = $page_list;
	$lang['s'] = $s;
	$page .= parsetemplate(gettemplate('alliance_admin_members_table'), $lang);

	display($page,$lang['Members_administrate']);

	// a=9 es para cambiar la etiqueta de la etiqueta.
	// a=10 es para cambiarleel nombre de la alianza
}
/**
 * ADMIN: edit requests
 */

if($_GET['mode'] == 'admin' && $_GET['edit'] == 'requests'){//Administrar solicitudes

	$ally_ranks = unserialize($ally['ally_ranks']);

	if($ally['ally_owner'] != $user['id'] && $ally_ranks[($user['ally_rank_id']-1)][3] != 1 && $ally_ranks[($user['ally_rank_id']-1)][5] != 1){
		message($lang['Denied_access'],$lang['Check_the_requests']);
	}

	if($_POST['action']==$lang['Ok']){
		
		$_GET['show'] = intval($_GET['show']);

		$u = doquery("SELECT * FROM {{table}} WHERE id = '".$_GET['show']."'",'users',true);
		//agrega los puntos al unirse el user a la alianza
		doquery("UPDATE {{table}} SET
			ally_members=ally_members+1,
			ally_points=ally_points+{$u['points_points']},
			ally_points_tech=ally_points_tech+{$u['points_tech']},
			ally_points_fleet=ally_points_fleet+{$u['points_fleet']}
			WHERE id='{$ally['id']}'",'alliance');
		doquery("UPDATE {{table}} SET
			ally_name='{$ally['ally_name']}',
			ally_request_text='',
			ally_request='0',
			ally_id='{$ally['id']}',
			new_message=new_message+1
			WHERE id='".$_GET['show']."'",'users');

		//Se envia un mensaje avizando...
		doquery("INSERT INTO {{table}} SET
			`message_owner`='".$_GET['show']."',
			`message_sender`='{$user['id']}' ,
			`message_time`='".time()."',
			`message_type`='2',
			`message_from`='{$ally['ally_tag']}',
			`message_subject`='Bewerbung',
			`message_text`='Der Spieler ".$u['username']." wurde in die Allianz aufgenommen.'
			","messages");
			header('Location:alliance.php?mode=admin&edit=requests');
			die();
	}elseif($_POST['action']==$lang['Repel']){
		doquery("UPDATE {{table}} SET ally_request_text='',ally_request='0',ally_id='0',new_message=new_message+1 WHERE id='".$_GET['show']."'",'users');
		//Se envia un mensaje avizando...
		doquery("INSERT INTO {{table}} SET
			`message_owner`='".$_GET['show']."',
			`message_sender`='{$user['id']}' ,
			`message_time`='".time()."',
			`message_type`='2',
			`message_from`='{$ally['ally_tag']}',
			`message_subject`='Bewerbung',
			`message_text`='Der Spieler ".$u['username']." wurde in die Allianz aufgenommen.'
			","messages");
			header('Location:alliance.php?mode=admin&edit=requests');
			die();
	}

	$row = gettemplate('alliance_admin_request_row');
	$i=0;
	$parse=$lang;
	$query = doquery("SELECT id,username,ally_request_text,ally_register_time FROM {{table}} WHERE ally_request='{$ally['id']}'",'users');
	while($r = mysql_fetch_array($query)){
		//recolectamos los datos del que se eligio.
		if(isset($_GET['show'])&&$r['id'] == $_GET['show']){
			$s['username'] = $r['username'];
			$s['ally_request_text']=nl2br($r['ally_request_text']);
			$s['id']=$r['id'];
		}
		//la fecha de cuando se envio la solicitud
		$r['time'] = date("Y-m-d h:i:s",$r['ally_register_time']);
		$parse['list'] .= parsetemplate($row, $r);
		$i++;
	}
	if($parse['list']==''){
		$parse['list'] = '<tr><th colspan=2>Keine Bewerbungen</th></tr>';
	}
	//Con $show
	if(isset($_GET['show'])&&$_GET['show']!=0&&$parse['list']!=''){
		//Los datos de la solicitud
		$s['Request_from'] = str_replace('%s',$s['username'],$lang['Request_from']);
		//el formulario
		$parse['request'] = parsetemplate(gettemplate('alliance_admin_request_form'), $s);
		$parse['request'] = parsetemplate($parse['request'], $lang);
	}else{
		$parse['request'] = '';
	}

	$parse['ally_tag'] = $ally['ally_tag'];
	$parse['Back'] = $lang['Back'];

	$parse['There_is_hanging_request'] = str_replace('%n',$i,$lang['There_is_hanging_request']);
	//$parse['list'] = $lang['Return_to_overview'];
	$page = parsetemplate(gettemplate('alliance_admin_request_table'), $parse);
	display($page,$lang['Check_the_requests']);

}

/**
 * ADMIN: edit name
 */

if($_GET['mode'] == 'admin' && $_GET['edit'] == 'name'){//renombrar alianza
	/*
	  Tan simple como renombrar algo...
	*/
	//obtenemos el array de los rangos
	$ally_ranks = unserialize($ally['ally_ranks']);
	//comprobamos el permiso
	if($ally['ally_owner'] != $user['id']&&$ally_ranks[$user['ally_rank_id']-1][5]!=1){
		message($lang['Denied_access'],$lang['Members_list']);
	}
	$_POST['newname'] = escapeString($_POST['newname']);
	if($_POST['newname']){
		/*
		  Si bien, se tendria que confirmar, no tengo animos para hacerlo mas detallado...
		  sorry :(
		*/
		$ally['ally_name'] = $_POST['newname'];
		doquery("UPDATE {{table}} SET `ally_name`='{$_POST['newname']}' WHERE `id`='{$user['ally_id']}'","alliance");
		doquery("UPDATE {{table}} SET `ally_name`='{$_POST['newname']}' WHERE `ally_id`='{$ally['id']}'","users");

	}
	//vista normal...
	$parse['question'] = str_replace('%s',$ally['ally_name'],$lang['How_you_will_call_the_alliance_in_the_future']);
	$parse['New_name'] = $lang['New_name'];
	$parse['Change'] = $lang['Change'];
	$parse['name'] = 'newname';
	$parse['Return_to_overview'] = $lang['Return_to_overview'];
	$page .= parsetemplate(gettemplate('alliance_admin_rename'), $parse);
	display($page,$lang['Alliance_admin']);

}

/**
 * ADMIN: edit tag
 */

if($_GET['mode'] == 'admin' && $_GET['edit'] == 'tag'){//renombrar etiqueta de la alianza
	/*
	  Tan simple como renombrar algo...
	*/
	//obtenemos el array de los rangos
	$ally_ranks = unserialize($ally['ally_ranks']);
	//comprobamos el permiso
	if($ally['ally_owner'] != $user['id']&&$ally_ranks[$user['ally_rank_id']-1][5]!=1){
		message($lang['Denied_access'],$lang['Members_list']);
	}
	$_POST['newtag'] = escapeString($_POST['newtag']);
	if($_POST['newtag']){
		/*
		  Si bien, se tendria que confirmar, no tengo animos para hacerlo mas detallado...
		  sorry :(
		*/
		$ally['ally_tag'] = $_POST['newtag'];
		doquery("UPDATE {{table}} SET `ally_tag`='{$_POST['newtag']}' WHERE `id`='{$user['ally_id']}'","alliance");

	}
	//vista normal...
	$parse['question'] = str_replace('%s',$ally['ally_tag'],$lang['How_you_will_call_the_alliance_in_the_future']);
	$parse['New_name'] = $lang['New_name'];
	$parse['Change'] = $lang['Change'];
	$parse['name'] = 'newtag';
	$parse['Return_to_overview'] = $lang['Return_to_overview'];
	$page .= parsetemplate(gettemplate('alliance_admin_rename'), $parse);
	display($page,$lang['Alliance_admin']);/**/

}

/**
 * ADMIN: delete alliance
 */

if($_GET['mode'] == 'admin' && $_GET['edit'] == 'exit'){//disolver una alianza

	//obtenemos el array de los rangos
	$ally_ranks = unserialize($ally['ally_ranks']);
	//comprobamos el permiso
	if($ally['ally_owner'] != $user['id']&&$ally_ranks[$user['ally_rank_id']-1][1]!=1){
		message($lang['Denied_access'],$lang['Members_list']);
	}
	/*
	  Si bien, se tendria que confirmar, no tengo animos para hacerlo mas detallado...
	  sorry :(
	*/
	if(!isset($_GET['confirm'])) {
		message('<a href="alliance.php?mode=admin&amp;edit=exit&amp;confirm=1">L&ouml;schen best&auml;tigen!</a>', 'Best&auml;tigung');
	}
	doquery("DELETE FROM {{table}} WHERE id='{$ally['id']}'","alliance");
	header('location: alliance.php');

}

/**
 * default page
 */

{//Default *falta revisar...*
	/*
	  Cuando se puede apreciar la alianza propia...
	  Realizamos el query para pedir todos los datos.
	  Pero hay que ver el tema de los permisos.
	*/

	//$count = doquery("SELECT COUNT(DISTINCT(id)) FROM {{table}} WHERE ally_id=".$user['ally_id'].";","users",true);
	/*
	  En esta array se encuentran los permisos de un rango de la alianza.
	  Utiliza un serialize y unserialize para extraer la array y devolverla
	*/
	if($ally['ally_owner'] != $user['id']){
	$ally_ranks = unserialize($ally['ally_ranks']);
	}
	//if($user['id'] == 143) die(print_r(unserialize($ally['ally_ranks']), true));
	//Imagen de la alianza
	if($ally['ally_ranks'] != ''){
		$ally['ally_ranks'] = "<tr><td colspan=2><img src=\"{$ally['ally_image']}\"></td></tr>";
	}
	//temporalmente...
	if($ally['ally_owner'] == $user['id']){
		$range = ($ally['ally_owner_range']!='')?$lang['Founder']:$ally['ally_owner_range'];
	}elseif($user['ally_rank_id']!= 0){
		$range = $ally_ranks[($user['ally_rank_id']-1)][0];
	}else{$range = $lang['member'];}

	//Link de la lista de miembros
	if($ally['ally_owner'] == $user['id'] || $ally_ranks[($user['ally_rank_id']-1)][4] != 0){
		$lang['members_list'] = " (<a href=\"?mode=memberslist\">{$lang['Members_list']}</a>)";
	}else{$lang['members_list'] = '';}

	//El link de adminstrar la allianza
	if($ally['ally_owner'] == $user['id'] || $ally_ranks[($user['ally_rank_id']-1)][5] != 0){
		$lang['alliance_admin'] = " (<a href=\"?mode=admin&edit=ally\">{$lang['Alliance_admin']}</a>)";
	}else{$lang['alliance_admin'] = '';}

	//El link de enviar correo circular
	if($ally['ally_owner'] == $user['id'] || $ally_ranks[($user['ally_rank_id']-1)][8] != 0){
		$lang['send_circular_mail'] = "<tr><th>{$lang['Circular_message']}</th><th><a href=\"?mode=circular\">{$lang['Send_circular_mail']}</a></th></tr>";
	}else{$lang['send_circular_mail'] = '';}


	//El link para ver las solicitudes
	$lang['requests'] = '';
	$request = doquery("SELECT id FROM {{table}} WHERE ally_request='{$ally['id']}'",'users');
	$request_count = mysql_num_rows($request);
	if($request_count!=0){
	if($ally['ally_owner'] == $user['id']|| $ally_ranks[($user['ally_rank_id']-1)][3] != 0)
		$lang['requests'] = "<tr><th>{$lang['Requests']}</th><th><a href=\"alliance.php?mode=admin&edit=requests\">{$request_count} {$lang['XRequests']}</a></th></tr>";
	}
	if($ally['ally_owner'] != $user['id']){
		$lang['ally_owner'] .= MessageForm($lang['Exit_of_this_alliance'],"","?mode=exit",$lang['Continue']);
	}else{
		$lang['ally_owner'] .= '';
	}
	//La imagen de logotipo
	$lang['ally_image'] = ($ally['ally_image'] != '')?
		"<tr><th colspan=2><img src=\"{$ally['ally_image']}\"></td></tr>":'';

	//$ally_image =
	$lang['range'] = $range;

	//codigo raro
	$patterns[]     = "#\[fc\]([a-z0-9\#]+)\[/fc\](.*?)\[/f\]#Ssi";
	$replacements[] = '<font color="\1">\2</font>';
	$patterns[]     = '#\[img\](.*?)\[/img\]#Smi';
	$replacements[] = '<img src="\1" alt="\1" style="border:0px;" />';
	$patterns[]     = "#\[fc\]([a-z0-9\#\ \[\]]+)\[/fc\]#Ssi";
	$replacements[] = '<font color="\1">';
	$patterns[]     = "#\[/f\]#Ssi";
	$replacements[] = '</font>';
	$ally['ally_description'] = preg_replace($patterns, $replacements, $ally['ally_description']);
	$lang['ally_description'] = nl2br($ally['ally_description']);

	$ally['ally_text'] = preg_replace($patterns, $replacements, $ally['ally_text']);
	$lang['ally_text'] = nl2br($ally['ally_text']);

	$lang['ally_web'] = $ally['ally_web'];
	$lang['ally_tag'] = $ally['ally_tag'];
	$lang['ally_members'] = $ally['ally_members'];
	$lang['ally_name'] = $ally['ally_name'];

	$page .= parsetemplate(gettemplate('alliance_frontpage'), $lang);
	display($page,$lang['your_alliance']);

}

}//---------------------------------------------------------------------------------------------------------------------------------------------------

//
//  Parte final, el resto de las funciones
//


//...





//
// I SAY END!
//

// Created by Perberos. All rights reversed (C) 2006
?>
