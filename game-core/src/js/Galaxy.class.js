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
/**
 * @author		Biggerskimo
 * @copyright	2007 Lost Worlds <http://lost-worlds.net>
 */
function Galaxy(galaxyN, system, probesCount) {
	this.galaxyN = galaxyN;
	this.system = system;
	this.planet = null;
	this.probesCount = probesCount;
	this.ajaxRequest = null;
	this.xmlHttpRequest = null;

	this.checkAjaxRequest = function() {
		if(this.ajaxRequest != null) return;

		this.ajaxRequest = new AjaxRequest();
	}

	this.sendProbes = function(planet, planetType) {
		this.checkAjaxRequest();

		this.planet = planet;

		this.ajaxRequest.openGet('game/index.php?action=GalaxyEspionage&galaxy='+this.galaxyN+'&system='+this.system+'&planet='+planet+'&planetKind='+planetType, this.ajaxResponseWrapper);
	}

	this.ajaxResponseWrapper = function() {
		galaxy.ajaxResponseHandler();
	}

	this.ajaxResponseHandler = function() {
		if (this.ajaxRequest.xmlHttpRequest.readyState != 4) return;

		this.xmlHttpRequest = this.ajaxRequest.xmlHttpRequest;

		var text = this.xmlHttpRequest.responseText;

		if(text == '') {
			var className = 'success';
			text = 'done';
		} else var className = 'error';

		this.addMessage(text, className);
	}

	this.addMessage = function(text, className) {
		document.getElementById('fleetstatusrow').style.display = '';
		var table = document.getElementById('fleetstatustable');

		if(table.rows.length >= 3) table.deleteRow(2);

		var row = table.insertRow(0);

		var messageRow = document.createElement('td');
		var koord = this.galaxyN + ':' + this.system + ':' + this.planet;
		var message = document.createTextNode('Sende ' + this.probesCount + ' Spionagesonden zu ' + koord + '...');
		messageRow.appendChild(message);

		var statusRow = document.createElement('td');
		var statusSpan = document.createElement('span');
		var statusText = document.createTextNode(text);
		statusSpan.appendChild(statusText);
		statusSpan.className = className;
		statusRow.appendChild(statusSpan);

		row.appendChild(messageRow);
		row.appendChild(statusRow);
	}

}

var galaxy = new Galaxy();