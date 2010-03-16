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

/**
 * knock downs the interceptor missiles to free the space
 */
class InterceptorMissileKnockDownAction extends AbstractAction {
	protected $interceptorMissiles = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if(isset($_POST['interceptorMissiles'])) $this->interceptorMissiles = intval($_POST['interceptorMissiles']);
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		global $resource;

		parent::execute();

		// check permission
		if (!WCF::getUser()->userID) {
			message('Einloggen!');
		}

		$this->interceptorMissiles = min(max(0, $this->interceptorMissiles), LWCore::getPlanet()->interceptor_misil);

		$sql = "UPDATE ugml_planets
				SET interceptor_misil = interceptor_misil - ".$this->interceptorMissiles.",
					siloSlots = siloSlots - ".$this->interceptorMissiles."
				WHERE id = ".LWCore::getPlanet()->planetID;
		WCF::getDB()->registerShutdownUpdate($sql);

		$this->executed();

		header('Location: ../infos.php?gid=502');
		exit;
	}
}
?>