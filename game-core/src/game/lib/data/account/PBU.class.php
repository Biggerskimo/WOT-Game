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

require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/system/io/ZipFile.class.php');

require_once(LW_DIR.'lib/data/account/AccountEditor.class.php');
require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
require_once(LW_DIR.'lib/data/system/System.class.php');

/**
 * Holds all functions to create and manage private backups.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 * @package		game.wot.user
 */
class PBU extends DatabaseObject {
	protected $fileHandler = null;
	protected $zipFileHandler = null;
	protected $readMode = null;
	
	protected $user = null;
	
	public static $backupTables = array(
			array('ugml_buddy', 'sender'),
			array('ugml_buddy', 'owner'),
			//array('ugml_messages', 'message_owner'),
			//array('ugml_messages', 'message_sender'),
			array('ugml_notes', 'owner'),
			array('ugml_planets', 'id_owner'),
			//array('ugml_stat', 'userID'),
			array('ugml_users', 'id')
		);
	public static $skipColumns = array(
			'ugml_users' =>
				array(
					'ally_id',
					'ally_name',
					'ally_request',
					'ally_register_time',
					'ally_request_text')
			);
	
	const ZIP_FILE_HANDLE = 0x01; // 2^0
	const FILE_HANDLE = 0x02; // 2^1
			
