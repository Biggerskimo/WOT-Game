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
 // logout.php :: Establece el tiempo de expiración de las cookies.

define('INSIDE', true);

$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

includeLang('logout');

LWCore::logout();

header('Location: index.htm');

exit;

//setcookie($game_config['COOKIE_NAME'], "", time()-100000, "/", "", 0);//le da el expire

//message($lang['see_you'],$lang['session_closed'],"login.".$phpEx);

// Created by Perberos. All rights reversed (C) 2006
?>