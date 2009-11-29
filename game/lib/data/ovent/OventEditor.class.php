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

require_once(LW_DIR.'lib/data/ovent/Ovent.class.php');
require_once(LW_DIR.'lib/util/SerializeUtil.class.php');

/**
 * This class is able to handle overview events.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds
 */
class OventEditor {
	/**
	 * Creates a new overview event.
	 * 
	 * @param	int		ovent type id
	 * @param	int		time
	 * @param	mixed	event id (may be null)
	 * @param	int		relational id
	 * @param	array	additional fields
	 * @param	bool	checked
	 * @param	mixed	data
	 * @return	Ovent
	 */
	public static function create($oventTypeID, $time, $eventID, $relationalID, $additionalFields = array(), $checked = 0, $data = array()) {
		if(!is_array($data)) $data = array($data);
		
		$oventID = self::insert($oventTypeID, $time, $eventID, $relationalID, $additionalFields, $checked, $data);
		
		return Ovent::getByOventID($oventID);
	}
	
	/**
	 * Inserts a new overview event row.
	 * 
	 * @param	int		ovent type id
	 * @param	int		time
	 * @param	mixed	event id (may be null)
	 * @param	int		relational id
	 * @param	array	additional fields
	 * @param	bool	checked
	 * @param	array	data
	 * @return	int		ovent id
	 */
	public static function insert($oventTypeID, $time, $eventID, $relationalID, $additionalFields, $checked, $data) {
		$sql = "INSERT INTO ugml_ovent
				(oventTypeID, `time`, eventID,
				 relationalID, checked, data,
				 ".implode(',', array_keys($additionalFields)).")
				VALUES
				(".$oventTypeID.", ".$time.", ".$eventID.",
				 ".$relationalID.", ".intval($checked).", '".SerializeUtil::serialize($data)."',
				 ".implode(',', $additionalFields).")";
		WCF::getDB()->sendQuery($sql);
		
		return WCF::getDB()->getInsertID();
	}
}
?>