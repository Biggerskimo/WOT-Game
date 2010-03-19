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

require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(LW_DIR.'lib/data/news/News.class.php');

/**
 * Closes a news.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class CloseNewsAction extends AbstractAction {
	public $newsID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['newsID'])) {
			$this->newsID = LWUtil::checkInt($_REQUEST['newsID']);
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		if (!WCF::getUser()->userID) {
			die('invalid userID');
		}
		
		WCF::getCache()->addResource('news-'.PACKAGE_ID, WCF_DIR.'cache/cache.news-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderNews.class.php');
		$news = WCF::getCache()->get('news-'.PACKAGE_ID);
		
		if(!isset($news[$this->newsID])) {
			die('invalid newsID');
		}
		$news[$this->newsID]->setViewed();
		
		$this->executed();
		
		die('done');
	}
}
?>