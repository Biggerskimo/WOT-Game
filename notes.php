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
  //notes.php :: Notas


define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

if(!check_user()){ header("Location: login.$phpEx"); die();}

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

//lenguaje
includeLang('notes');

$lang['PHP_SELF'] = 'notes.'.$phpEx;

if($_POST["s"] == 1 || $_POST["s"] == 2){//Edicion y agregar notas

	$time = time();
	$priority = $_POST["u"];
	$title = ($_POST["title"]) ? $_POST["title"] : $lang['NoTitle'];
	$text = ($_POST["text"]) ? $_POST["text"] : $lang['NoText'];

	if($_POST["s"] ==1){
		doquery("INSERT INTO {{table}} SET owner={$user['id']}, time=".intval($time).", priority=".intval($priority).", title='".escapeString($title)."', text='".escapeString($text)."'","notes");
		message($lang['NoteAdded'], $lang['Please_Wait'],'notes.'.$phpEx,"3");
	}elseif($_POST["s"] == 2){
		/*
		  pequeño query para averiguar si la nota que se edita es del propio jugador
		*/
		$id = $_POST["n"];
		$note_query = doquery("SELECT * FROM {{table}} WHERE id=".intval($id)." AND owner=".$user["id"],"notes");

		if(!$note_query){ error($lang['notpossiblethisway'],$lang['Notes']); }

		doquery("UPDATE {{table}} SET time=".intval($time).", priority=".intval($priority).", title='".escapeString($title)."', text='".escapeString($text)."' WHERE id=".escapeString($id),"notes");
		message($lang['NoteUpdated'], $lang['Please_Wait'], 'notes.'.$phpEx, "3");
	}

}
elseif($_POST){//Borrar

	foreach($_POST as $a => $b){
		/*
		  Los checkbox marcados tienen la palabra delmes seguido del id.
		  Y cada array contiene el valor "y" para compro
		*/
		if(preg_match("/delmes/i",$a) && $b == "y"){

			$id = str_replace("delmes","",$a);
			$note_query = doquery("SELECT * FROM {{table}} WHERE id=".intval($id)." AND owner={$user['id']}","notes");
			//comprobamos,
			if($note_query){
				$deleted++;
				doquery("DELETE FROM {{table}} WHERE `id`=".intval($id).";","notes");// y borramos
			}
		}
	}
	if($deleted){
		$mes = ($deleted == 1) ? $lang['NoteDeleted'] : $lang['NoteDeleteds'];
		message($mes,$lang['Please_Wait'],'notes.'.$phpEx,"3");
	}else{header("Location: notes.$phpEx");}

}else{//sin post...
	if($_GET["a"] == 1){//crear una nueva nota.
		/*
		  Formulario para crear una nueva nota.
		*/

		$parse = $lang;

		$parse['c_Options'] = "<option value=2 selected=selected>{$lang['Important']}</option>
			  <option value=1>{$lang['Normal']}</option>
			  <option value=0>{$lang['Unimportant']}</option>";

		$parse['cntChars'] = '0';
		$parse['TITLE'] = $lang['Createnote'];
		$parse['text'] = '';
		$parse['title'] = '';
		$parse['inputs'] = '<input type=hidden name=s value=1>';

		$page .= parsetemplate(gettemplate('notes_form'), $parse);

		display($page,$lang['Notes'],false, '', false);

	}
	elseif($_GET["a"] == 2){//editar
		/*
		  Formulario donde se puestra la nota y se puede editar.
		*/
		$note = doquery("SELECT * FROM {{table}} WHERE owner={$user['id']} AND id=".intval($_GET['n']),'notes',true);

		if(!$note){ message($lang['notpossiblethisway'],$lang['Error']); }

		$cntChars = strlen($note['text']);

		$SELECTED[$note['priority']] = ' selected="selected"';

		$parse = array_merge($note,$lang);

		$parse['c_Options'] = "<option value=2{$SELECTED[2]}>{$lang['Important']}</option>
			  <option value=1{$SELECTED[1]}>{$lang['Normal']}</option>
			  <option value=0{$SELECTED[0]}>{$lang['Unimportant']}</option>";

		$parse['cntChars'] = $cntChars;
		$parse['TITLE'] = $lang['Editnote'];
		$parse['inputs'] = '<input type=hidden name=s value=2><input type=hidden name=n value='.$note['id'].'>';

		$page .= parsetemplate(gettemplate('notes_form'), $parse);

		display($page,$lang['Notes'],false, '', false);

	}
	else{//default

		$notes_query = doquery("SELECT * FROM {{table}} WHERE owner={$user['id']} ORDER BY time DESC",'notes');
		//Loop para crear la lista de notas que el jugador tiene
		$count = 0;
		$parse=$lang;
		while($note = mysql_fetch_array($notes_query)){
			$count++;
			//Colorea el titulo dependiendo de la prioridad
			if($note["priority"] == 0){ $parse['NOTE_COLOR'] = "lime";}//Importante
			elseif($note["priority"] == 1){ $parse['NOTE_COLOR'] = "yellow";}//Normal
			elseif($note["priority"] == 2){ $parse['NOTE_COLOR'] = "red";}//Sin importancia

			//fragmento de template
			$parse['NOTE_ID'] = $note['id'];
			$parse['NOTE_TIME'] = date("Y-m-d h:i:s",$note["time"]);
			$parse['NOTE_TITLE'] = $note['title'];
			$parse['NOTE_TEXT'] = strlen($note['text']);

			$list .= parsetemplate(gettemplate('notes_body_entry'), $parse);

		}

		if($count == 0){
			$list .= "<tr><th colspan=4>{$lang['ThereIsNoNote']}</th>\n";
		}

		$parse = $lang;
		$parse['BODY_LIST'] = $list;
		//fragmento de template
		$page .= parsetemplate(gettemplate('notes_body'), $parse);

		display($page,$lang['Notes'],false, '', false);
	}
}

// Created by Perberos. All rights reversed (C) 2006
?>
