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

// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/page/location/Location.class.php');

/**
 * ThreadLocation is an implementation of Location for the thread page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb.data.page.location  
 */
class ThreadLocation implements Location {
	public $cachedThreadIDs = array();
	public $threads = null;
	
	/**
	 * @see Location::cache()
	 */
	public function cache($location, $requestURI, $requestMethod, $match) {
		$this->cachedThreadIDs[] = $match[1];
	}
	
	/**
	 * @see Location::get()
	 */
	public function get($location, $requestURI, $requestMethod, $match) {
		if ($this->threads == null) {
			$this->readThreads();
		}
		
		$threadID = $match[1];
		if (!isset($this->threads[$threadID])) {
			return '';
		}
		
		return WCF::getLanguage()->get($location['locationName'], array('$thread' => '<a href="index.php?page=Thread&amp;threadID='.$threadID.SID_ARG_2ND.'">'.StringUtil::encodeHTML($this->threads[$threadID]).'</a>'));
	}
	
	/**
	 * Gets threads.
	 */
	protected function readThreads() {
		$this->threads = array();
		
		if (!count($this->cachedThreadIDs)) {
			return;
		}
		
		// get accessible boards
		$boardIDs = Board::getAccessibleBoards();
		if (empty($boardIDs)) return;
		
		$sql = "SELECT	threadID, topic
			FROM	wbb".WBB_N."_thread
			WHERE	threadID IN (".implode(',', $this->cachedThreadIDs).")
				AND boardID IN (".$boardIDs.")
				AND isDeleted = 0
				AND isDisabled = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->threads[$row['threadID']] = $row['topic'];
		}
	}
}
?>