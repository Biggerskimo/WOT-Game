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
 // debug.class.php ::  Clase Debug, maneja reporte de eventos

if(!defined('INSIDE')){ die("attemp hacking");}
//
//  Experiment code!!!
//
/*vamos a experimentar >:)
  le veo futuro a las classes, ayudaria mucho a tener un codigo mas ordenado...
  que esperabas!!! soy newbie!!! D':<
*/

class debug
{
	var $log,$numqueries;

	function debug()
	{
		$this->vars = $this->log = '';
		$this->numqueries = 0;
	}

	function add($mes)
	{
		$this->log .= $mes;
		$this->numqueries++;
	}

	function echo_log()
	{	global $ugamela_root_path;
		echo  "<br><table><tr><td class=k colspan=4><a href=".$ugamela_root_path."admin/settings.php>Debug log</a>:</td></tr>".$this->log."</table>";
		die();
	}
	
	function error($message,$title)
	{
		global $link,$game_config;
		if($game_config['debug']==1){
			echo "<h2>$title</h2><br><font color=red>$message</font><br><hr>";
			echo  "<table>".$this->log."</table>";
		}
		//else{
			//A futuro, se creara una tabla especial, para almacenar
			//los errores que ocurran.
			global $user,$ugamela_root_path,$phpEx;
			include($ugamela_root_path . 'config.'.$phpEx);
			if(!$link) die('mySQL no esta disponible por el momento, sentimos el inconveniente...');
			$query = "INSERT INTO {{table}} SET
				`error_sender` = '{$user['id']}' ,
				`error_time` = '".time()."' ,
				`error_type` = '{$title}' ,
				`error_text` = '".mysql_escape_string($message)."';";
			$sqlquery = mysql_query(str_replace("{{table}}", $dbsettings["prefix"].'errors',$query))
				or die(mysql_error());
			$query = "explain select * from {{table}}";
			$q = mysql_fetch_array(mysql_query(str_replace("{{table}}", $dbsettings["prefix"].
				'errors', $query))) or die('error fatal: ');
				
			echo "Se produjo un error, Contacte a un admin sobre el error #numero '".$q['rows']."'";
		//}
		
		die();
	}
	
	
}

// Created by Perberos. All rights reversed (C) 2006
?>
