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
 //index.php :: Pagina inicial

if(isset($_GET['page']) || isset($_GET['form']) || isset($_GET['action'])) {
	define('SITE_PREFIX', 'game/');
	include('game/redirector.inc.php');
}

define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
//echo ".";

if(!check_user()){ header("Location: login.php"); die();}
//Index con dos frames
echo parsetemplate(gettemplate('index_frames'), $lang);




// Created by Perberos. All rights reserved (C) 2006
?>
