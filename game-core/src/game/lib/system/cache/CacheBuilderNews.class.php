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

require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Caches the news.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class CacheBuilderNews implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$sql = "SELECT *
				FROM ugml_news
				WHERE disabled = 0
					AND `time` > ".(TIME_NOW - 60 * 60 * 24 * 30)."
				ORDER BY `time` DESC
				LIMIT 3";
		$result = WCF::getDB()->sendQuery($sql);
		
		$data = array('hash' => array());
		while($row = WCF::getDB()->fetchArray($result)) {
			$data[$row['newsID']] = new News(null, $row);
			$data['hash'][sha1($row['title'].$row['text'].$row['link'])] = $row['newsID'];
		}
		
		return $data;
	}
}
?>