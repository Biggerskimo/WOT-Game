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
require_once(LW_DIR.'lib/util/LWUtil.class.php');

/**
 * Shows a form that allows the users to configure their overview.
 * 
 * @author		Biggerskimo
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
class OverviewOptionsForm extends AbstractForm {
	public $templateName = 'overviewOptions';
	
	public $oventTypes = array();
	public $hideOventTypes = array();
	public $dontAskOnHiding = false;
	public $hideInformation = false;
	public $hideColonies = false;
		
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
				
		foreach($this->oventTypes as $oventTypeID => $oventTypeData) {
			if(isset($_POST['hideOventType'.$oventTypeID]) && $_POST['hideOventType'.$oventTypeID] == 'on') {
				$this->hideOventTypes[$oventTypeID] = true;
			}
			else {
				$this->hideOventTypes[$oventTypeID] = false;
			}
		}
		
		if(isset($_POST['dontAskOnHiding']) && $_POST['dontAskOnHiding'] == 'on') {
			$this->dontAskOnHiding = true;
		}
		else {
			$this->dontAskOnHiding = false;
		}
		
		if(isset($_POST['hideInformation']) && $_POST['hideInformation'] == 'on') {
			$this->hideInformation = true;
		}
		else {
			$this->hideInformation = false;
		}
		
		if(isset($_POST['hideColonies']) && $_POST['hideColonies'] == 'on') {
			$this->hideColonies = true;
		}
		else {
			$this->hideColonies = false;
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		WCF::getCache()->addResource('oventTypes-'.PACKAGE_ID, WCF_DIR.'cache/cache.oventTypes-'.PACKAGE_ID.'.php', LW_DIR.'lib/system/cache/CacheBuilderOventTypes.class.php');
		$this->oventTypes = WCF::getCache()->get('oventTypes-'.PACKAGE_ID);
		
		parent::readData();
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array('oventTypes' => $this->oventTypes));
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		foreach($this->hideOventTypes as $oventTypeID => $hide) {
			WCF::getUser()->setSetting('hideOventType'.$oventTypeID, $hide);
		}
		WCF::getUser()->setSetting('dontAskOnOventHiding', $this->dontAskOnHiding);
		WCF::getUser()->setSetting('hideInformation', $this->hideInformation);
		WCF::getUser()->setSetting('hideColonies', $this->hideColonies);
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check user
		if (!WCF::getUser()->userID) message('Zutritt nicht erlaubt!');
		
		parent::show();
	}
}
?>