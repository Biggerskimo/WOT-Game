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
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * This class provides default implementations for the WOT API.
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 */
abstract class WOTAPIAction extends AbstractAction {
	protected $key = '';
	protected $salt = '';
	
	protected $value1 = null;
	protected $value2 = null;
	protected $value3 = null;
	protected $value4 = null;
	protected $value5 = null;
	
	/**
	 * Creates a new WOTAPIAction object.
	 */
	public function __construct() {
		$this->readParameters();
		$this->validate($this->key, $this->salt, $this->value1, $this->value2, $this->value3, $this->value4, $this->value5);
		$this->execute();
	}
	
	/**
	 * Sends the output.
	 * 
	 * @param	string	output
	 * @param	int		state
	 */
	protected function send($output, $state) {
		$this->sendHeaders($state);
		
		echo $output;
		
		exit;
	}
	
	/**
	 * Sends the standard lines of the wot api protocol.
	 */
	protected function sendHeaders($state) {
		echo "WOTAPIP/2.0 ".$state."\n";
		echo "SERVER: WOT".VERSION."\n";
		echo "\n";
	}
	
	/**
	 * @see Action::readParameters()
	 */
	abstract public function readParameters() {
		parent::readParameters();
		
		$this->key = StringUtil::trim($_REQUEST['key']);
		$this->salt = StringUtil::trim($_REQUEST['salt']);
		
		if(empty($this->key)) {
			$this->send('no key found', 102);
		}
		
		if(empty($this->salt)) {
			$this->send('no salt found', 103);
		}
	}
	
	/**
	 * Validates the key.
	 * 
	 * @param	string	key
	 * @param	string	salt
	 * @param	mixed	value
	 * @param	mixed	value2
	 * ...
	 */
	protected function validate($key, $salt, $value1, $value2 = null, $value3 = null, $value4 = null, $value5 = null) {
		$values = array();
		
		if($value1 === null) {
			$this->send('no values given to validate', 104);
		}
		
		$values[] = $value1;
		if($value2 !== null) $values[] = $value2;
		if($value3 !== null) $values[] = $value3;
		if($value4 !== null) $values[] = $value4;
		if($value5 !== null) $values[] = $value5;
		
		$i = 0;
		do {
			$string .= $values[$i];
			$i++;
		} while($values[$i] !== null);
		
		if($key !== StringUtil::getDoubleSaltedHash($string, $salt)) {
			$this->send('key not correct', 101);
		}
	}
}
?>