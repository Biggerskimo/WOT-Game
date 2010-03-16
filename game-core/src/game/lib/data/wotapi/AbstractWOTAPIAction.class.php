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

require_once(LW_DIR.'lib/data/wotapi/WOTAPIAction.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Provides default implementations for wotapi actions.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class AbstractWOTAPIAction implements WOTAPIAction {
	protected $wotAPIServerClient = null;
	public $data = null;
	
	/**
	 * Creates a new AbstractWOTAPIAction object.
	 * 
	 * @param	WOTAPIServerClient
	 */
	public function __construct($wotAPIServerClient) {
		$this->wotAPIServerClient = $wotAPIServerClient;
		$this->data = $this->wotAPIServerClient->data;
		
		$this->readParameters();
		$this->execute();
		$this->answer();
	}
	
	/**
	 * @see WOTAPIAction::execute()
	 */
	public function readParameters() {
		EventHandler::fireAction($this, 'readParameters');
	}
	
	/**
	 * @see WOTAPIAction::execute()
	 */
	public function execute() {
		EventHandler::fireAction($this, 'execute');
	}
	
	/**
	 * @see WOTAPIAction::answer()
	 */
	public function answer() {
		EventHandler::fireAction($this, 'answer');
	}
}
?>