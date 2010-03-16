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
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.wotapipserver.core
 */
class WOTAPIServerClient extends AbstractSocketServerClient {
	protected $impl = null;
	
	/**
	 * Searches for a correct implementation and let the implementation do its work.
	 *
	 * @param	string		text
	 */
	protected function handleRequest($lines) {
		if($this->impl === null) {
			$nativeVersion = substr($lines[0], 8); // "WOTAPIP/sth" => "sth"
			
			switch($nativeVersion) {
				case '2.0':
				case '2.0.1':
					require_once(LW_DIR.'lib/system/io/socket/WOTAPIP20ServerClient.class.php');
					$this->impl = new WOTAPIP20ServerClient();
					break;
				case '2.0.9':
				case '2.1':
				case '2.1.0':
					require_once(LW_DIR.'lib/system/io/socket/WOTAPIP21ServerClient.class.php');
					$this->impl = new WOTAPIP21ServerClient();
					break;
				default:
					require_once(LW_DIR.'lib/system/exception/SystemException.class.php');
					throw new SystemException();			
			}
			$this->impl->setSocket($this);
		}
		$this->impl->handleRequest($lines);
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
	 * Checks if this string is suitable to process.
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
}
?>