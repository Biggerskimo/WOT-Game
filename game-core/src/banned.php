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
  //mboss

define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

if(!check_user()){ header("Location: login.php"); die();}


$dpath = (!$userrow["dpath"]) ? DEFAULT_SKINPATH : $userrow["dpath"];

$i = (is_numeric($from)&&isset($from)) ? $from : 0;

echo_head("Suspensiones en Ugamela");
if($userrow){echo_topnav();}
echo "<center>\n";

echo '<h2>Pranger</h2>

   <table border="0" cellpadding="2" cellspacing="1">
    <tr height="20">
     <td class="c">Gesperrter</td>
     <td class="c">Wann</td>
     <td class="c">Gesperrt bis:</td>
     <td class="c">Kontakt:</td>
     <td class="c">Grund</td>
    </tr>';

$count = 0;
$banned = doquery("SELECT * FROM {{table}} ORDER BY `time` DESC LIMIT $i,50","banned");
while($b = mysql_fetch_array($banned)){
	echo "<tr height=20>";
	echo "<th>".$b["who"]."</th>";
	echo "<th>".date("d.m.Y G:i:s", $b['time'])."</th>";
	echo "<th>".date("d.m.Y G:i:s",$b['longer'])."</th>";
	echo '<th><a href="mailto:'.$b["email"].'?subject=banned:'.$b["who"].'">'.$b["author"]."</a></th>";
	echo "<th>".$b["theme"]."</th>";
	echo "</tr>";
	$count++;
}

if($count == 0){echo "<tr height=20><th colspan=\"5\">Keine Sperren.</th></tr>";}
$ia=$i-50;
$i+=50;
echo "<tr>";
echo '<th colspan="5">';
if($i >50){echo "<a href=\"?from=$ia\">&lt;&lt; Vorherige 50</a>&nbsp;&nbsp;&nbsp;&nbsp;";}
echo "<a href=\"?from=$i\">N&auml;chste 50 &gt;&gt;</a>";
echo "</th>";
echo "</tr>";

echo "</table></center></body></html>";


if($userrow['authlevel'] == 3){
	$tiempo = microtime();
	$tiempo = explode(" ",$tiempo);
	$tiempo = $tiempo[1] + $tiempo[0];
	$tiempoFin = $tiempo;
	$tiempoReal = ($tiempoFin - $tiempoInicio);
	echo $depurerwrote001.$tiempoReal.$depurerwrote002.$numqueries.$depurerwrote003;
}


?>