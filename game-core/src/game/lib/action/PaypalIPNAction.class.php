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

require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(LW_DIR.'../lib/payment/Paypal.php');

/**
 * Handles a instant payment notification request from paypal.
 * 
 * @author		Biggerskimo
 * @copyright	2011 Lost Worlds <http://lost-worlds.net>
 */
class PaypalIPNAction extends AbstractAction {
	public $tx = "";
	public $customStr = "";
	public $custom = array();
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters()
	{
		parent::readParameters();
		
		if(isset($_REQUEST['txn_id']))
		{
			$this->tx = StringUtil::trim($_REQUEST['txn_id']);
		}
		if(isset($_REQUEST['custom']))
		{
			$this->customStr = StringUtil::trim($_REQUEST['custom']);
			
			$a = explode('|', $this->customStr);
			foreach($a as $b)
			{
				list($key, $val) = explode('=', $b, 2);
				$this->custom[$key] = $val;
			}
		}
		var_dump($this);
	}
		

	/**
	 * @see Action::execute()
	 */
	public function execute()
	{
		parent::execute();
		
		// check ipn
		$paypal = new Paypal();
		$paypal->enableTestMode();
		$paypal->ipnLog = true;
		$paypal->validateIpn();
		
		if($paypal->validateIpn() && $paypal->ipnData['payment_status'] == 'Completed')
		{
			$ipnData = $paypal->ipnData;
			$payed = (float)$ipnData['mc_gross'] * 100;
			$item = StringUtil::trim($paypal->item_name);
			
			if($item == '30000 Dilizium' && $payed >= 900 && $ipnData['txn_type'] == 'subscr_payment')
				$diliPerCent = 30000 / 900;
			else if($item == '2500 Dilizium' && $payed >= 150)
				$diliPerCent = 2500 / 150;
			else if($item == '10000 Dilizium' && $payed >= 400)
				$diliPerCent = 10000 / 400;
			else if($item == '30000 Dilizium' && $payed >= 1000)
				$diliPerCent = 30000 / 1000;
			else
				$diliPerCent = 2500 / 150;
			
			$ipnData['lw_diliPercent'] = $diliPerCent;
			$dilizium = $diliPerCent * $payed;
			$ipnData['lw_dilizium'] = $dilizium;
				
			$sql = "INSERT INTO ugml_paypal
					(tx, `time`, userID, dilizium, ip, ipnData)
					VALUES
					('".$this->tx."', ".TIME_NOW.", ".intval($this->custom['userID']).",
					 ".intval($dilizium).", INET_ATON('".$_SERVER['REMOTE_ADDR']."'),
					 '".escapeString(print_r($ipnData, true))."')";
			WCF::getDB()->sendQuery($sql);
		}
		
		$this->executed();
		
		die('done');
	}
}
?>