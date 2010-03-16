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
 * Creates a private backup of a user.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.action.user
 */
class WOTAPIPbucreateAction extends AbstractWOTAPIAction {
	public $pbuObj = null;
	protected $pbuStr = '';
	
	public $userID = 0;
	public $coordChanges = array();
	public $send = false;
	
	/**
	 * @see WOTAPIAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->userID = intval($this->data['userid']);
		
		if(isset($this->data['coordchanges'])) {
			echo '#',StringUtil::trim(WOTAPIUtil::unescape($this->data['coordchanges'])),'#';
			$this->coordChanges = unserialize(StringUtil::trim(WOTAPIUtil::unescape($this->data['coordchanges'])));
		}
		
		if(isset($this->data['send'])) {
			$this->send = (bool)$this->data['send'];
		}
	}

	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		var_dump($this->coordChanges);
		$this->pbuObj = PBU::create($this->userID, $this->coordChanges);
		$this->pbuObj->close();
		
		$this->pbuStr = file_get_contents($this->pbuObj->getFileName());
		
		parent::execute();
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		parent::answer();
		
		$data = array();
		if($this->send) {
			$data['pbuid'] = $this->pbuObj->pbuID;
			$data['pbustr'] = $this->pbuStr;
			$data['time'] = $this->pbuObj->time;
		}
		
		$this->wotAPIServerClient->send('private backup successful created'.($this->data['send'] ? ', appending' : '').'.', 100, $data);		
	}
}
?>