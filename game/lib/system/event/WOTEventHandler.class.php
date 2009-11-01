<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * All classes that handle events of the wot event system should implement this interface.
 * 
 * @author		David W�gner
 * @copyright	2007 - 2008 Lost Worlds
 */
interface WOTEventHandler {
	/**
	 * Executes a event
	 * 
	 * @param	array	data
	 */
    public function execute($data);
}
?>