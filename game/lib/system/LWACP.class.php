<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(WCF_DIR.'lib/system/WCFACP.class.php');

require_once(LW_DIR.'lib/util/LWUtil.class.php');

/**
 * This class extends the main WCFACP class by forum specific functions.
 *
 * @package	game.wot.core
 * @author	David Waegner
 */
class LWACP extends WCFACP {
	public static $ressourceTypes = array('metal', 'crystal', 'deuterium');
	public static $missionTypes = array(1 => 'Angreifen', 3 => 'Transport', 4 => 'Stationieren', 5 => 'Zerstören', 6 => 'Spionieren', 8 => 'Abbau', 9 => 'Kolonisieren');
	
	public function __construct() {
		try {
			parent::__construct();
		}
		catch(PermissionDeniedException $e) {
			// ..
		}
	}
	
	/**
	 * @see WCF::getOptionsFilename()
	 */
	protected function getOptionsFilename() {
		return LW_DIR.'options.inc.php';
	}

	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = new ACPTemplate(self::getLanguage()->getLanguageID(), array(LW_DIR.'acp/templates/', WCF_DIR.'acp/templates/'));
		$this->assignDefaultTemplateVariables();

	}
	
	/**
	 * @see	WCFACP::initSession()
	 */
	protected function initSession() {
		require_once(LW_DIR.'lib/system/session/LWACPSessionFactory.class.php');
		$factory = new LWACPSessionFactory();
		self::$sessionObj = $factory->get();
		
		// check if the user changed to another package in the ACP
		if (self::getSession()->packageID != PACKAGE_ID) {
			self::getSession()->updateUserData();
		}
		
		self::$userObj = self::getSession()->getUser();
	}

	/**
	 * Does the user authentication.
	 */
	protected function initAuth() {
		//echo '.';
		parent::initAuth();
		//echo ':';

		// user ban
		if (self::getUser()->banned) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		/*if(self::getUser()->userID && !self::getUser()->isGO()) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();			
		}*/
	}

	/**
	 * @see WCF::assignDefaultTemplateVariables()
	 */
	protected function assignDefaultTemplateVariables() {
		parent::assignDefaultTemplateVariables();

		self::getTPL()->assign(array(
			// add jump to board link
			//'additionalHeaderButtons' => '<li><a href="'.RELATIVE_WOT_DIR.'"><img src="../icon/indexS.png" alt="" /> <span>'.WCF::getLanguage()->get('wot.acp.jumpToCMS').'</span></a></li>',
			// individual page title
			'pageTitle' => 'Lost Worlds'
		));
	}

	/**
	 * @see WCF::loadDefaultCacheResources()
	 */
	protected function loadDefaultCacheResources() {
		parent::loadDefaultCacheResources();
		self::getCache()->addResource('bbcodes', WCF_DIR.'cache/cache.bbcodes.php', WCF_DIR.'lib/system/cache/CacheBuilderBBCodes.class.php');
		self::getCache()->addResource('smilies', WCF_DIR.'cache/cache.smilies.php', WCF_DIR.'lib/system/cache/CacheBuilderSmilies.class.php');
		//self::getCache()->addResource('LinkUs', CMS_DIR.'cache/cache.LinkUs.php', CMS_DIR.'lib/system/cache/CacheBuilderLinkUs.class.php');
	}

}
?>