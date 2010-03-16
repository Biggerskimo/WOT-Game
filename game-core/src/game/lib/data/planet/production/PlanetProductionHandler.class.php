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

require_once(LW_DIR.'lib/data/AbstractDecorator.class.php');

/**
 * This class is able the calculate and cache the production.
 * 
 * @copyright	2007-2009 Lost Worlds <http://lost-worlds.net>
 * @author		Biggerskimo
 */
class PlanetProductionHandler extends AbstractDecorator {
	protected static $productorsList = array('hangar', 'resource');
	
	protected $planetID = 0;
	protected $productors = array();
	
	protected $changed = false;
	protected $changes = array();
	
	/**
	 * Creates a new PlanetProductionHandler object.
	 * 
	 * @param	int
	 */
	public function __construct($planetID) {		
		$this->planetID = $planetID;
	}
	
	/**
	 * @see AbstractDecorator::getObject()
	 */
	protected function getObject() {
		return Planet::getInstance($this->planetID, null, false);
	}
	
	/**
	 * Returns a productor object.
	 * 
	 * @param	string	$productorName
	 * @return	PlanetProduction
	 */
	public function getProductorObject($productorName) {
		if(!isset($this->productors[$productorName])) {
			$className = StringUtil::firstCharToUpperCase($productorName).'Production';
			$fileName = LW_DIR.'lib/data/planet/production/'.$className.'.class.php';
			
			if(!file_exists($fileName)) {
				throw new PlanetException('can not find production class file '.$fileName);
			}
			require_once($fileName);
			if(!class_exists($className)) {
				throw new PlanetException('production class '.$className.' does no exist');
			}
			$this->productors[$productorName] = new $className($this->getObject());
		}
		return $this->productors[$productorName];
	}
	
	/**
	 * Produces things.
	 */
	public function produce() {
		$this->changed = false;
		
		if(!WCF::getUser()->urlaubs_modus) {
			foreach(self::$productorsList as $productorName) {
				$prodObj = $this->getProductorObject($productorName);
							
				$prodObj->produce();
				
				$changed = $prodObj->checkChanges();			
				$this->changed = $this->changed || $changed;			
				if($changed) {
					$changes = $prodObj->getChanges();
					
					foreach($changes as $colName => $value) {
						if(!is_array($value)) {
							$value = array(1);
						}
						// TODO: handle conflicts
						switch($value[0]) {
							case 0:
								$this->changes[$colName] = $value;
								$this->getObject()->$colName = $value;
								break;
								
							case 2:
								if(isset($this->changes[$colName])) {
									$this->changes[$colName][1] += $value[1];
								} else {
									$this->changes[$colName] = array(2, $value[1]);
								}
								//if(WCF::getUser()->userID == 1) var_dump($colName, $this->getObject()->$colName, $value);
								$this->getObject()->$colName += $value[1];
								break;
								
							//case 1:
							default:
								$this->changes[$colName] = array(1, $this->getObject()->$colName);
						}
					}
					
					if(method_exists($prodObj, 'resetChanges')) {
						$prodObj->resetChanges();
					}
				}
			}
		}
		if($this->getObject()->last_update < time()) $this->changed = true;
		$this->getObject()->last_update = time();
		
		$this->save();
	}
	
	/**
	 * Saves all changes.
	 */
	protected function save() {
		global $planetrow;
		
		if($this->changed) {
			$changes = array();
			
			$sql = "UPDATE ugml_planets
					SET ";
			foreach($this->changes as $colName => $change) {
				switch($change[0]) {
					case 0:
						$sql .= "`".$colName."` = '".$change[1]."', ";
						break;
						
					case 2:
						$sql .= "`".$colName."` = `".$colName."` + '".$change[1]."', ";
						break;
						
					//case 1:
					default:
						$sql .= "`".$colName."` = '".$this->getObject()->$colName."', ";
				}
			}
			
			$sql .= "last_update = ".time()."
					WHERE id = ".$this->planetID;
			WCF::getDB()->sendQuery($sql);
			
			$this->changes = array();
		}
	}
}
?>