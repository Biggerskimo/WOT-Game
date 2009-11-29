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

/**
 * Contains functions for filesystem-based locking.
 */
class LockUtil {
	/**
	 * Locks a account for a specific time.
	 * 
	 * @param	int		user id
	 * @param	int		seconds to lock
	 */
	public static function setLock($identifier, $lockTime) {
		require_once(WCF_DIR.'lib/system/io/File.class.php');
		$lockFile = new File(LW_DIR.'lock/lock'.$identifier.'.lock.php');
		
		$lockTimeEnd = microtime(true) + $lockTime;
		
		$lockFile->write("<?php
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
\n\$lockTimeEnd = ".$lockTimeEnd.";\n?>");
		$lockFile->close();
	}
	
	/**
	 * Removes a existing lock for a user.
	 * 
	 * @param	string	identifier
	 */
	public static function removeLock($identifier) {
		if(file_exists(LW_DIR.'lock/lock'.$identifier.'.lock.php')) {
			unlink(LW_DIR.'lock/lock'.$identifier.'.lock.php');
		}
	}
	
	/**
	 * Checks for a lock in the file system. If a old is found, it will be removed.
	 *  Otherwise, a SystemException will be thrown.
	 * 
	 * @param	int		user id
	 */
	public static function checkLock($identifier) {
		if(file_exists(LW_DIR.'lock/lock'.$identifier.'.lock.php')) {
			include(LW_DIR.'lock/lock'.$identifier.'.lock.php');
			
			// throw exception
			if($lockTimeEnd > microtime(true)) {
				require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
				throw new SystemException('account is locked for next '.ceil($lockTimeEnd - microtime(true)).'s');
			}
			
			// remove lock
			self::removeLock($identifier);
		}
	}
}
?>