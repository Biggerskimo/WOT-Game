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


function doquery($query, $table, $fetch = false){
  global $link,$debug,$ugamela_root_path;

	@include($ugamela_root_path.'config.php');

	if(!$link)
	{
		$link = odbc_connect($dbsettings["server"], $dbsettings["user"], 
				$dbsettings["pass"]) or
				$debug->error(odbc_error()."<br />$query","SQL Error");
				//message(mysql_error()."<br />$query","SQL Error");
		
		odbc_select_db($dbsettings["name"]) or $debug->error(odbc_error()."<br />$query","SQL Error");
	}
	// por el momento $query se mostrara
	// pero luego solo se vera en modo debug
	$sqlquery = odbc_exec($query, str_replace("{{table}}", $dbsettings["prefix"].
				$table)) or 
				$debug->error(odbc_error()."<br />$query","SQL Error");
				//message(mysql_error()."<br />$query","SQL Error");

	unset($dbsettings);//se borra la array para liberar algo de memoria

	global $numqueries,$debug;//,$depurerwrote003;
	$numqueries++;
	//$depurerwrote003 .= ;
	$debug->add("<tr><th>Query $numqueries: </th><th>$query</th><th>$table</th><th>$fetch</th></tr>");

	if($fetch)
	{ //hace el fetch y regresa $sqlrow
		$sqlrow = odbc_fetch_array($sqlquery);
		return $sqlrow;
	}else{ //devuelve el $sqlquery ("sin fetch")
		return $sqlquery;
	}
	
}



// Created by Perberos. All rights reversed (C) 2006

?>
