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

require_once(LW_DIR.'lib/data/protection/BotDetectorClass.class.php');

/**
 * Detects bad words in sent messages.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class BadWordDetector extends BotDetectorClass {
	protected $badWordList = array(
		'arsch', 'arschgesicht', 'arschloch',
		'hurensohn', 'motherfucker', 'schlampe');
	protected $delimiters = '[\s\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]';
	
	/**
	 * @see	BotDetectorInterface::checkBot()
	 */
	public function checkBot() {
		$text = StringUtil::trim($_POST['text']);
		
		// string to lower case
		$text = StringUtil::toLowerCase($text);
		
		// split the text in single words
		$textSplit = preg_split('/'.$this->delimiters.'+/', $text);
		
		// check each word if it censored.
		foreach($textSplit as $word) {
			
			foreach($this->badWordList as $censoredWord) {
				$maxDiff = ceil(strlen($censoredWord) / 4);
				$diff = levenshtein($censoredWord, $word);
				
				if($diff <= $maxDiff) {
					$this->information .= '"'.$word.'" matched "'.$censoredWord.'" with ld '.$diff.';';
					
					continue 2;
				}
			}
		}
		
		if(!empty($this->information)) return true;		
		return false;
	}
}
?>