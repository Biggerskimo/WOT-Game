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

if(!defined('ZEND_INCLUDED')) {
	define('ZEND_INCLUDED', true);

	if(!defined('APPLICATION_PATH')) define('APPLICATION_PATH', LW_DIR);

	if(!defined('APPLICATION_ENVIRONMENT')) define('APPLICATION_ENVIRONMENT', 'development');

	set_include_path(APPLICATION_PATH.'..' 
    	.PATH_SEPARATOR.
    	get_include_path());
    	
	require_once "Zend/Loader.php";
	Zend_Loader::registerAutoload();
}
require_once('Zend/Search/Lucene.php');
require_once(LW_DIR.'lib/data/search/LuceneEditor.class.php');

/**
 * Provides functions to access the lucene search function of the zend framework.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class Lucene extends DatabaseObject {
	protected $fields = array();
	
	protected $index = null;
	protected $initializedAnalyzer = false;
	
	/**
	 * Creates a new Lucene Object.
	 * 
	 * @param	int		luceneID
	 * @param	array	row
	 */
	public function __construct($luceneID, $row = null) {
		if($row === null) {
			$sql = "SELECT *
					FROM ugml_lucene
					WHERE luceneID = ".$luceneID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the editor of this object.
	 * 
	 * @return	LuceneEditor
	 */
	public function getEditor() {
		return new LuceneEditor($this);
	}
	
	/**
	 * Loads the field data.
	 */
	protected function loadFieldData() {
		if(!count($this->fields)) {
			$sql = "SELECT fieldName,
						fieldType
					FROM ugml_lucene_field
					WHERE luceneID = ".$this->luceneID;
			$result = WCF::getDB()->sendQuery($sql);
			while($row = WCF::getDB()->fetchArray($result)) {
				$this->fields[$row['fieldName']] = $row['fieldType'];
			}
		}
	}
	
	/**
	 * Opens the index.
	 * 
	 * @return Zend_Search_Lucene_Interface
	 */
	public function getIndex() {
		if($this->index === null) {
			$this->index = Zend_Search_Lucene::open(LW_DIR.'lucene/'.$this->name);
			
			$this->initAnalyzer();
		}
		return $this->index;
	}
	
	/**
	 * Adds a new analyzer.
	 */
	private function initAnalyzer() {
		if(!$this->initializedAnalyzer) {
			$analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive();
			
			Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);
			$this->initializedAnalyzer = true;
		}
	}
	
	/**
	 * Adds a document to the index.
	 * 
	 * @param	array	documents or fields
	 * @param	bool	commit after adding
	 * @param	bool	ignore missing fields
	 */
	public function add($documents, $commit = true, $ignoreMissingFields = false) {
		$this->loadFieldData();
		
		if(LWUtil::getDimensionCount($documents) == 1) {
			$documents = array($documents);
		}
		foreach($documents as $fields) {
			$document = new Zend_Search_Lucene_Document();
			
			foreach($this->fields as $fieldName => $fieldType) {
				if(!isset($fields[$fieldName])) {
					if(!$ignoreMissingFields) {
						var_Dump($fields);
						require_once(WCF_DIR.'lib/system/exception/SystemException.class.php');
						throw new SystemException('missing field '.$fieldName);
					}
				}
				
				$field = call_user_func_array(array('Zend_Search_Lucene_Field', $fieldType), array($fieldName, $fields[$fieldName]));
			
				$document->addField($field);
			}
			
			$this->getIndex()->addDocument($document);
		}
		if($commit) {
			$this->getIndex()->commit();
		}
	}
	
	/**
	 * @see Zend_Search_Lucene::count()
	 */
	public function count() {
		return $this->getIndex()->count();
	}
	
	/**
	 * @see Zend_Search_Lucene::terms()
	 */
	public function terms() {
		return $this->getIndex()->terms();
	}
	
	/**
	 * @see Zend_Search_Lucene::optimize()
	 */
	public function optimize() {
		return $this->getIndex()->terms();
	}
	
	/**
	 * @see Zend_Search_Lucene::termDocs()
	 */
	public function termDocs($term, $fieldName) {
		$termObj = new Zend_Search_Lucene_Index_Term($term, $fieldName);
		
		return $this->getIndex()->termDocs($termObj);
	}
	
	/**
	 * @see Zend_Search_Lucene::find()
	 */
	public function search() {
		$args = func_get_args();
		return call_user_func_array(array($this->getIndex(), 'find'), $args);
	}
	
	/**
	 * @see	Zend_Search_Lucene::getDocument()
	 */
	public function getDocument($id) {
		return $this->getIndex()->getDocument($id);
	}
}
?>