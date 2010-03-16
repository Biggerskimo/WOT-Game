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
function Fleet() {
	this.galaxyDiff = false;
	this.systemDiff = false;
	this.planetDiff = false;
	this.time = null;
	this.storage = null;

	this.maxShip = function(shipID) {
		var shipCount = document.getElementsByName('maxShip' + shipID)[0].value;

		document.getElementsByName('ship' + shipID)[0].value = shipCount;
	}

	this.maxShips = function() {
		for(shipID = 200; shipID < 400; shipID++) {
			if(!document.getElementsByName('ship' + shipID)[0]) continue;

			this.maxShip(shipID);
		}
	}

	this.noShips = function() {
		for(shipID = 200; shipID < 400; shipID++) {
			if(!document.getElementsByName('ship' + shipID)[0]) continue;

			document.getElementsByName('ship' + shipID)[0].value = '0';
		}
	}

	this.getDistance = function() {
		// galaxy
		var galaxy = document.getElementsByName('galaxy')[0].value;
		var thisGalaxy = document.getElementsByName('thisgalaxy')[0].value;

		var galaxyDiff = Math.abs(galaxy - thisGalaxy);

		if(galaxyDiff) {
			this.galaxyDiff = galaxyDiff;
			this.systemDiff = this.planetDiff = false;

			return galaxyDiff * 20000;
		}

		// system
		var system = document.getElementsByName('system')[0].value;
		var thisSystem = document.getElementsByName('thissystem')[0].value;

		var systemDiff = Math.abs(system - thisSystem);

		if(systemDiff) {
			this.systemDiff = systemDiff;
			this.galaxyDiff = this.planetDiff = 0;

			return systemDiff * 95 + 2700;
		}

		// planet
		var planet = document.getElementsByName('planet')[0].value;
		var thisPlanet = document.getElementsByName('thisplanet')[0].value;

		var planetDiff = Math.abs(planet - thisPlanet);

		if(planetDiff) {
			this.planetDiff = planetDiff;
			this.galaxyDiff = this.systemDiff = 0;

			return planetDiff * 5 + 1000;
		}

		// planet -> moon/debris etc.
		this.galaxyDiff = this.systemDiff = this.planetDiff = 0;

		return 5;
	}

	this.getDuration = function() {
		var speedFactor = document.getElementsByName('speedfactor')[0].value;
		var maxSpeed = document.getElementsByName('speedallsmin')[0].value;
		var speedPercent = document.getElementsByName('speed')[0].value;
		var distance = this.getDistance();

		var duration = Math.round(((35000 / speedPercent * Math.sqrt(distance * 10 / maxSpeed) + 10) / speedFactor));

		return duration;
	}

	this.getConsumption = function() {
		var consumption = 0;
		var basicConsumption = 0;
		var distance = this.getDistance();
		var speedPercent = document.getElementsByName('speed')[0].value;
		var maxSpeed = document.getElementsByName('speedallsmin')[0].value;
		var speedFactor = document.getElementsByName('speedfactor')[0].value;

		for(shipID = 200; shipID < 400; shipID++) {
			if(!document.getElementsByName('ship' + shipID)[0]) continue;

			var shipCount = document.getElementsByName('ship' + shipID)[0].value;
			var shipConsumption = document.getElementsByName('consumption' + shipID)[0].value;
			var shipSpeed = document.getElementsByName('speed' + shipID)[0].value;

			var spd = 35000 / (this.getDuration() * speedFactor - 10) * Math.sqrt(distance * 10 / shipSpeed);

			consumption += shipCount * shipConsumption * distance / 35000 * ((spd / 10) + 1) * ((spd / 10) + 1);
		}

		return Math.round(consumption) + 1;
	}

	this.shortInfo = function() {
		// distance
		var distance = this.getDistance();
		document.getElementById('distance').innerHTML = distance;

		// duration
		var duration = this.getDuration();
		if(this.time == null) this.time = new Time('duration', duration, false, false);
		else this.time.setTime(duration);

		// consumption
		var consumption = this.getConsumption();
		document.getElementById('consumption').innerHTML = consumption;

		// storage
		var storage = this.getStorage();
		if(storage <= 0) storage = '<span style="color: red;">' + storage + '</span>';
		document.getElementById('storage').innerHTML = storage;
	}


	this.maxResource = function(resourceID) {
		var onPlanet = document.getElementsByName('thisresource' + resourceID)[0].value;

		var selected = parseInt(document.getElementsByName('resource' + resourceID)[0].value);
		if(isNaN(selected)) selected = 0;

		var selectedResource1 = parseInt(document.getElementsByName('resource1')[0].value);
		if(isNaN(selectedResource1)) selectedResource1 = 0;

		var selectedResource2 = parseInt(document.getElementsByName('resource2')[0].value);
		if(isNaN(selectedResource2)) selectedResource2 = 0;

		var selectedResource3 = parseInt(document.getElementsByName('resource3')[0].value);
		if(isNaN(selectedResource3)) selectedResource3 = 0;

		var selectedResources = selectedResource1 + selectedResource2 + selectedResource3;

		var capacity = parseInt(document.getElementsByName('capacity')[0].value);

		var consumption = parseInt(document.getElementsByName('consumption_php')[0].value);

		var freeCapacity = capacity - selectedResources - consumption;

		var addResource = Math.max(0, Math.min(onPlanet, freeCapacity));

		document.getElementsByName('resource' + resourceID)[0].value = addResource;

		this.calcFreeCapacity();
	}

	this.noResources = function() {
		document.getElementsByName('resource1')[0].value = 0;
		document.getElementsByName('resource2')[0].value = 0;
		document.getElementsByName('resource3')[0].value = 0;
	}

	this.maxResources = function() {
		this.noResources();

		this.maxResource(1);
		this.maxResource(2);
		this.maxResource(3);
	}

	this.calcFreeCapacity = function() {
		var selectedResource1 = parseInt(document.getElementsByName('resource1')[0].value);
		if(isNaN(selectedResource1)) selectedResource1 = 0;

		var selectedResource2 = parseInt(document.getElementsByName('resource2')[0].value);
		if(isNaN(selectedResource2)) selectedResource2 = 0;

		var selectedResource3 = parseInt(document.getElementsByName('resource3')[0].value);
		if(isNaN(selectedResource3)) selectedResource3 = 0;

		var selectedResources = selectedResource1 + selectedResource2 + selectedResource3;

		var capacity = parseInt(document.getElementsByName('capacity')[0].value);

		var consumption = parseInt(document.getElementsByName('consumption_php')[0].value);

		var freeCapacity = capacity - selectedResources - consumption;

		document.getElementById('remainingresources').innerHTML = freeCapacity;
	}

	this.setTarget = function(galaxy, system, planet, planettype) {
		document.getElementsByName('galaxy')[0].value = galaxy;
		document.getElementsByName('system')[0].value = system;
		document.getElementsByName('planet')[0].value = planet;
		document.getElementsByName('planettype')[0].value = planettype;

		this.shortInfo();
	}

	this.getStorage = function() {
		var capacity = 0;

		for(shipID = 200; shipID < 400; shipID++) {
			if(!document.getElementsByName('ship' + shipID)[0]) continue;

			capacity += document.getElementsByName('ship' + shipID)[0].value * document.getElementsByName('capacity' + shipID)[0].value;
		}

		var storage = capacity - this.getConsumption();

		return storage;
	}
}

var fleet = new Fleet();