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

require_once(LW_DIR.'lib/data/news/NewsEditor.class.php');

/**
 * Searches for news in a feed.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class NewsFeed {
	protected $uris = array();

	/**
	 * Searches for news in feeds.
	 */
	public function __construct() {
		$this->getURIs();
		$this->check();
	}
	
	/**
	 * Checks the feeds for new news.
	 */
	protected function check() {
		foreach($this->uris as $uri) {
			$feed = Zend_Feed_Reader::import($uri);
			
			foreach($feed as $entry) {
				$content = $this->getFixedContent($entry);
				NewsEditor::create($entry->getTitle(), $content, $entry->getLink());
			}
		}
	}
	
	/**
	 * Zend_Feed_Reader seems to produce utf8 outputs when there should be a iso-8859-x output.
	 * This method returns the corrent encoded output.
	 *
	 * @param	Zend_Feed_Reader_Entry
	 * @return	string	content
	 */
	protected function getFixedContent($entry) {
		$saidEncoding = $entry->getEncoding();
		$saidContent = $entry->getContent();
		
		if(strpos($saidEncoding, 'ISO-8859-') === false) {
			// nothing to fix
			return $saidContent;
		}
		
		if(StringUtil::isUtf8($saidContent)) {
			// use mb_convert_encoding here?
			return utf8_decode($saidContent);
		}
		// correct iso-8859 encoding
		return $saidContent;
	}
	
	/**
	 * Fetches the list of feed-uris.
	 */
	protected function getURIs() {
		$sql = "SELECT *
				FROM ugml_news_feed";
		$result = WCF::getDB()->sendQuery($sql);
		
		while($row = WCF::getDB()->fetchArray($result)) {
			$this->uris[] = $row['uri'];
		}
		
		$this->uris[] = 'http://lost-worlds.net/gfeed.php';
	}
}
?>