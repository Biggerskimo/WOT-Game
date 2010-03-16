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

require_once(LW_DIR.'lib/data/protection/BotDetectorClass.class.php');

/**
 * Detects logins with bad data.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class BadLoginDetector extends BotDetectorClass {

	/**
	 * @see	BotDetectorInterface::checkBot()
	 */
	public function checkBot() {
		if(isset($_POST['username'])) {
			if(!isset($_POST['world'])) $this->information .= 'world not set;';
		
			if(!isset($_POST['button_x'])) $this->information .= 'button_x not set;';
			//else if($_POST['button_x'] <= 0) $this->information .= 'button_x <= 0;';
			
			if(!isset($_POST['button_y'])) $this->information .= 'button_y not set;';
			//else if($_POST['button_y'] <= 0) $this->information .= 'button_y <= 0;';
		}
		
		if(!empty($this->information)) return true;
		return false;
	}
}
?>