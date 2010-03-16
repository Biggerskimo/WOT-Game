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
 // ----> by Justus

define('INSIDE', true);
$ugamela_root_path = './../';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
//checkeamos que el usuario este logueado y que tenga los permisos de admin
if(!check_user()){ header("Location: ./../login.php"); }
if($user['authlevel']!="3"){message("Puto de mierda","pero vo so loko?");}


$r = doquery("SELECT * FROM {{table}}","users");

$page .= "<center><br><br><br><br><br><table> \n";

$page .= "<tr> \n";
$page .=  "<td><th><b><font color=\"orange\">Nr</b></th></td> \n";
$page .=  "<td><th><b><font color=\"orange\">Gracz</b></th></td> \n";
$page .=  "<td><th><b><font color=\"orange\">E-Mail</b></th></td> \n";
$page .=  "<td><th><b><font color=\"orange\">IP</b></th></td> \n";
$page .=  "<td><th><b><font color=\"orange\">Register Time</b></th></td> \n";
$page .=  "<td><th><b><font color=\"orange\">Online Time</b></th></td> \n";

$page .=  "</tr> \n";
while ($row = mysql_fetch_row($r)){
$page .=  "<tr> \n";
$page .=  "<td><th><font color=\"lime\">$row[0]</th></td> \n";
$page .=  "<td><th><font color=\"lime\">$row[1]</th></td> \n";
$page .=  "<td><th><font color=\"lime\">$row[3]</th></td> \n";
$page .=  "<td><th><font color=\"lime\">$row[15]</th></td> \n";
$page .=  "<td><th><font color=\"lime\">$row[16]</th></td> \n";
$page .=  "<td><th><font color=\"lime\">$row[17]</th></td> \n";

$page .=  "</tr> \n";
}
$page .=  "</table> \n";

display($page,'Userlist');



?>


<link rel="stylesheet" type="text/css" media="screen" href="http://80.237.203.201/download/use/epicblue/formate.css" />

</style> 

