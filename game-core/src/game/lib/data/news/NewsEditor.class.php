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

require_once(LW_DIR.'lib/data/news/News.class.php');
require_once(LW_DIR.'lib/data/AbstractDecorator.class.php');

/**
 * Creates and deletes news.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class NewsEditor extends AbstractDecorator {
	protected $newsObj = null;
	
	/**
	 * Creates a new NewsEditor object.
	 *
	 * @param	int		news id
	 */
	public function __construct(News $newsObj) {
		$this->newsObj = $newsObj;
	}
	
	/**
	 * Returns the associated news object.
	 * 
	 * @return News
	 */
	protected function getObject() {
		return $this->newsObj;
	}
	
	/**
	 * Creates a new news.
	 *
	 * @param	string	title
	 * @param	string	text
	 * @param	string	url
	 * @param	int		time
	 */
	public static function create($title, $text, $url, $time = TIME_NOW) {
		// check duplicate
		WCF::getCache()->addResource('news-'.PACKAGE_ID, WCF_DIR.'cache/cache.news-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderNews.class.php');
		$hash = sha1($title.$text.$url);
		$byHash = WCF::getCache()->get('news-'.PACKAGE_ID, 'hash');
		if(isset($byHash[$hash])) {
			return WCF::getCache()->get('news-'.PACKAGE_ID, $byHash[$hash]);
		}
		
		// insert
		$sql = "INSERT INTO ugml_news
				(title, text, link, `time`)
				VALUES
				('".escapeString($title)."', '".escapeString($text)."', '".escapeString($url)."', ".$time.")";
		WCF::getDB()->sendQuery($sql);
		
		$newsID = WCF::getDB()->getInsertID();
		
		WCF::getCache()->clearResource('news-'.PACKAGE_ID);
		
		return new News($newsID);
	}
	
	/**
	 * Deletes this news.
	 */
	public function delete() {
		$identifier = $this->getIdentifier();
		$hash = sha1($identifier);
		
		WCF::getDB()->sendQuery("START TRANSACTION");
		
		// user settings
		// TODO: create a class, that handles this
		$sql = "SELECT GROUP_CONCAT(userID)
				FROM ugml_user_setting
				WHERE hash = '".$hash."'
				GROUP BY hash";
		$row = WCF::getDB()->getFirstRow($sql);
		
		$userIDs = $row['userIDs'];
		
		Session::resetSessions($userIDs, true, false);
		
		$sql = "DELETE FROM ugml_user_setting
				WHERE hash = '".$hash."'";
		WCF::getDB()->sendQuery($sql);
		
		// news itself
		$sql = "DELETE FROM ugml_news
				WHERE newsID = ".$this->newsID;
		WCF::getDB()->sendQuery($sql);
		
		WCF::getCache()->addResource('news-'.PACKAGE_ID, WCF_DIR.'cache/cache.news-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderNews.class.php');
		WCF::getCache()->clearResource('news-'.PACKAGE_ID);
		
		WCF::getDB()->sendQuery("COMMIT");
	}
}
?>