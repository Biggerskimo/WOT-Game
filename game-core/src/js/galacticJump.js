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
function setTarget(galaxy, solarsystem, planet, planettype) {
  document.getElementsByName('galaxy')[0].value = galaxy;
  document.getElementsByName('system')[0].value = solarsystem;
  document.getElementsByName('planet')[0].value = planet;
  document.getElementsByName('planettype')[0].value = planettype;
}

function maxspeed() {
  return(document.getElementsByName("speedallsmin")[0].value);
}

function distance() {
  var dist;

  var pageNo = document.getElementsByName('pageNo')[0].value;

  if(pageNo == 1) {

	  var thisGalaxy;
	  var thisSystem;
	  var thisPlanet;

	  var targetGalaxy;
	  var targetSystem;
	  var targetPlanet;

	  thisGalaxy = document.getElementsByName("thisgalaxy")[0].value;
	  thisSystem = document.getElementsByName("thissystem")[0].value;
	  thisPlanet = document.getElementsByName("thisplanet")[0].value;

	  targetGalaxy = document.getElementsByName("galaxy")[0].value;
	  targetSystem = document.getElementsByName("system")[0].value;
	  targetPlanet = document.getElementsByName("planet")[0].value;

	  dist = 0;
	  if ((targetGalaxy - thisGalaxy) != 0) {
	    dist = Math.abs(targetGalaxy - thisGalaxy) * 20000;
	  } else if ((targetSystem - thisSystem) != 0) {
	    dist = Math.abs(targetSystem - thisSystem) * 5 * 19 + 2700;
	  } else if ((targetPlanet - thisPlanet) != 0) {
	    dist = Math.abs(targetPlanet - thisPlanet) * 5 + 1000;
	  } else {
	    dist = 5;
	  }
  } else dist = document.getElementsByName('dist')[0].value;

  return dist;
}

function duration() {
  var pageNo = document.getElementsByName('pageNo')[0].value;

  var ress = parseInt(0);
   if(pageNo == 2) {
	  var metal = document.getElementsByName('resource1')[0].value;
	  if(!isNaN(metal) && metal > 0) ress += parseInt(metal);

	  var crystal = document.getElementsByName('resource2')[0].value;
	  if(!isNaN(crystal) && crystal > 0) ress += parseInt(crystal);

	  var deuterium = document.getElementsByName('resource3')[0].value;
	  if(!isNaN(deuterium) && deuterium > 0) ress += parseInt(deuterium);
  }
  var dist = distance();
  var thisGJ = document.getElementsByName("thisGJ")[0].value;
  var duration = ((dist / 100000) * Math.pow(ress, 0.05) + 1) * Math.pow(0.95, thisGJ) * 3600;

  return duration;
}


function consumption() {
  var pageNo = document.getElementsByName('pageNo')[0].value;

  var dist = distance();
  var thisGJ = document.getElementsByName("thisGJ")[0].value;
  var ress = parseInt(0);
  if(pageNo == 2) {
	  var metal = document.getElementsByName('resource1')[0].value;
	  if(!isNaN(metal) && metal > 0) ress += parseInt(metal);

	  var crystal = document.getElementsByName('resource2')[0].value;
	  if(!isNaN(crystal) && crystal > 0) ress += parseInt(crystal);

	  var deuterium = document.getElementsByName('resource3')[0].value;
	  if(!isNaN(deuterium) && deuterium > 0) ress += parseInt(deuterium);
  }
  var consumption = Math.ceil((Math.pow(1.000007, ress) * dist / 10000 + ress * 0.1) * Math.pow(0.99, thisGJ));


  return consumption;
 }

function storage() {
  var storage = 0;

  for (i = 200; i < 300; i++) {
    if (document.getElementsByName("ship" + i)[0]) {
      if ((document.getElementsByName("ship" + i)[0].value * 1) >= 1) {
	storage
	  += document.getElementsByName("ship" + i)[0].value
	  *  document.getElementsByName("capacity" + i)[0].value
      }
    }
  }

  storage -= consumption();
  return(storage);
}

function storage2() {
  var storage = document.getElementsByName('storage')[0].value;

  return(storage);
}

function shortInfo() {
  document.getElementById("distance").innerHTML = distance();
  var seconds = duration();
  var hours = Math.floor(seconds / 3600);
  seconds -= hours * 3600;

  var minutes = Math.floor(seconds / 60);
  seconds -= minutes * 60;

  if (minutes < 10) minutes = "0" + minutes;
  seconds = Math.ceil(seconds);
  if (seconds < 10) seconds = "0" + seconds;

  document.getElementById("duration").innerHTML = hours + ":" + minutes + ":" + seconds + " h";
  var cons = consumption();
  document.getElementById("consumption").innerHTML = cons;
}


function setResource(id, val) {
  if (document.getElementsByName(id)[0]) {
    document.getElementsByName("resource" + id)[0].value = val;
  }
}

function maxResource(id) {
  var thisresource = document.getElementsByName("thisresource" + id)[0].value;
  var thisresourcechosen = document.getElementsByName("resource" + id)[0].value;

  var storCap = storage2();

  var metalToTransport = document.getElementsByName("resource1")[0].value;
  var crystalToTransport = document.getElementsByName("resource2")[0].value;
  var deuteriumToTransport = document.getElementsByName("resource3")[0].value;

  var freeCapacity = Math.min(Math.max(storCap - metalToTransport - crystalToTransport - deuteriumToTransport + thisresourcechosen * 1, 0), thisresource);

  if (document.getElementsByName("resource" + id)[0]) {
    document.getElementsByName("resource" + id)[0].value = freeCapacity;
  }
  calculateTransportCapacity();
}

function maxResources() {
  var id;
  var storCap = storage2();
  var metalToTransport = document.getElementsByName("thisresource1")[0].value;
  var crystalToTransport = document.getElementsByName("thisresource2")[0].value;
  var deuteriumToTransport = document.getElementsByName("thisresource3")[0].value;

  var freeCapacity = storCap - metalToTransport - crystalToTransport - deuteriumToTransport;
  if (freeCapacity < 0) {
    metalToTransport = Math.min(metalToTransport, storCap);
    crystalToTransport = Math.min(crystalToTransport, storCap - metalToTransport);
    deuteriumToTransport = Math.min(deuteriumToTransport, storCap - metalToTransport - crystalToTransport);
  }
  document.getElementsByName("resource1")[0].value = Math.max(metalToTransport, 0);
  document.getElementsByName("resource2")[0].value = Math.max(crystalToTransport, 0);
  document.getElementsByName("resource3")[0].value = Math.max(deuteriumToTransport, 0);
}

function noResources() {
  document.getElementsByName("resource1")[0].value = 0;
  document.getElementsByName("resource2")[0].value = 0;
  document.getElementsByName("resource3")[0].value = 0;
  calculateTransportCapacity();
}
