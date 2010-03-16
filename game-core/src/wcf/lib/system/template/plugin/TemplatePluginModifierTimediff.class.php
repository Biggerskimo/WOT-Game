<?php
// imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/TemplatePluginModifier.class.php');
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * The 'timediff' modifier formats an unix timestamp for difference of time.
 * It may contain days, hours, minutes and seconds
 * 
 * Usage:
 * $timestamp|timediff
 * 70000|timediff:'hours'
 *
 * @author	Biggerskimo 
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class TemplatePluginModifierTimediff implements TemplatePluginModifier {
	const DAY = 0x01;
	const HOUR = 0x02;
	const MINUTE = 0x04;
	const SECOND = 0x08;
	
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, Template $tplObj) {
		$time = $tagArgs[0];
		$flags = 0;
		
		if(isset($tagArgs[1])) {
			switch($tagArgs[1]) {
				case 'days':
					$flags |= self::DAY;
				case 'hours':
					$flags |= self::HOUR;
				case 'minutes':
					$flags |= self::MINUTE;
				case 'seconds':
					$flags |= self::SECOND;
					break;
				default:
					$flags = 0x0F;
			}			
		}
		// days
		if($flags & self::DAY) {
			$days = floor($time / 86400);
			$time %= 86400;
		}

		// hours
		if($flags & self::HOUR) {
			$hours = floor($time / 3600);
			$time %= 3600;
		}
		
		// minutes
		if($flags & self::MINUTE) {
			$minutes = floor($time / 60);
			$time %= 60;
		}
		
		// seconds
		if($flags & self::SECOND) {
			$seconds = ceil($time);
		}
		
		// format
		$formatArg1 = $formatArg2 = 'Seconds';
		if(isset($tagArgs[1])) {
			$formatArg1 = ucfirst($tagArgs[1]);
			$formatArg2 = $formatArg1;
		}
		if($days > 0) {
			if(!isset($formatArg1)) {
				$formatArg1 = 'Days';
			}
			$formatArg2 = 'Days';
		}
		if($hours > 0) {
			if(!isset($formatArg1)) {
				$formatArg1 = 'Hours';
			}
			$formatArg2 = 'Hours';
		}
		if($minutes > 0) {
			if(!isset($formatArg1)) {
				$formatArg1 = 'Minutes';
			}
			$formatArg2 = 'Minutes';
		}
		if($seconds > 0){	
			if(!isset($formatArg1)) {
				$formatArg1 = 'Seconds';
			}
			$formatArg2 = 'Seconds';
		}
		$format = 'wot.global.time.with'.$formatArg1.'To'.$formatArg2;
		$args = array(
			'$days' => $days,
			'$hours' => $hours,
			'$minutes' => $minutes,
			'$seconds' => $seconds
			);
		$formatted = WCF::getLanguage()->get($format, $args);
		
		return $formatted;
	}
}
?>
