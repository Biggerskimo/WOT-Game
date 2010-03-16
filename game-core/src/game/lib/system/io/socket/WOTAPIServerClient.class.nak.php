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

require_once(WCF_DIR.'lib/system/io/socket/AbstractSocketServerClient.class.php');
require_once(LW_DIR.'lib/util/WOTAPIUtil.class.php');

/**
 * Extends the default socketServer class and cares for the timelimit.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.core
 */
class WOTAPIServerClient extends AbstractSocketServerClient {
	protected $protocolVersion = '2.0.1';
	
	protected $action = '';
	public $data = array();
	protected $sendTime = 0;
	protected $connection = 'Close';
	
	private $key = '';
	private $salt = '';
	
	/**
	 * Reads the data, validates it and executes the action.
	 *
	 * @param	string		text
	 */
	protected function handleRequest($lines) {
		// get data
		$this->connection = 'Close';
		$this->data = array();
		$this->key = '';
		$this->salt = '';
		$this->sendTime = 0;
		$this->action = '';
		
		foreach($lines as $no => $line) {
			if($no == 0 && substr($line, 0, 8) == 'WOTAPIP/') {
				$this->protocolVersion = substr($line, 8, 3);
				
				continue;
			}
			
			$parts = ArrayUtil::trim(explode(':', $line, 2));
			
			switch($parts[0]) {
				case 'KEY':
					$this->key = StringUtil::trim($parts[1]);
					break;
				case 'SALT':
					$this->salt = StringUtil::trim($parts[1]);
					break;
				case 'ACTION':
					$this->action = StringUtil::trim($parts[1]);
					break;
				case 'SENDTIME':
					$this->sendTime = intval(StringUtil::trim($parts[1]));
					break;
				case 'CONNECTION':
					if($parts[1] !== 'Close' && $parts[1] !== 'Keep-Alive') {
						$this->send('unkwown connection value '.$parts[1], 206);
						return;
					}
					$this->connection = StringUtil::trim($parts[1]);
					break;
				default:
					if(substr($parts[0], 0, 5) == 'DATA_') $parts[0] = substr($parts[0], 5);
					else {
						$this->send('unkwown line '.$parts[0], 204);
						return;
					}
					
					$this->data[StringUtil::toLowerCase($parts[0])] = $parts[1];
			}
		}
		
		// validate
		if(empty($this->key)) {
			$this->send('no key found', 200);
			return;
		}
		if(empty($this->salt)) {
			$this->send('no salt found', 201);
			return;
		}
		if(empty($this->action)) {
			$this->send('no key found', 202);
			return;
		}
		if(empty($this->sendTime)) {
			$this->send('sendTime not given', 203);
			return;
		}
		if($this->sendTime < time() - 60 * 60 || $this->sendTime > time()) {
			$this->send('invalid sendTime given', 207);
			return;
		}
		/*if(count($this->data) > 5) {
			$this->send('too many data given', 205);
			return;
		}*/
		
		if(!$this->validateKey($this->data)) return;
		
		// execute
		$className = 'WOTAPI'.StringUtil::firstCharToUpperCase(StringUtil::toLowerCase($this->action)).'Action';
		
		if(!file_exists(LW_DIR.'lib/wotapi/'.$className.'.class.php')) {
			$this->send('classfile not found', 210);
			return;
		}
		
		require_once(LW_DIR.'lib/wotapi/'.$className.'.class.php');
				
		if(!class_exists($className)) {
			$this->send('class \''.$className.'\' not found', 211);
			return;
		}
		
		try {
			$action = new $className($this);
		} catch(Exception $e) {
			ob_start();
			$e->show();
			$output = ob_get_contents();
			ob_end_clean();
			$this->send('exception thrown while execution: '.addcslashes($output, ":\n\r\\"), 220);
		}
	}
	
	/**
	 * @see	SocketServerClient::onRead()
	 */
	public function onRead() {		
		$string = StringUtil::unifyNewlines($this->readBuffer);
		
		echo '#~->',(strlen($string) > 500 ? substr($string, 0, 500) : $string);
		
		if(!$this->isValidString($string)) return;
						
		$lines = ArrayUtil::trim(explode("\n", $string));
		
		$this->handleRequest($lines);
		
		$this->read_buffer = '';
	}
	
	/**
	 * Checks if can be worked with the given string.
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isValidString($string) {
		if (strpos($string, "\n\n") !== false) {
			return true;
		}
		return false;
	}
	
	/**
	 * Validates the key.
	 * 
	 * @param	array	data
	 */
	private function validateKey($data) {
		$string = $this->sendTime.str_rot13($this->action);
		
		if(StringUtil::$this->key !== StringUtil::getDoubleSaltedHash($string, $this->salt)) {
			$this->send('key validation failed with string: "'.$string.'"', 220);
			return false;
		}
		
		return true;
	}

	/**
	 * @see SocketClient::write()
	 */
	public function write($buffer) {
		parent::write($buffer);
		
		if(empty($this->writeBuffer) && $this->connection != "Keep-Alive") {
			$this->disconnect();
		}
	}
	
	/**
	 * Sends the output and closes the connection.
	 * 
	 * @param	string	message
	 * @param	int		state
	 * @param	array	data
	 */
	public function send($message = 'OK', $state = 100, $data = array()) {
		$output = "";
		
		foreach($data as $name => $date) {
			$output .= "DATA_".StringUtil::toUpperCase($name).":".WOTAPIUtil::escape($date)."\n";
		}
		
		$output .= "STATE: ".WOTAPIUtil::escape($state)."\n";
		$output .= "MESSAGE: ".WOTAPIUtil::escape($message)."\n";
		$output .= "\n";
		
		$this->write($output);
		
		echo $output;
		//echo $this->read_buffer;
		flush();
		
		if($this->connection == 'Close') {
			// close connection
			/*$this->close();
			$this->disconnected = true;
			$this->socket = intval($this->socket);
			
			// execute event handler
			$this->onDisconnect();*/
			echo 'closed';
		}
	}
	
	public function onTimer() {
		echo 'a';
	}
}
?>