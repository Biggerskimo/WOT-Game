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
 //fleetback.php
/*define('INSIDE', true);
$ugamela_root_path = '../';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
if(!check_user()){ header("Location: login.php"); die();}

$start = intval($_GET['start']);
$inserts = array();
*/
for($i = 1; $i <= 250; ++$i) {
	//$w = array();
	for($j = 1; $j <= 100; ++$j) {
		$x = array();
		for($k = 1; $k <= $i; ++$k) {
			$x[] = mt_rand(1, 6);
		}
		$v = array_sum($x) / count($x);
	
		$inserts[] = "(".$i.", ".$v.")";
	}
	//$avg = array_sum($w) / count($w);
}
$sql = "INSERT INTO	w
		VALUES
		".implode(',', $inserts);
echo $sql;