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

require_once(WCF_DIR.'lib/system/crypt/Mcrypt.class.php');
require_once(LW_DIR.'lib/system/io/socket/WOTAPIPServerClient.class.php');
require_once(LW_DIR.'lib/util/WOTAPIUtil.class.php');

/**
 * Extends the default socketServer class and cares for the timelimit.
 * 
 * @author		Biggerskimo
 * @copyright	2008 - 2009 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.core
 */
class WOTAPIP21ServerClient implements WOTAPIPServerClient {
	protected $socket = null;
	protected $started = false;
	private $crypter = null;
	
	protected $action = '';
	public $data = array();
	protected $connection = 'Close';
	
	/**
	 * @see WOTAPIPServerClient::setSocket()
	 */
	public function setSocket(Socket $socket) {
		$this->socket = $socket;
	}
	
	/**
	 * @see WOTAPIPServerClient::handleRequest()
	 */
	public function handleRequest($lines) {
		// get data
		$this->connection = 'Close';
		$this->data = array();
		$this->action = '';
		
		$dataComing = false;
		$resumption = $this->started;
		foreach($lines as $no => $line) {
			// not encrypted
			if(($no == 0 || $no == 1) && !$resumption) {
				// version
				if(substr($line, 0, 8) == 'WOTAPIP/') {
					$this->started = true;
					continue;
				}
				// initialization vector
				$iv = base64_decode($line);
				$this->crypter = new Mcrypt();
				$this->crypter->init(CRYPTER_KEY, $iv);				
				
				continue;
			}
			
			// encrypted
			$line = $this->crypter->decryptFromText($line);		
			
			$parts = ArrayUtil::trim(explode(':', $line, 2));

			if($dataComing) {
				$this->data[StringUtil::toLowerCase($parts[0])] = $parts[1];
				continue;
			}
			switch($parts[0]) {
				case 'ACTION':
					$this->action = $parts[1];
					break;
					
				case 'CONNECTION':
					if($parts[1] !== 'Close' && $parts[1] !== 'Keep-Alive') {
						$this->send('unknown connection value '.$parts[1], 206);
						return;
					}
					$this->connection = StringUtil::trim($parts[1]);
					break;
					
				case 'DATA':
					$dataComing = true;
					break;
					
				default:
					if(substr($parts[0], 0, 5) == 'DATA_') $parts[0] = substr($parts[0], 5);
					else {
						$this->send('unknown line '.$parts[0], 204);
						return;
					}
					
			}
		}
		
		// validate
		if(empty($this->action)) {
			$this->send('no action specified', 202);
			return;
		}
		
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
	 * @see SocketClient::write()
	 */
	public function write($buffer) {
		$this->socket->write($buffer);
		
		//var_dump($this->socket->writeBuffer, $this->connection);
		if(empty($this->socket->writeBuffer) && $this->connection != "Keep-Alive") {
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
		
		$output .= $this->crypter->encryptToText("STATE: ".WOTAPIUtil::escape($state))."\n";
		$output .= $this->crypter->encryptToText("MESSAGE: ".WOTAPIUtil::escape($message))."\n";
		
		foreach($data as $name => $date) {
			$output .= $this->crypter->encryptToText("DATA_".StringUtil::toUpperCase($name).":".WOTAPIUtil::escape($date))."\n";
			echo $this->crypter->encryptToText("DATA_".StringUtil::toUpperCase($name).":".WOTAPIUtil::escape($date))."\n";
		}
		
		$output .= "\n";
		
		$this->write($output);
		
		echo $output;
		//echo $this->read_buffer;
		flush();
		
		if($this->connection == 'Close') {
			// write() will close for us
			echo 'closed';
		}
	}
	
	public function __call($function, $arguments) {
		call_user_func_array(array($this->socket, $function), $arguments);
	}
}
?>