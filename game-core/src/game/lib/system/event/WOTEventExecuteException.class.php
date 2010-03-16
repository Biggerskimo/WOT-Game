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

require_once(LW_DIR.'lib/system/event/WOTEventHandler.class.php');
require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');

/**
 * This exception is thrown when a error in the socket daemon occurs.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class WOTEventExecuteException extends SystemException {
	protected $e = null;
	protected $eventID = 0;
	protected $eventTypeID = 0;
	protected $h = null;
	
	/**
	 * Creates a new WOTEventExecuteException.
	 * 
	 * @param	Exception		caught exception
	 * @param	WOTEventHandler	object where the exception occurred
	 * @param	int				eventID
	 * @param	int				eventTypeID
	 */
	public function __construct(Exception $e, WOTEventHandler $h, $eventID, $eventTypeID = 0) {
		// import exception variables
		$this->message = $e->getMessage();
		$this->code = $e->getCode();
		$this->file = $e->getFile();
		$this->line = $e->getLine();
		$this->e = $e;
		
		// import event handler variables
		$this->eventID = $eventID;
		$this->eventTypeID = $eventTypeID;
		$this->h = $h;
	}

	/**
	 * Removes database password from stack trace.
	 * @see SystemException::getTraceAsString()
	 */
	public function __getTraceAsString() {
		// $this->getTraceAsString() is declared as final, so we can not use it properly
		return preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $this->e->getTraceAsString());
	}
	
	/**
	 * Returns the eventID.
	 * 
	 * @return	int		eventID
	 */
	public function getEventID() {
		return $this->eventID;
	}
	
	/**
	 * Returns the eventTypeID.
	 * 
	 * @return	int		eventTypeID
	 */
	public function getEventTypeID() {
		return $this->eventTypeID;
	}
	
	/**
	 * Prints the error page.
	 */
	public function show() {
		$this->information .= '<b>event id:</b> '.intval($this->getEventID()).'<br />';		
		$this->information .= '<b>event handler no:</b> '.intval($this->getEventTypeID()).'<br />';
		$this->information .= '<b>event handler objekt:</b> '.StringUtil::encodeHTML(print_r($this->h, true)).'<br />';
		
		parent::show();
	}
}
?>