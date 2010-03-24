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

// lw imports
require_once(LW_DIR.'lib/util/LWUtil.class.php');
require_once(LW_DIR.'lib/util/SerializeUtil.class.php');
require_once(LW_DIR.'lib/util/WOTUtil.class.php');
require_once(LW_DIR.'lib/data/planet/Planet.class.php');
require_once(LW_DIR.'lib/system/spec/Spec.class.php');

/**
 * This class extends the main WCF class by game specific functions.
 *
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class LWCore extends WCF {
	protected static $headerMenuObj = null;
	protected static $userCPMenuObj = null;
	protected static $styleObj;
	public static $availablePagesDuringOfflineMode = array(
		'page' => array('Captcha', 'LegalNotice'),
		'form' => array('UserLogin'),
		'action' => array());
	public static $ressourceTypes = array('metal', 'crystal', 'deuterium');
	public static $missionTypes = array(1 => 'Angreifen', 3 => 'Transport', 4 => 'Stationieren', 5 => 'Zerstoeren', 6 => 'Spionieren', 8 => 'Abbau', 9 => 'Kolonisieren', 11 => 'Verbandsangriff', 12 => 'Halten', 20 => 'Interplanetarraketen-Angriff');
	protected static $planetObj = null;
	public static $startTime = 0;
	public static $requestID = 0;
	
	protected static $alliance = null;
	protected static $allianceEditor = null;

	/**
	 * @see WCF::__construct()
	 */
	public function __construct() {
		// include options before locking
		try {
			$this->initOptions();
		} catch(Exception $e) {
			// ignore ...
		}
		
		// check locking
		$userID = @intval($_COOKIE[COOKIE_PREFIX.'userID']);
		if($userID)	{
			$i = 0;
			do {
				try {
				LWUtil::checkLock($userID);
					
					// everything okay
					break;
				}
				catch(SystemException $e) {
					if($i >= 6) {
						die($e->getMessage());
					}
					++$i;
					
					usleep(500000);
				}
			} while(true);
		}
		
		// bugfix
		if(WCF::getDB() !== null) return false;
		
		parent::__construct();
		
		// game-frontend only
		if($this->getUser()->userID == 0) return;
		$args = array_merge($_GET, $_POST);
		
		if(isset($args['password'])) {
			unset($args['password']);
		}

		// log request		
		$sql = "INSERT INTO ugml_request
				(userID, `time`, ip,
				 data)
				VALUES
				(".$this->getUser()->userID.", ".TIME_NOW.", INET_ATON('".$_SERVER['REMOTE_ADDR']."'),
				 '".escapeString(SerializeUtil::serialize(array('page' => LWUtil::getFileName(), 'args' => $args)))."')";
		WCF::getDB()->sendQuery($sql);		
		self::$requestID = WCF::getDB()->getInsertID();

		if($this->getUser()->lastLoginTime < (TIME_NOW - 60 * 60 * 12) && $this->getUser()->lastLoginTime > 1188597600 /* <- 07/09/01 00:00 */ && !defined('LOGIN')) self::logout('index.htm');

		if($this->getUser()) {
		
			if($this->getUser()->urlaubs_modus == 2) {
				$sql = "UPDATE ugml_users
						SET urlaubs_modus = 0
						WHERE id = ".$this->getUser()->userID;
				WCF::getDB()->sendQuery($sql);
				Session::resetSessions($this->getUser()->userID);
				$this->getUser()->urlaubs_modus = 0;	
			}
		}
		
		// TODO dirty banned-fix	
		if($this->getUser()->wotBanned) {
			$row = WCF::getDB()->getFirstRow("SELECT * FROM ugml_banned WHERE who = '".WCF::getUser()->username."' ORDER BY id DESC");
			
			require_once(WCF_DIR.'lib/system/exception/NamedUserException.class.php');
			throw new NamedUserException('Du bist bis '.date("d.m.Y G:i:s", $row['longer']).' von <a href="mailto:'.$row['email'].'?subject=banned:'.$row['who'].'">'.$row['author'].'</a> gesperrt. Grund:<br><br>'.$row['theme'], 'Gebannt');
		}
		
		$this->initPlanet();
		
		// detect bots
		/*require_once(LW_DIR.'lib/data/protection/BotDetector.class.php');
		new BotDetector();*/
		
		$this->initSpec();
	}
	
	
	/**
	 * @see WCF::destruct()
	 */
	public function __destruct() {
		//require_once(LW_DIR.'lib/data/protection/BotDetector.class.php');
		//new BotDetector();
	}

	public static function logout($newSite = false) {
		global $game_config;

		require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
		WCF::getSession()->changeUser(new UserSession());

		// remove cookies
		if (isset($_COOKIE[COOKIE_PREFIX.'userID'])) {
			HeaderUtil::setCookie('userID', 0);
		}
		if (isset($_COOKIE[COOKIE_PREFIX.'password'])) {
			HeaderUtil::setCookie('password', '');
		}
		setcookie($game_config['COOKIE_NAME'], "", time()-100000, "/", "", 0);

		if($newSite === false) return;

		echo '<html>
				<head>
					<script language="JavaScript" >
						top.location.href = \''.$newSite.'?time='.TIME_NOW.'\';
					</script>
				</head>
				<body>
					<center>
						<a href="javascript:top.location.href=\''.$newSite.'?time='.TIME_NOW.'\'">
							Du wurdest ausgeloggt. Hier klicken, um wieder auf die Startseite zu kommen.
						</a>
					</center>
				</body>
			  </html>';
		exit;
	}
	
	/**
	 * Returns a $game_config value.
	 *
	 * @param	string name
	 * @return	value
	 */
	public function getGameConfig($name) {
		global $game_config;
		
		return $game_config[$name];
	}

	/**
	 * @see WCF::initTPL()
	 */
	protected function initTPL() {
		parent::initTPL();
		
		self::$tplObj->setTemplatePaths(array(LW_DIR.'templates/', WCF_DIR.'templates/'));
		

		// init cronjobs
		//$this->initCronjobs();
	}

	/**
	 * Initialises the cronjobs.
	 */
	protected function initCronjobs() {
		try {
			self::getTPL()->assign('executeCronjobs', WCF::getCache()->get('cronjobs-'.PACKAGE_ID, 'nextExec') < TIME_NOW);
		} catch(Exception $e) {
			self::getTPL()->assign('executeCronjobs', false);
		}
	}

	/**
	 * @see WCF::loadDefaultCacheResources()
	 */
	protected function loadDefaultCacheResources() {
		parent::loadDefaultCacheResources();
		self::getCache()->addResource('pageLocations-'.PACKAGE_ID, WCF_DIR.'cache/cache.pageLocations-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderPageLocations.class.php');
		self::getCache()->addResource('bbcodes', WCF_DIR.'cache/cache.bbcodes.php', WCF_DIR.'lib/system/cache/CacheBuilderBBCodes.class.php');
		self::getCache()->addResource('smilies', WCF_DIR.'cache/cache.smilies.php', WCF_DIR.'lib/system/cache/CacheBuilderSmilies.class.php');
		self::getCache()->addResource('cronjobs-'.PACKAGE_ID, WCF_DIR.'cache/cache.cronjobs-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderCronjobs.class.php');
		self::getCache()->addResource('help-'.PACKAGE_ID, WCF_DIR.'cache/cache.help-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderHelp.class.php');
		
		self::getCache()->addResource('spec-'.PACKAGE_ID, WCF_DIR.'cache/cache.spec-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderSpecs.class.php');
		//var_dump(self::getCache()->get('spec-'.PACKAGE_ID));
	}

	/**
	 * Initialises the page header menu.
	 */
	protected static function initHeaderMenu() {
		require_once(WCF_DIR.'lib/page/util/menu/HeaderMenu.class.php');
		self::$headerMenuObj = new HeaderMenu();
		if (HeaderMenu::getActiveMenuItem() == '') HeaderMenu::setActiveMenuItem('wot.header.menu.homepage');
	}

	/**
	 * Initialises the page header menu.
	 */
	protected static function initUserCPMenu() {
		require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
		self::$userCPMenuObj = UserCPMenu::getInstance();
	}

	/**
	 * @see WCF::getOptionsFilename()
	 */
	protected function getOptionsFilename() {
		return LW_DIR.'options.inc.php';
	}

	/**
	 * Changes the active style.
	 *
	 * @param	integer		$styleID
	 */
	public static final function changeStyle($styleID) {
		require_once(WCF_DIR.'lib/system/style/Style.class.php');
		self::$styleObj = new Style($styleID);

		if (self::getTPL()) {
			self::getTPL()->setTemplatePackID(self::getStyle()->templatePackID);
		}
	}

	/**
	 * @see HeaderMenuContainer::getHeaderMenu()
	 */
	public static final function getHeaderMenu() {
		if (self::$headerMenuObj === null) {
			self::initHeaderMenu();
		}

		return self::$headerMenuObj;
	}

	/**
	 * @see UserCPMenuContainer::getUserCPMenu()
	 */
	public static final function getUserCPMenu() {
		if (self::$userCPMenuObj === null) {
			self::initUserCPMenu();
		}

		return self::$userCPMenuObj;
	}

	/**
	 * Returns the active style object.
	 *
	 * @return	Style
	 */
	public static final function getStyle() {
		return self::$styleObj;
	}

	/**
	 * @see WCF::initSession()
	 */
	protected function initSession() {
		// start session
		require_once(LW_DIR.'lib/system/session/LWSessionFactory.class.php');
		$factory = new LWSessionFactory();
		self::$sessionObj = $factory->get();
		self::$userObj = self::getSession()->getUser();
		self::$userObj->setUserVar();
		
		// update activity
		register_shutdown_function(array($this, 'updateActivity'));
	}
	
	public function updateActivity() {
		$eno = 0;
		$estr = '';
		@fopen('http://lost-worlds.net/glog.php?s='
			.rawurlencode($_SERVER['SERVER_NAME'])
			.'&c='.$_SERVER['REMOTE_ADDR']
			.'&u='.rawurlencode($_SERVER['REQUEST_URI']), 'r'); 
	}

	/**
	 * @see	WCF::assignDefaultTemplateVariables()
	 */
	protected function assignDefaultTemplateVariables() {
		parent::assignDefaultTemplateVariables();

		$dpath = (!self::getUser()->dpath) ? DEFAULT_SKINPATH : self::getUser()->dpath;
		if(!defined('DPATH')) define('DPATH', $dpath);
				
		self::getTPL()->assign(array('dpath' => $dpath,
				'planets' => Planet::getByUserID(WCF::getUser()->userID, null, true),
				'site' => LWUtil::getFileName(),
				'args' => LWUtil::getArgsStr()));
	}

	/**
	 * Calculates ressources and executes the event handler
	 */
	public function initPlanet() {
		global $planetrow;
		
		self::$planetObj = Planet::getInstance(self::getUser()->current_planet);

		self::$planetObj->setPlanetRow();
		
		self::getTPL()->assign(array('actualPlanet' => self::getPlanet(), 'currentPlanet' => self::getPlanet()
				));
	}

	/**
	 * Initialises the specification system.
	 */
	protected function initSpec() {
		Spec::storeData(self::getPlanet(), self::getUser());
	}
	
	/**
	 * Returns the actual planet
	 */
	public static function getPlanet() {
		return self::$planetObj;
	}


	/**
	 * Loads the database configuration and creates a new connection to the database.
	 */
	protected function initDB($bHost = null, $dbUser = null, $dbPassword = null, $dbName = null, $dbCharset = null) {
		// get configuration
		$dbHost = $dbUser = $dbPassword = $dbName = $dbCharset = '';
		require_once(WCF_DIR.'config.inc.php');
		$dbClass = 'LWMySQLDatabase';

		// create database connection
		require_once(LW_DIR.'lib/system/database/'.$dbClass.'.class.php');
		self::$dbObj = new $dbClass($dbHost, $dbUser, $dbPassword, $dbName, $dbCharset);
	}
	
	/**
	 * Returns a LWUtil Object for using in templates
	 * 
	 * @return	LWUtil
	 */
	public static function getLWUtil() {
		return new LWUtil();
	}
	
	/**
	 * Returns a BasicSpecUtil Object for using in templates
	 * 
	 * @return	BasicSpecUtil
	 */
	public static function getSpecUtil() {
		require_once(LW_DIR.'lib/util/BasicSpecUtil.class.php');
		return new BasicSpecUtil();
	}
	
	/**
	 * Return the actual alliance.
	 * 
	 * @param	bool	Editor
	 * @return	Alliance
	 */
	public static function getAlliance($editor = false) {
		if($editor) {
			if(self::$allianceEditor === null) {
				require_once(LW_DIR.'lib/data/user/alliance/AllianceEditor.class.php');
				self::$allianceEditor = Alliance::getByUserID(self::getUser()->userID, true);
			}
			return self::$allianceEditor;
		} else {
			if(self::$alliance === null)  {
				require_once(LW_DIR.'lib/data/user/alliance/Alliance.class.php');
				self::$alliance = Alliance::getByUserID(self::getUser()->userID, false);
			}
			return self::$alliance;
		}
	}
	
	/**
	 * Wrapper for static spec functions.
	 */
	public function __call($func, $args) {
		call_user_func_array(array('Spec', $func), $args);
	}
}
?>