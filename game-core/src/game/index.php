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

if (!defined('RELATIVE_LW_DIR')) define('RELATIVE_LW_DIR', '');
if (!defined('RELATIVE_WCF_DIR')) define('RELATIVE_WCF_DIR', RELATIVE_LW_DIR.'../wcf/');

//require_once('./global.php');
if (!defined('INSIDE')) define('INSIDE', true);
$ugamela_root_path = dirname(__FILE__).'/../';
require_once('../extension.inc');
require_once('../common.php');

if(!isset($_GET['page']) && !isset($_GET['form']) && !isset($_GET['action'])) header('Location: http://xen251.linea7.net/game');
RequestHandler::handle(array(LW_DIR . 'lib/', WCF_DIR . 'lib/'));

?>