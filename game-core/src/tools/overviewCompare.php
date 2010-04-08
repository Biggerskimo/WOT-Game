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
$ugamela_root_path = '../';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
if(!check_user()){ header("Location: login.php"); die();}

require_once(LW_DIR.'lib/util/SerializeUtil.class.php');

$alt = $neu = 0;

$sql = "SELECT data
		FROM ugml_request
		WHERE `time` > ".(TIME_NOW - 86400);
$result = WCF::getDB()->sendUnbufferedQuery($sql);
echo "sent query ...";

while($row = WCF::getDB()->fetchArray($result)) {
	$data = SerializeUtil::unserialize($row['data']);
	
	if($data['page'] == 'overview') {
		$alt++;
	}
	else if($data['page'] == 'index' && $data['args']['page'] == 'Overview') {
		$neu++;
	}
}
echo "Gesamt: ".($alt + $neu)."<br />";
echo "Alt: ".$alt."<br />";
echo "Neu: ".$neu."<br />";
echo "Alt / Neu: ".($alt / ($alt + $neu))."<br />";
?>