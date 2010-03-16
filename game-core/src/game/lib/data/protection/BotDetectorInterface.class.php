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
 * All bot detectors must implement this interface.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
interface BotDetectorInterface {
	/**
	 * Creates a new object.
	 *
	 * @param	int		detector id
	 */
	public function __construct($detectorID);

	/**
	 * Checks if a bot is used.
	 * 
	 * @return	bool	state
	 */
	public function checkBot();
	
	/**
	 * Returns the information message.
	 * 
	 * @return	string	information
	 */
	public function getInformation();
	
	/**
	 * Saves the detection to the database.
	 */
	public function saveDetection();
}
?>