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

require_once(WCF_DIR.'lib/system/io/File.class.php');
require_once(LW_DIR.'lib/wotapi/AbstractWOTAPIAction.class.php');
require_once(LW_DIR.'lib/data/account/PBU.class.php');

/**
 * Receives a compressed private backup.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIPbuuploadAction extends AbstractWOTAPIAction {
	public $pbuObj = null;
	
	protected $pbuStr = '';	
	public $pbuID = 0;
	public $userID = 0;
	public $serverID = 0;
	public $time = 0;
	public $recover = false;
	
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		//$this->pbuID = intval($this->data['pbuid']);
		$this->userID = intval($this->data['userid']);
		$this->serverID = intval($this->data['serverid']);
		$this->time = intval($this->data['time']);
		$this->pbuStr = WOTAPIUtil::unescape($this->data['pbustr']);
		
		if(isset($this->data['recover'])) {
			$this->recover = (bool)$this->data['recover'];
		}
	}

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		$this->pbuID = PBU::insert($this->userID, $this->serverID, $this->time);
		$this->pbuObj = new PBU($this->pbuID);
		$this->pbuObj->getFile()->write($this->pbuStr);
		$this->pbuObj->close(PBU::FILE_HANDLE);
		chmod($this->pbuObj->getFileName(), 0777);
		
		if($this->recover) {
			$this->pbuObj->recover();
		}
		
		parent::execute();
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();
		
		$this->wotAPIServerClient->send('private backup successful saved'.($this->recover ? ' and recovered' : '').'.', 100);		
	}
}
?>