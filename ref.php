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

$userID = intval($_GET['u']);

if($userID != 0 && !isset($_COOKIE['dili_link_used'.$userID])) {	
	// log
	$sql = "INSERT INTO ugml_dilizium_link
			(userID, registered, `time`,
			 ipAddress, userAgent, cookieData)
			VALUES
			(".$userID.", 0, ".time().",
			 INET_ATON('".escapeString($_SERVER['REMOTE_ADDR'])."'), '".escapeString($_SERVER['HTTP_USER_AGENT'])."', '".escapeString(LWUtil::serialize($_COOKIE))."')";
	WCF::getDB()->sendQuery($sql);
	$diliLinkID = WCF::getDB()->getInsertID();
		
	setcookie('dili_link_used'.$userID, time(), time() + 60 * 60 * 24 * 365);
	setcookie('dili_link_clicked', $diliLinkID, time() + 60 * 60 * 24 * 365);
}

header("Location: index.htm");
?>