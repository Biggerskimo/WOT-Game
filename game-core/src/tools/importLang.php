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

require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
require_once(WCF_DIR.'lib/util/XML.class.php');

$std = 'de-informal';

$languageCache = WCF::getCache()->get('languages');
$languages = $languageCache['languages'];

foreach($languages as $languageID => $languageArray) {
	$editor = new LanguageEditor($languageID);
	
	if(file_exists('lang/'.$languageArray['languageCode'].'.xml')) {
		$xml = new XML('lang/'.$languageArray['languageCode'].'.xml');
	}
	else {
		$string = file_get_contents('lang/'.$std.'.xml');
		
		$string = preg_replace('/languagecode="'.$std.'"/i', 'languagecode="'.$std.'"', $string, 1);
		
		$xml = new XML();
		$xml->loadString($string);
	}
	$editor->updateFromXML($xml, PACKAGE_ID);
}
?>