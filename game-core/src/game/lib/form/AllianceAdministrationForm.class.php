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

require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(LW_DIR.'lib/data/user/alliance/AllianceEditor.class.php');
require_once(LW_DIR.'lib/data/user/LWUser.class.php');


/**
 * Shows the start page of the alliance administration.
 * 
 * @author		Biggerskimo
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
class AllianceAdministrationForm extends AbstractForm {
	public $templateName = 'allianceAdministration';
	
	protected $allianceID = 0;
	protected $alliance = null;
	
	protected $externalText = null;
	protected $internalText = null;
	protected $applicationTemplate = null;
	
	protected $allianceName = null;
	protected $allianceTag = null;
	
	protected $webAdress = null;
	protected $allianceImage = null;
	protected $founderName = null;

	/**
	 * Creates a new AllianceAdministrationForm object.
	 */
	public function __construct() {
		if(!defined('DO_NOT_PARSE_ALLIANCE_TEXTS')) define('DO_NOT_PARSE_ALLIANCE_TEXTS', true);
		
		parent::__construct();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if(isset($_GET['allianceID'])) $this->allianceID = intval($_GET['allianceID']);
		if(!isset($_GET['allianceID']) || $this->allianceID != WCF::getUser()->ally_id) {
			require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		// texts
		if(isset($_POST['externalText'])) $this->externalText = StringUtil::trim($_POST['externalText']);
		if(isset($_POST['internalText'])) $this->internalText = StringUtil::trim($_POST['internalText']);
		if(isset($_POST['applicationTemplate'])) $this->applicationTemplate = StringUtil::trim($_POST['applicationTemplate']);

		// name
		if(isset($_POST['name'])) $this->allianceName = StringUtil::trim($_POST['name']);
		if(isset($_POST['tag'])) $this->allianceTag = StringUtil::trim($_POST['tag']);
		
		// settings
		if(isset($_POST['homepage'])) $this->webAddress = StringUtil::trim($_POST['homepage']);
		if(isset($_POST['image'])) $this->allianceImage = StringUtil::trim($_POST['image']);
		if(isset($_POST['founder'])) $this->founderName = StringUtil::trim($_POST['founder']);
		
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {		
		$this->alliance = new AllianceEditor($this->allianceID);
		
		
		parent::readData();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		$this->alliance->updateTexts($this->externalText, $this->internalText, $this->applicationTemplate);
		
		$this->alliance->setName($this->allianceName, $this->allianceTag);
		
		$this->alliance->changeLinks($this->allianceImage, $this->webAddress);
		$this->alliance->changeLeader(null, $this->founderName);
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
				'alliance' => $this->alliance,
				'leader' => new LWUser($this->alliance->ally_owner)
				));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) {
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}

		$_SERVER['HTTP_ACCEPT'] = str_replace('platzhalter', 'application/xhtml+xml', $_SERVER['HTTP_ACCEPT']);
		parent::show();
		//echo_foot();
	}
}
?>