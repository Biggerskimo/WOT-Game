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

require_once(LW_DIR.'lib/system/io/socket/Socket.class.php');
require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');

/**
 * This exception is thrown when a error in the socket daemon occurs.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.core
 */
class SocketException extends SystemException {
	protected $errorNumber = 0;
	protected $errorDesc = '';
	protected $socket = null;
	protected $socketAddress = '';
	protected $socketPort = 0;
	
	/**
	 * Creates a new SocketException.
	 * 
	 * @param	string		message
	 * @param	Socket		socket class
	 */
	public function __construct($message, Socket $socket) {
		$this->errorNumber 	= $socket->last_error();
		$this->errorDesc = $socket->get_error();
		$this->socketAddress = $socket->bind_address;
		$this->socketPort = $socket->bind_port;
		$this->socket = $socket;
		
		parent::__construct($message, $this->errorNumber);
	}
	
	/**
	 * Returns the error number of this exception.
	 * 
	 * @return	int
	 */
	public function getErrorNumber() {
		return $this->errorNumber;
	}
	
	/**
	 * Returns the error description of this exception.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		return $this->errorDesc;
	}
	
	/**
	 * Returns the socket address of this socket.
	 * 
	 * @return	string
	 */
	public function getSocketAddress() {
		return $this->socketAddress;
	}
	
	/**
	 * Returns the socket port of this socket.
	 * 
	 * @return	int
	 */
	public function getSocketPort() {
		return $this->socketPort;
	}
	
	/**
	 * Prints the error page.
	 */
	public function show() {
		$this->information .= '<b>socket error:</b> '.StringUtil::encodeHTML($this->getErrorDesc()).'<br />';
		$this->information .= '<b>socket error number:</b> '.StringUtil::encodeHTML($this->getErrorNumber()).'<br />';
		$this->information .= '<b>socket address:</b> '.StringUtil::encodeHTML($this->getSocketAddress()).'<br />';
		$this->information .= '<b>socket port:</b> '.StringUtil::encodeHTML($this->getSocketPort()).'<br />';
		
		parent::show();
	}
}
?>