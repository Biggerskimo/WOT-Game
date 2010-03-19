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

require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Contains a news.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class News extends DatabaseObject {
	/**
	 * Creates a new News object.
	 *
	 * @param	int		news id
	 * @param	array	row data
	 */
	public function __construct($newsID, $row = null) {
		if($row === null) {
			$sql = "SELECT *
					FROM ugml_news
					WHERE newsID = ".$newsID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Sets the viewed-status.
	 *
	 * @param	int		time when viewed (0 for not viewed)
	 */
	public function setViewed($time = TIME_NOW) {
		WCF::getUser()->setSetting($this->getIdentifier(), $time);
	}
	
	/**
	 * Checks whether this news has been viewed.
	 *
	 * @return	bool
	 */
	public function isViewed() {
		$value = WCF::getUser()->getSetting($this->getIdentifier());
		
		if($value === null || !$value) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns the identifier of this news.
	 *
	 * @return	string
	 */
	public function getIdentifier() {
		return "news_".$this->newsID;
	}
}
?>