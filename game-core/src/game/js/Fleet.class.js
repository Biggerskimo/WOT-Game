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
 * @copyright	2007-2008 Lost Worlds <http://lost-worlds.net>
 */
function Fleet(galaxy, system, planet) {
	this.startGalaxy = null;
	this.startSystem = null;
	this.startPlanet = null;
	
	this.galaxyDiff = false;
	this.systemDiff = false;
	this.planetDiff = false;
	
	this.shipTypes = null;
	
	this.time = null;
	this.timeRe = null;
	this.capacity = 0;
	
	this.init = function(galaxy, system, planet) {
		this.startGalaxy = galaxy;
		this.startSystem = system;
		this.startPlanet = planet;
	}
	
	/**
	 * Adds thousand seperators.
	 *
	 * @param	int		$number
	 * @return	string	formattedNumber
	 */ 
	this.formatNumber = function(number) {
		var prefix = number < 0 ? '-' : '';
		var realNumber = new String(parseInt(number = Math.abs(+number || 0).toFixed(0)));
		var firstDigitGroupDigitCount = (firstDigitGroupDigitCount = realNumber.length) > 3 ? firstDigitGroupDigitCount % 3 : 0;
		    
		return prefix + (firstDigitGroupDigitCount ? realNumber.substr(0, firstDigitGroupDigitCount) + '.' : '') + realNumber.substr(firstDigitGroupDigitCount).replace(/(\d{3})(?=\d)/g, '$1.');
	}

	/**
	 * Sets a ship input field to his maximum.
	 *
	 * @param	int		$specID
	 */
	this.maxShip = function(specID) {
		document.getElementsByName('ship' + specID)[0].value = this.shipTypes[specID].count;
	}

	/**
	 * Sets all ship input fields to their maximum.
	 */
	this.maxShips = function() {
		var shipTypes = this.shipTypes;
	
		for(specID in shipTypes) {
			this.maxShip(specID);
		}
		
		// update link
		link = document.getElementById('generalShipCountsLink');
		link.setAttribute('href', 'javascript:fleet.noShips(this)');
		link.innerHTML = language['wot.fleet.start.ships.min'];
	}

	/**
	 * Resets all ship input fields.
	 *
	 * @todo replace with the normal form reset specification
	 */
	this.noShips = function() {
		var shipTypes = this.shipTypes;
	
		for(specID in shipTypes) {
			document.getElementsByName('ship' + specID)[0].value = '0';
		}
		
		// update link
		link = document.getElementById('generalShipCountsLink');
		link.setAttribute('href', 'javascript:fleet.maxShips(this)');
		link.innerHTML = language['wot.fleet.start.ships.max'];
	}
	
	/**
	 * Shows the fleet details.
	 *
	 * @param	int		fleetID
	 */
	this.showDetails = function(fleetID) {
		detailsRow = document.getElementById('fleetDetails'+fleetID);
		detailsImg = document.getElementById('fleetDetails'+fleetID+'Img');
				
		if(detailsRow.style.display == 'none') {
			detailsRow.style.display = 'table-row';
			detailsImg.src = RELATIVE_WCF_DIR+'icon/minusS.png';
		}
		else {
			detailsRow.style.display = 'none';
			detailsImg.src = RELATIVE_WCF_DIR+'icon/plusS.png';
		}
	}
		
	/**
	 * Calculates the distance.
	 *
	 * @return	int		$distance
	 */
	this.getDistance = function() {
		// galaxy
		var galaxyDiff = Math.abs(document.getElementsByName('galaxy')[0].value - this.startGalaxy);

		if(galaxyDiff) {
			this.galaxyDiff = galaxyDiff;
			this.systemDiff = this.planetDiff = false;

			return galaxyDiff * 20000;
		}

		// system
		var systemDiff = Math.abs(document.getElementsByName('system')[0].value - this.startSystem);

		if(systemDiff) {
			this.systemDiff = systemDiff;
			this.galaxyDiff = this.planetDiff = 0;

			return systemDiff * 95 + 2700;
		}

		// planet
		var planetDiff = Math.abs(document.getElementsByName('planet')[0].value - this.startPlanet);

		if(planetDiff) {
			this.planetDiff = planetDiff;
			this.galaxyDiff = this.systemDiff = 0;

			return planetDiff * 5 + 1000;
		}

		// planet -> moon/debris etc.
		this.galaxyDiff = this.systemDiff = this.planetDiff = 0;

		return 5;
	}

	/**
	 * Calculates the time needed for a flight.
	 *
	 * @return	int		$duration
	 */
	this.getDuration = function() {
		var speedPercent = document.getElementsByName('speed')[0].value;
		var distance = this.getDistance();
		
		var duration = Math.round(((3500 / speedPercent * Math.sqrt(distance * 10 / maxSpeed) + 10) / speedFactor));

		return duration;
	}

	/**
	 * Calculates the deuterium needed for the complete flight.
	 *
	 * @return	int		consumption
	 */
	this.getConsumption = function() {
		var consumption = 0;
		var distance = this.getDistance();
		var speedPercent = document.getElementsByName('speed')[0].value;
		
		var shipTypes = this.shipTypes;
		
		for(specID in shipTypes) {
			var spec = shipTypes[specID];

			var spd = 35000 / (this.getDuration() * speedFactor - 10) * Math.sqrt(distance * 10 / spec.speed);

			consumption += spec.count * spec.consumption * distance / 35000 * ((spd / 10) + 1) * ((spd / 10) + 1);
		}

		return Math.round(consumption) + 1;
	}

	/**
	 * Executes the basic functions for the coordinates fleet page.
	 */
	this.shortInfo = function() {
		// distance
		var distance = this.getDistance();
		document.getElementById('distance').innerHTML = this.formatNumber(distance);

		// duration
		var duration = this.getDuration();
		if(this.time == null) this.time = new Time('duration', duration, false, false);
		else this.time.setTime(duration);
		
		if(this.timeRe == null) this.timeRe = new Time('durationRe', duration<<1, false, false);
		else this.timeRe.setTime(duration<<1);

		// consumption
		var consumption = this.getConsumption();
		document.getElementById('consumption').innerHTML = this.formatNumber(consumption);

		// storage
		var storage = this.getStorage();
		if(storage <= 0) storage = '<span style="color: red;">' + this.formatNumber(storage) + '</span>';
		document.getElementById('storage').innerHTML = this.formatNumber(storage);
	}

	/**
	 * Sets a resource input field to his maximum.
	 *
	 * @param	string	resource name
	 */
	this.maxResource = function(resourceName) {
		eval('var onPlanet = '+resourceName+';');

		var selected = parseInt(document.getElementsByName(resourceName)[0].value);
		if(isNaN(selected)) selected = 0;

		var selectedResource1 = parseInt(document.getElementsByName('metal')[0].value);
		if(isNaN(selectedResource1)) selectedResource1 = 0;

		var selectedResource2 = parseInt(document.getElementsByName('crystal')[0].value);
		if(isNaN(selectedResource2)) selectedResource2 = 0;

		var selectedResource3 = parseInt(document.getElementsByName('deuterium')[0].value);
		if(isNaN(selectedResource3)) selectedResource3 = 0;

		var selectedResources = selectedResource1 + selectedResource2 + selectedResource3 - selected;
		
		var freeCapacity = capacity - selectedResources;

		var addResource = Math.max(0, Math.min(onPlanet, freeCapacity));

		document.getElementsByName(resourceName)[0].value = addResource;

		this.calcFreeCapacity();
	}

	/**
	 * Resets the resource fields.
	 */
	this.noResources = function() {
		document.getElementsByName('metal')[0].value = 0;
		document.getElementsByName('crystal')[0].value = 0;
		document.getElementsByName('deuterium')[0].value = 0;
		
		this.calcFreeCapacity();
	}

	/**
	 * Sets all resource input fields to the maximum.
	 */
	this.maxResources = function() {
		this.noResources();

		this.maxResource('metal');
		this.maxResource('crystal');
		this.maxResource('deuterium');
	}

	/**
	 * Calculates the free capacity.
	 */
	this.calcFreeCapacity = function() {
		var selectedResource1 = parseInt(document.getElementsByName('metal')[0].value);
		if(isNaN(selectedResource1)) selectedResource1 = 0;

		var selectedResource2 = parseInt(document.getElementsByName('crystal')[0].value);
		if(isNaN(selectedResource2)) selectedResource2 = 0;

		var selectedResource3 = parseInt(document.getElementsByName('deuterium')[0].value);
		if(isNaN(selectedResource3)) selectedResource3 = 0;

		var selectedResources = selectedResource1 + selectedResource2 + selectedResource3;

		var freeCapacity = capacity - selectedResources;

		document.getElementById('remainingResources').innerHTML = this.formatNumber(freeCapacity);
		
		if(freeCapacity >= 0) {
			document.getElementById('remainingResources').className = 'positive';
		}
		else {
			document.getElementById('remainingResources').className = 'negative';
		}
	}

	/**
	 * Updates the target on the coordinates page.
	 *
	 * @param	int		$galaxy
	 * @param	int		$system
	 * @param	int		$planet
	 * @param	int		$planetType
	 */
	this.setTarget = function(galaxy, system, planet, planetType) {
		document.getElementsByName('galaxy')[0].value = galaxy;
		document.getElementsByName('system')[0].value = system;
		document.getElementsByName('planet')[0].value = planet;
		document.getElementsByName('planetType')[0].value = planetType;

		this.shortInfo();
	}
	
	/**
	 * Calculates the capacity.
	 *
	 * @return	int	capacity
	 */
	this.calculateCapacity = function() {
		if(this.capacity > 0) return this.capacity;
		
			
		var shipTypes = this.shipTypes;
		var capacity = 0;
	
		for(specID in shipTypes) {
			capacity += shipTypes[specID].capacity * shipTypes[specID].count;
		}
		
		this.capacity = capacity;
		
		return capacity;
	}

	/**
	 * Calculates the storage of the fleet
	 */
	this.getStorage = function() {
		var storage = this.calculateCapacity() - this.getConsumption();

		return storage;
	}
	
	this.init(galaxy, system, planet);
}