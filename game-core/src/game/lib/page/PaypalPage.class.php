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
require_once(LW_DIR.'lib/data/news/News.class.php');
require_once(LW_DIR.'lib/data/ovent/Ovent.class.php');

/**
 * This page shows just a template.
 * 
 * @author		Biggerskimo
 * @copyright	2011 Lost Worlds <http://lost-worlds.net>
 */
class PaypalPage extends AbstractPage
{
	public $templateName = 'paypal';
	
	public $success = 0;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters()
	{
		parent::readParameters();
		
		if(isset($_REQUEST['success']))
			$this->success = intval($_REQUEST['success']);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables()
	{
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'url' => 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'],
			'success' => $this->success
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show()
	{
		// check user
		if (!WCF::getUser()->userID)
		{
			require_once(WCF_DIR.'lib/system/exception/PermissionDeniedException.class.php');
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
?>