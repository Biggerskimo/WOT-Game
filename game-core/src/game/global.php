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

if(!isset($ugamela_root_path)) $ugamela_root_path = '';
if (!defined('RELATIVE_LW_DIR')) define('RELATIVE_LW_DIR', $ugamela_root_path.'');
if (!defined('RELATIVE_WCF_DIR')) define('RELATIVE_WCF_DIR', RELATIVE_LW_DIR.'wcf/');
if(!defined('TIMEZONE')) define('TIMEZONE', 2);

// include config
require_once(dirname(__FILE__).'/config.inc.php');

// include WCF
require_once(RELATIVE_WCF_DIR.'global.php');

// include ZF
set_include_path(get_include_path() . PATH_SEPARATOR . LW_DIR . '../zf/library');
require_once('Zend/Loader/Autoloader.php');
Zend_Loader_Autoloader::getInstance();
spl_autoload_register('__autoload');

// starting wot core
require_once(LW_DIR.'lib/system/LWCore.class.php');
new LWCore();

WOTUtil::initDone();
?>
