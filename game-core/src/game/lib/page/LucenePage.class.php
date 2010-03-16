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

require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(LW_DIR.'lib/data/search/LuceneEditor.class.php');

/**
 * Shows a small test page ;)
 * 
 * @author Biggerskimo
 * @copyright 2008 Lost Worlds <http://lost-worlds.net>
 */
class LucenePage extends AbstractPage {
	protected $luceneID = 0;
	protected $start = 0;
	
	const INTERVAL = 500;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_REQUEST['luceneID'])) $this->luceneID = intval($_REQUEST['luceneID']);
		if(isset($_REQUEST['start'])) $this->start = intval($_REQUEST['start']);
	}
	
	/**
	 * 
	 */
	public function getLucene() {
		return new Lucene($this->luceneID);
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		if($this->action == 'create') {
			$fields = array(
				'messageID' => 'UnIndexed',
				'senderID' => 'Keyword',
				'sender' => 'Text',
				'ownerID' => 'Keyword',
				'time' => 'Keyword',
				'messageType' => 'Keyword',
				'subject' => 'Text',
				'message' => 'Text'
			);
			$luceneObj = LuceneEditor::create('messages', $fields);
			var_dump($luceneObj);
		}
		else if($this->action == 'count') {			
			$luceneObj = $this->getLucene();
			
			var_dump($luceneObj->count());
		}
		else if($this->action == 'search') {
			$time = microtime(true);
			
			$luceneObj = $this->getLucene();
			
			$hits = $luceneObj->search($_REQUEST['search']);
			echo count($hits).' in '.(microtime(true) - $time).'s:<br>"';
			foreach($hits as $hit) {
				echo $hit->id.'"<br>"';
				echo $hit->ownerID.'"<br>"';
				echo $hit->messageID.'"<br>"';
				echo $hit->time.'"<br>"';
				echo $hit->subject.'"<br>"';
				echo $hit->message.'"<br><br>';
			}
		}
		else if($this->action == 'termDocs') {
			$time = microtime(true);
			
			$luceneObj = $this->getLucene();
			
			$ids = $luceneObj->termDocs($_REQUEST['search'], $_REQUEST['f']);			
		
			echo count($ids).' in '.(microtime(true) - $time).'s:<br>"';
			foreach($ids as $id) {
				$hit = $luceneObj->getDocument($id);
				echo $id.'"<br>"';
				echo $hit->ownerID.'"<br>"';
				echo $hit->messageID.'"<br>"';
				echo $hit->time.'"<br>"';
				echo $hit->subject.'"<br>"';
				echo $hit->message.'"<br><br>';
			}
		}
		else if($this->action == 'test') {
			var_dump($_REQUEST['search']);
			$time = microtime(true);
			
			$luceneObj = $this->getLucene();
			$luceneObj->getIndex();
			
			$query = Zend_Search_Lucene_Search_QueryParser::parse($_REQUEST['search']);
			var_dump($query);
			echo '<br>';
			echo '<br>';
			$query = $query->rewrite($luceneObj->getIndex())->optimize($luceneObj->getIndex());
			var_dump($query);
			echo '<br>';
			echo '<br>';
			//var_dump(Zend_Search_Lucene_Search_QueryParser::$_instance);
			echo '<br>';
			echo '<br>';
			var_dump(Zend_Search_Lucene_Search_QueryParser::$_instance->_context->getQuery());
			echo '<br>';
			echo '<br>';
			/*$expressionRecognizer = new Zend_Search_Lucene_Search_BooleanExpressionRecognizer();
			$expressionRecognizer->processLiteral(Zend_Search_Lucene_Search_QueryParser::$_instance->_context->_entries[0]);
			$conjuctions = $expressionRecognizer->finishExpression();
			var_dump(Zend_Search_Lucene_Search_QueryParser::$_instance->_context->_entries[0], $conjuctions);*/
			//$query2 = Zend_Search_Lucene_Search_QueryParser::$_instance->_context->_entries[0]->getQuery(null);
			var_dump($query2);
			echo '<br>';
			echo '<br>';			
			$token = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize('1');
			var_dump($token);
			
			echo 'tested in '.(microtime(true) - $time).'s<br>';
		}
		else if($this->action == 'test2') {
			$time = microtime(true);
			
			$luceneObj = $this->getLucene();
			
			$lexer = new Zend_Search_Lucene_Search_QueryLexer();
			var_dump($lexer);
			echo '<br>';
			echo '<br>';
			$tokens = $lexer->tokenize($_REQUEST['search']);
			var_dump($tokens);
			
			echo 'tested in '.(microtime(true) - $time).'s<br>';
		}
		else if($this->action == 'list') {
			$time = microtime(true);
			
			$luceneObj = $this->getLucene();
			
			$hits = $luceneObj->terms();
			
			echo 'found in '.(microtime(true) - $time).'s:<br>';
			var_dump($hits);
		}
		else if($this->action == 'optimize') {
			$time = microtime(true);
			
			$luceneObj = $this->getLucene();
			
			$luceneObj->optimize();
			
			echo 'optimized in '.(microtime(true) - $time).'s<br>';
		}
		else if($this->action == 'process') {
			require_once('../wcf/lib/system/io/File.class.php');
			include('/tmp/cache.php');
			ob_start();
			//
			//
			set_time_limit(120);
			$luceneObj = $this->getLucene();
			
			$sql = "SELECT *
					FROM ugml_messages
					WHERE message_id BETWEEN ".$this->start." AND ".($this->start + self::INTERVAL);
			$result = WCF::getDB()->sendQuery($sql);
			
			$array = array();
			$i = 0;
			$time = microtime(true);
			while($row = WCF::getDB()->fetchArray($result)) {
				$fields = array(
					'messageID' => $row['message_id'],
					'senderID' => $row['message_sender'],
					'sender' => $row['message_from'],
					'ownerID' => $row['message_owner'],
					'time' => $row['message_time'],
					'messageType' => $row['message_type'],
					'subject' => $row['message_subject'],
					'message' => $row['message_text']
				);
				
				$array[] = $fields;
				++$i;
			}
			if(count($array)) {
				$luceneObj->add($array);
			}
			else {
				echo 'i think im done :)';
			}
			echo 'done '.$i.' in '.(microtime(true) - $time).';<br>';
						
			$output .= ob_get_contents();
			ob_end_clean();
			
			$file = new File('/tmp/cache.php');
			$file->write("<?php
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
\n\$output = '".$output."';\n?>");
			$file->close();
			echo $output;
			?>
			<br>
			<br>
			<br>
			<br>
			<br>
			next link:
			<br>
			<a href="index.php?page=Lucene&action=process&luceneID=<?php
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
 echo $this->luceneID; ?>&start=<?php
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
 echo $this->start+self::INTERVAL; ?>">
				index.php?page=Lucene&action=process&luceneID=<?php
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
 echo $this->luceneID; ?>&start=<?php
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
 echo $this->start+self::INTERVAL; ?>
			</a>
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
			
			usleep(100000);
			ob_flush();
			flush();
			if(count($array)) {
				?>
				<script>
					window.location.href = 'index.php?page=Lucene&action=process&luceneID=<?php
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
 echo $this->luceneID; ?>&start=<?php
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
 echo $this->start+self::INTERVAL; ?>';
				</script>
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

			}
			exit;
		}
	}
}
?>