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

require_once(LW_DIR.'lib/system/event/WOTEventExecute.class.php');
require_once(LW_DIR.'lib/system/event/WOTEventExecuteException.class.php');
require_once(LW_DIR.'lib/util/LockUtil.class.php');

/**
 * This class executes the wot event executer.
 * The lifetime _must_ be greater than 20, should be greater than 30;
 *  if lesser/equal to 20, the lifetime will be set to 21
 * 
 * @author		Biggerskimo
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 */
class WOTEventExecuteDaemon {
	protected $removedLock = false;
	
	protected $lifeTime = 0;
	protected $interval = 1.0;
	
	protected $startTime = 0;
	protected $executeCount = 0;
	protected $maxExecutions = 0;
	protected $lastExecution = 0;
	
	const LOCK_NAME = 'woteventexecutedaemon';
	
	/**
	 * Starts the daemon.
	 * 
	 * @param	float	lifetime
	 * @param	float	interval
	 */
	public function __construct($lifeTime, $interval = 1.0) {
		if($lifeTime <= 20) {
			$lifeTime = 21;
		}
		
		set_time_limit($lifeTime * 2 - 10);
		
		$this->startTime = $this->lastExecution = microtime(true);
		$this->lifeTime = $lifeTime;
		
		$this->maxExecutions = $lifeTime / $interval;
		
		$this->checkLock();
		$this->setLock();
		
		$this->mainLoop();
		
		$this->removeLock();
	}
	
	/**
	 * Destroys this object.
	 */
	public function __destruct() {
		$this->removeLock();
	}
	
	/**
	 * Waits for the expiration of the lock.
	 */
	protected function checkLock() {
		do {
			try {
				echo 'l';
				LockUtil::checkLock(self::LOCK_NAME);
				return;
			} catch(SystemException $e) {
				usleep(50000);
			}
		} while($this->startTime + ($this->lifeTime * 2) - 20 > microtime());
		
		// lock couldnt be removed; so delete it and exit (prepare for the next attempt)
		LockUtil::removeLock(self::LOCK_NAME);
		
		throw new SystemException('locking error; removed lock for next attempt');
	}
	
	/**
	 * Sets a new lock.
	 */
	protected function setLock() {
		$lockTime = 2 * $this->lifeTime + $this->startTime - microtime(true) - 10;
		
		LockUtil::setLock(self::LOCK_NAME, $lockTime);
	}
	
	/**
	 * Removes the lock.
	 */
	protected function removeLock() {
		if(!$this->removedLock) {
			LockUtil::removeLock(self::LOCK_NAME);
			
			$this->removedLock = true;
		}
	}
	
	/**
	 * Manages the execution and waiting.
	 */
	protected function mainLoop() {
		$this->execute();
		
		do {
			$this->wait();
			$this->execute();			
		} while($this->checkExecution());
	}
	
	/**
	 * Keeps the time distances between the executions.
	 */
	protected function wait() {		
		$elapsedTime = microtime(true) - $this->lastExecution;
		
		$moreExecutions = floor($elapsedTime / $this->interval); // should be 0
		$this->lastExecution += $this->interval * $moreExecutions;
		
		$waitTime = 1000000 * ($this->lastExecution + $this->interval - microtime(true));
		echo 'wait: '.$waitTime.';<br />';
		@ob_flush();
		flush();
		usleep($waitTime);
		
		$this->lastExecution = microtime(true);
	}
	
	/**
	 * Runs the wot event executer.
	 */
	protected function execute() {
		try {
			new WOTEventExecute(microtime(true));
		}
		catch(WOTEventExecuteException $e) {
			$this->logException($e);
		}
	}
	
	/**
	 * Loggs a occurred exception.
	 * 
	 * @param	WOTEventExecuteException
	 */
	protected function logException(WOTEventExecuteException $e) {
		// 'exception' is no reserved word in mysql; (rather than 'sqlexception')
		$sql = "INSERT INTO ugml_event_exception_log
				(eventID, eventTypeID, `time`,
				 exception)
				VALUES
				(".$e->getEventID().", ".$e->getEventTypeID().", ".time().",
				 '".escapeString($e)."')";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Checks whether there is time to wait and execute another time.
	 * 
	 * @return	int		time
	 */
	protected function checkExecution() {
		$maxTime = $this->startTime + ($this->maxExecutions - 1) * $this->interval;
		
		var_dump("TARGET:" . $maxTime . "; REAL: " . microtime(true));
		return (microtime(true) <= $maxTime);
	}
}
?>