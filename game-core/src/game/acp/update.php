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

/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
$packageID = $this->installation->getPackageID();

// delete old style files
@unlink(WCF_DIR.'style/burningBoard.css');
@unlink(WCF_DIR.'style/ie6-fix.css');
@unlink(WCF_DIR.'style/ie7-fix.css');
@unlink(WCF_DIR.'style/help.css');
@unlink(WCF_DIR.'style/print.css');
@unlink(WCF_DIR.'style/privateMessages.css');

// refresh style files
require_once(WCF_DIR.'lib/system/style/StyleEditor.class.php');
$sql = "SELECT * FROM wcf".WCF_N."_style";
$result = WCF::getDB()->sendQuery($sql);
while ($row = WCF::getDB()->fetchArray($result)) {
	$style = new StyleEditor(null, $row);
	$style->writeStyleFile();
}
?>