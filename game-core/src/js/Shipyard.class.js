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
function Shipyard() {
	this.lastShipStart = null;
	this.time = null;

	this.createList = function(startShip) {
		var option, shipType;

		// delete old
		while (document.Atr.auftr.length > 0) {
      		document.Atr.auftr.options[(document.Atr.auftr.length - 1)] = null;
   		}

   		// insert new
   		for(var shipTypeID in shipTypeArray) {
   			shipType = shipTypeArray[shipTypeID];

   			if(shipType == null) continue;

   			if(!shipTypeName) var shipTypeName = shipType['name'];

   			option = new Option();
   			option.text = shipType['count'] + ' ' + shipType['name'];

   			document.Atr.auftr.options[document.Atr.auftr.length] = option;
   		}

   		document.getElementById('shipName').innerHTML = shipTypeName;
	}

	this.startShip = function(doneTime) {
		var date = new Date();
   		this.lastShipStart = Math.round(date.getTime() / 1000);

   		for(var shipTypeID in shipTypeArray) {
   			if(shipTypeArray[shipTypeID] == null) continue;

   			break;
   		}

   		var time = shipTypeArray[shipTypeID]['time'] - doneTime;

   		window.setTimeout('shipyard.finalizeShip()', (time * 1000));

   		// timer
   		if(shipTime == null) shipTime = new Time('shipTime', time, true, false);
   		else shipTime.setTime(time);
	}

	this.finalizeShip = function() {
		for(var shipTypeID in shipTypeArray) {
			if(shipTypeArray[shipTypeID] == null) continue;

			var count = shipTypeArray[shipTypeID]['count'];

			if(count == 1) shipTypeArray[shipTypeID] = null;
			else shipTypeArray[shipTypeID]['count'] -= 1;

			break;
		}

		this.createList(false);

		// finished all ships
		var shipTypeCount = 0;
		for(var shipTypeID in shipTypeArray) {
			if(shipTypeArray[shipTypeID] == null) continue;

			shipTypeCount += 1;

			break;
		}
		if(shipTypeCount) this.startShip(0);
	}

	this.createList(true);
	this.startShip(doneTime);
}

var shipyard = new Shipyard();