	/**
	 * Creates a new PBU object.
	 * 
	 * @param	int		pbu id
	 * @param	array	pbu row
	 */
	public function __construct($pbuID, $row = null) {
		if($row === null) {
			$sql = "SELECT *
					FROM ugml_pbu
					WHERE pbuID = ".$pbuID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Destroys this object.
	 */
	public function __destruct() {
		$this->close();
	}
	
	/**
	 * Closes the file.
	 * 
	 * @param	int		handle
	 */
	public function close($handle = null) {
		if($handle === null) $handle = self::ZIP_FILE_HANDLE | self::FILE_HANDLE;
		
		if($handle & 1 && $this->zipFileHandler !== null) {
			$this->getZipFile()->close();
			
			$this->zipFileHandler = null;
		}
		
		if($handle >> 1 & 1 && $this->fileHandler !== null) {
			$this->getFile()->close();
			
			$this->fileHandler = null;
		}
	}
	
	/**
	 * Creates a new backup.
	 * 
	 * @param	int		userid
	 * @param	array	changes the coordinates
	 * @return	PBU
	 */
	public static function create($userID, $coordChanges = array()) {
		$pbuID = self::insert($userID);
		$pbuObj = new PBU($pbuID);
		$pbuObj->backup($coordChanges);
		
		return $pbuObj;
	}
	
	/**
	 * Inserts a new private backup row.
	 * 
	 * @param	int		userid
	 * @param	int		serverid
	 * @param	int		time
	 * @return	int		pbuid
	 */
	public static function insert($userID, $serverID = SERVER_ID, $time = TIME_NOW) {
		$sql = "INSERT INTO ugml_pbu
				 (userID, serverID, `time`)
				VALUES
				 (".$userID.", ".$serverID.", ".$time.")";
		WCF::getDB()->sendQuery($sql);
		
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Returns a file name and creates one if none exists
	 * 
	 * @return	string	filename
	 */
	public function getFileName() {
		if(empty($this->fileName)) {
			$this->fileName = LW_DIR.'pbu/'.$this->pbuID.'.sql.gz';
			
			$sql = "UPDATE ugml_pbu
					SET fileName = '".escapeString($this->fileName)."'
					WHERE pbuID = ".$this->pbuID;
			WCF::getDB()->sendQuery($sql);
		}
		
		return $this->fileName;
	}
	
	/**
	 * Returns the gz file handler.
	 * 
	 * @param	bool	open in read mode
	 * @return	ZipFile
	 */
	public function getZipFile($read = null) {
		// change file handler
		if($this->readMode === null || ($read !== $this->readMode && $read !== null)) {
			// set default
			if($read === null) {
				$read = false;
			}
			// close old file handler
			$this->close();
			
			$this->readMode = $read;
			
			// open new
			if($read) {
				$mode = 'rb';
			}
			else {
				$mode = 'ab';
			}
			
			$this->zipFileHandler = new ZipFile($this->getFileName(), $mode);
			$this->zipFileHandler->rewind();
		}
		
		return $this->zipFileHandler;
	}
	
	/**
	 * Returns the file handler of the gzip file.
	 * 
	 * @return	File
	 */
	public function getFile() {
		if($this->fileHandler === null) {
			$this->fileHandler = new File($this->getFileName(), 'a+b');
		}
		
		return $this->fileHandler;
	}

	/**
	 * Returns the account to which this backup is related.
	 * 
	 * @return	LWUser
	 */
	public function getUser() {
		if($this->user === null) {
			$this->user = new LWUser($this->userID);
		}
		
		return $this->user;
	}
	
	/**
	 * Reads the data for the backup from the db and saves it to the file.
	 * 
	 * @param	array	coordinates changes of the planets
	 */
	public function backup($coordChanges) {
		global $resource;
		
		// create file
		$file = $this->getZipFile(false);
		
		// write headers
		$head = "-- WOT Game\n";
		$head .= "-- database: ".WCF::getDB()->getDatabaseName()."\n";
		$head .= "-- Private BackUp ".$this->pbuID." of user: ".$this->userID."\n";
		$head .= "-- generated at ".date('r')."\n\n";
		$head .= "-- DO NOT EDIT THIS FILE\n\n";
		$head .= "-- WCF DATABASE CHARSET\n";
		$head .= "SET NAMES ".WCF::getDB()->getCharset().";\n\n";
		$file->write($head);
		
		// read currently flying fleets
		
		$fleetObjs = Fleet::getByUserID($this->userID);
		
		foreach($fleetObjs as $fleetObj) {
			foreach($fleetObj->fleet as $specID => $count) {
				$fleets[$fleetObj->startPlanetID][$resource[$specID]] = $count;
			}
		}
		
		$inserted = false;
		$planets = array();
		$planetCount = 0;
		$mainPlanetCount = 0;
		$coordsToPlanetID = array();
		
		foreach(self::$backupTables as $tableArray) {
			$table = $tableArray[0];
			$col = $tableArray[1];
			
			$sql = "SELECT *
					FROM `".$table."`
					WHERE `".$col."` = ".$this->userID;
			// order moon directly after planet
			if($table == 'ugml_planets') {
				$sql .= " ORDER BY galaxy,
							system,
							planet,
							planet_type";
			}
			$result = WCF::getDB()->sendQuery($sql);
			
			if(WCF::getDB()->countRows($result)) {
				if($inserted) {
					$file->write("\n\n\n");
				}
				
				// check column types
				$sql = "SHOW COLUMNS FROM `".$table."`";				
				$result2 = WCF::getDB()->sendQuery($sql);
				
				$escapeColumns = array();
				while($row = WCF::getDB()->fetchArray($result2)) {
					// skip columns manually
					if(isset(self::$skipColumns[$table]) && in_array($row['Field'], self::$skipColumns[$table])) {
						$escapeColumns[$row['Field']] = 2;
						continue;
					}
					
					// escape string
					if(strpos($row['Type'], 'int') === false) {
						$escapeColumns[$row['Field']] = 1;
						continue;
					}
					
					// skip auto_increment automatically
					/*if($row['Extra'] == 'auto_increment') {
						$escapeColumns[$row['Field']] = 2;
						continue;
					}*/
					
					// change planet coords
					if(($table == 'ugml_planets' || $table == 'ugml_users') && ($row['Field'] == 'galaxy' || $row['Field'] == 'system' || $row['Field'] == 'planet')) {
						$escapeColumns[$row['Field']] = 3;
						continue;
					}
					
					// change main planet id
					if($table == 'ugml_users' && ($row['Field'] == 'id_planet' || $row['Field'] == 'current_planet')) {
						$escapeColumns[$row['Field']] = 4;
						continue;
					}
					
					// do not escape (integer)
					$escapeColumns[$row['Field']] = 0;
				}
				
				$inserted = false;
				
				// escape columns with backticks
				$columns = array();				
				foreach($escapeColumns as $column => $escape) {
					if($escape != 2) {
						$columns[] = "`".$column."`";
					}
				}
								
				// we could use "INSERT INTO table VALUES ..."
				// but if there would be added some columns
				// after the backup and before the restore, the
				// count of columns would not be the same anymore
				// and MySQL would throw a error. so we better
				// use "INSERT INTO table ... VALUES ..."
				$tableStr = "INSERT INTO `".$table."`";
				$tableStr .= "\n (".implode(",",$columns).")";
				$tableStr .= "\nVALUES";
				
				while($row = WCF::getDB()->fetchArray($result)) {					
					if($inserted) {
						$file->write("\n\n");
					}
					$file->write($tableStr);
					
					// set coord variables if needed ...
					$changeCoords = false;
					if($table == 'ugml_planets') {
						// ... for planet rows (planet)
						if($row['planet_type'] == 1 && isset($coordChanges[$row['id']])) {
							$changeCoords = true;
							
							$galaxy = $coordChanges[$row['id']]['galaxy'];
							$system = $coordChanges[$row['id']]['system'];
							$planet = $coordChanges[$row['id']]['planet'];
						}
						// ... for planet rows (moon)
						else if($row['planet_type'] == 3) {
							// last planet row was the planet with this moon,
							// so we can take the "old" coord variables
							
							$system = new System($row['galaxy'], $row['system']);
							$planet = $system->getPlanet($row['planet'], 1);
							
							if(isset($coordChanges[$planet->planetID])) {
								$changeCoords = true;
							}
							echo 'checked moon coord change ...';
							var_Dump($changeCoords);
						}
						
						// register planet count for main planet correction (referential integrity)
						if($row['id'] == $this->getUser()->id_planet) {
							$mainPlanetCount = $planetCount;
						}
					} else if($table == 'ugml_users') {
						// ... for user row	(main planet)				
						if(isset($coordChanges[$row['id_planet']])) {
							$changeCoords = true;
							
							$galaxy = $coordChanges[$row['id_planet']]['galaxy'];
							$system = $coordChanges[$row['id_planet']]['system'];
							$planet = $coordChanges[$row['id_planet']]['planet'];
						}
					}
					
					foreach($row as $column => $value) {
						switch($escapeColumns[$column]) {
							// escape strings
							case 1:
								$row[$column] = "'".escapeString($value)."'";
								continue 2;
							
							// remove
							case 2:
								unset($row[$column]);
								$$column = $value;
								continue 2;
							
							// replace coordinates
							case 3:
								if($changeCoords) {
									$row[$column] = $$column;
								}
								continue 2;
							
							// replace main planet id
							case 4:
								$row[$column] = "@pid".$mainPlanetCount;
								continue 2;
							
							// escape empty vars
							default:
								if(empty($value) && !is_numeric($value)) {
									$row[$column] = "''";
								}
								continue 2;
						}
					}
					
					// add currently flying fleets
					if($table == 'ugml_planets' && isset($fleets[$id])) {
						foreach($fleets[$id] as $column => $count) {
							$row[$column] += $count;
						}
					}
					
					// write
					$file->write(" (".implode(",",$row).");");
					
					if($table == 'ugml_planets') {
						$file->write("\nSET @pid".$planetCount." = LAST_INSERT_ID();");
						++$planetCount;
					}
					
					$inserted = true;					
				}
			}
		}
	}
	
	/**
	 * Returns the length of the uncompressed sql file in bytes.
	 * 
	 * @return	int		length
	 */
	public function getSqlFileSize() {
		$this->getFile()->seek(-4, SEEK_END);
		$buffer = $this->getFile()->read(4);
		$filesize = end(unpack("V", $buffer));
		
		return $filesize;
	}
	
	/**
	 * Recovers this backup to the database.
	 */
	public function recover() {
		// we wont use ZipFile::getFileSize(), because our algorithm seems to be faster (and not so buggy ?!).
		// reads the isize block of the gzip file; see RFC 1952 (http://tools.ietf.org/html/rfc1952)
		//$filesize = $this->getFile()->getFileSize();
		//$filesize = $this->getZipFile(true)->getFileSize();
		$filesize = 1<<27; // 128 MiB
		var_dump($filesize);
		$sqlStr = $this->getZipFile(true)->read($filesize);
		echo '->'.$sqlStr.'<-';
		
		require_once(WCF_DIR.'lib/system/database/QueryParser.class.php');
		
		//var_dump($sqlStr);
		QueryParser::sendQueries($sqlStr);
	}
}
?>