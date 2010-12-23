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

require_once(WCF_DIR.'lib/util/ArrayUtil.class.php');
require_once(LW_DIR.'lib/data/account/PBU.class.php');

// TODO: dirty :(
if(!defined('SERVER_ID')) define('SERVER_ID', 0);

if(isset($_REQUEST['userID']))
{
	$userID = intval($_REQUEST['userID']);
	pbuUser($userID);
}
else if(isset($_REQUEST['userIDs']))
{
	$userIDs = explode(',', $_REQUEST['userIDs']);
	$userIDs = ArrayUtil::toIntegerArray($userIDs);
	print_r($userIDs);
	foreach($userIDs as $userID)
		pbuUser($userID);
}

else if(isset($_REQUEST['all']))
{
	$sql = "SELECT *
			FROM ugml_users";
	$result = WCF::getDB()->sendQuery($sql);
	
	while($row = WCF::getDB()->fetchArray($result))
		pbuUser($row['id']);
}

function pbuUser($userID)
{
	$s = microtime(true);
	// TODO: transaction support?
	$pbu = PBU::create($userID, $coordChanges);
	$pbu->close();
	echo "<br>\nPBU for user ".$userID." completed in ".(microtime(true) - $s)."-<br>\n";
}
?>