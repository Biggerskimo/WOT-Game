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

require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(LW_DIR.'lib/data/news/News.class.php');
require_once(LW_DIR.'lib/data/ovent/Ovent.class.php');

/**
 * This page is able to show the different statistic types.
 * 
 * @author		Biggerskimo
 * @copyright	2009 - 2010 Lost Worlds <http://lost-worlds.net>
 */
class OverviewPage extends AbstractPage {
	public $templateName = 'overview';
	
	public $ovents = array();
	public $hovents = array();
	
	public $news = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->ovents = Ovent::getByConditions(array('userID' => WCF::getUser()->userID, 'checked' => 0));
		$this->hovents = Ovent::getByConditions(array('userID' => WCF::getUser()->userID, 'checked' => 1));
		
		// news
		WCF::getCache()->addResource('news-'.PACKAGE_ID, WCF_DIR.'cache/cache.news-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderNews.class.php');
		
		$this->news = WCF::getCache()->get('news-'.PACKAGE_ID);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array('ovents' => $this->ovents, 'hovents' => $this->hovents, 'news' => $this->news));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
?>