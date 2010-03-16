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
 * @author		Biggerskimo, Blobby
 * @copyright	2008-2009 Lost Worlds <http://lost-worlds.net>
 */
function Simulator() {
	this.nfsSlotNo = '01';
	this.shipData = new Object();
	this.varDelimiter = ';';
	this.keyValueDelimiter = ',';
	this.language = new Object();
	
	this.init = function() {
	}
	
	/**
	 * Updates the the displayed data
	 */
	this.changeNfsSlotNo = function() {
		this.getShipData();
	
		this.nfsSlotNo = document.getElementById('nfsSlotNo').value;
		if(this.nfsSlotNo.length == 1) this.nfsSlotNo = '0' + this.nfsSlotNo;
		
		if(this.nfsSlotNo < 1) this.nfsSlotNo = document.getElementById('nfsSlotNo').value = 1;
		else if(this.nfsSlotNo > 20) this.nfsSlotNo = document.getElementById('nfsSlotNo').value = 20;
		
		this.updateShipData();
	}
	
	/**
	 * Counts the nfs slot no down
	 */
	this.nfsSlotNoDown = function() {
		var tmpNfsSlotNo = document.getElementById('nfsSlotNo').value;
		document.getElementById('nfsSlotNo').value = (parseInt(tmpNfsSlotNo) - 1);
		
		this.changeNfsSlotNo();
	}
	
	/**
	 * Counts the nfs slot no up
	 */
	this.nfsSlotNoUp = function() {
		var tmpNfsSlotNo = document.getElementById('nfsSlotNo').value;
		document.getElementById('nfsSlotNo').value = (parseInt(tmpNfsSlotNo) + 1);
		
		this.changeNfsSlotNo();
	}
	
	/**
	 * Reads the displayed shipdata and saves it
	 */
	this.getShipData = function() {
		// fleet
		for(shipTypeID = 200; shipTypeID < 500; shipTypeID++) {
			if(document.getElementsByName('shipDataAttacker' + shipTypeID)[0] && (document.getElementsByName('shipDataAttacker' + shipTypeID)[0].value > 0 || this.shipData['shipData' + this.nfsSlotNo + 'Attacker' + shipTypeID])) {
				this.shipData['shipData' + this.nfsSlotNo + 'Attacker' + shipTypeID] = document.getElementsByName('shipDataAttacker' + shipTypeID)[0].value;
			}
		
			if(document.getElementsByName('shipDataDefender' + shipTypeID)[0] && (document.getElementsByName('shipDataDefender' + shipTypeID)[0].value > 0 || this.shipData['shipData' + this.nfsSlotNo + 'Defender' + shipTypeID])) {
				this.shipData['shipData' + this.nfsSlotNo + 'Defender' + shipTypeID] = document.getElementsByName('shipDataDefender' + shipTypeID)[0].value;
			}
		}
		
		// techs
		for(techTypeID = 109; techTypeID <= 111; techTypeID++) {
			if(document.getElementsByName('shipDataAttackerTech' + techTypeID)[0] && (document.getElementsByName('shipDataAttackerTech' + techTypeID)[0].value > 0 || this.shipData['shipData' + this.nfsSlotNo + 'AttackerTech' + techTypeID])) {
				this.shipData['shipData' + this.nfsSlotNo + 'AttackerTech' + techTypeID] = document.getElementsByName('shipDataAttackerTech' + techTypeID)[0].value;
			}
			
			if(document.getElementsByName('shipDataDefenderTech' + techTypeID)[0] && (document.getElementsByName('shipDataDefenderTech' + techTypeID)[0].value > 0 || this.shipData['shipData' + this.nfsSlotNo + 'DefenderTech' + techTypeID])) {
				this.shipData['shipData' + this.nfsSlotNo + 'DefenderTech' + techTypeID] = document.getElementsByName('shipDataDefenderTech' + techTypeID)[0].value;
			}
		}
	}
	
	/**
	 * Reads the data from the saved data and inserts it into the input-fields
	 */
	this.updateShipData = function() {
		// fleet
		for(shipTypeID = 200; shipTypeID < 500; shipTypeID++) {
			if(this.shipData['shipData' + this.nfsSlotNo + 'Attacker' + shipTypeID]) {
				document.getElementsByName('shipDataAttacker' + shipTypeID)[0].value = this.shipData['shipData' + this.nfsSlotNo + 'Attacker' + shipTypeID];
			} else if(document.getElementsByName('shipDataAttacker' + shipTypeID)[0]) {
				document.getElementsByName('shipDataAttacker' + shipTypeID)[0].value = '';
			}
			
			if(this.shipData['shipData' + this.nfsSlotNo + 'Defender' + shipTypeID]) {
				document.getElementsByName('shipDataDefender' + shipTypeID)[0].value = this.shipData['shipData' + this.nfsSlotNo + 'Defender' + shipTypeID];
			} else if(document.getElementsByName('shipDataDefender' + shipTypeID)[0]) {
				document.getElementsByName('shipDataDefender' + shipTypeID)[0].value = '';
			}
		}
		
		// techs
		for(techTypeID = 109; techTypeID <= 111; techTypeID++) {
			if(this.shipData['shipData' + this.nfsSlotNo + 'AttackerTech' + techTypeID]) {
				document.getElementsByName('shipDataAttackerTech' + techTypeID)[0].value = this.shipData['shipData' + this.nfsSlotNo + 'AttackerTech' + techTypeID];
			} else if(document.getElementsByName('shipDataAttackerTech' + techTypeID)[0]) {
				document.getElementsByName('shipDataAttackerTech' + techTypeID)[0].value = '';
			}
			
			if(this.shipData['shipData' + this.nfsSlotNo + 'DefenderTech' + techTypeID]) {
				document.getElementsByName('shipDataDefenderTech' + techTypeID)[0].value = this.shipData['shipData' + this.nfsSlotNo + 'DefenderTech' + techTypeID];
			} else if(document.getElementsByName('shipDataDefenderTech' + techTypeID)[0]) {
				document.getElementsByName('shipDataDefenderTech' + techTypeID)[0].value = '';
			}
		}
	}
	
	/**
	 * Stores given ship data
	 */
	this.storeShipData = function(newShipData) {	
		this.shipData = newShipData;
		
		this.updateShipData();
	}
	
	/**
	 * Reads the data from a given espionage report
	 */
	this.readData = function() {
		// prepare
		var report = document.getElementById('scanInput').value;
	
		var stringUtil = new StringUtil(report);
		
		report = stringUtil.trim();
		report = stringUtil.replace('\n', ' ');
		report = stringUtil.replace('\r', '');
		report = stringUtil.replace('\t', ' ');
		
		// clean data
		var scanArray = report.split(' ');
		var dataArray = new Array();
		var y = 0;
		
		var scanArrayLength = scanArray.length;
		for(var i = 0; i <= scanArrayLength; ++i) {
			if(scanArray[i] != "") {
				dataArray[y] = scanArray[i];
				++y;
			}
		}
		
		// read data
		var dataArrayLength = dataArray.length - 1;
		alert(dataArrayLength);
		
		for(var i = 0; i < dataArrayLength; ++i) {
			document.getElementById('debug').innerHTML += ';new-'+i+'-'+this.language[i]+'-'+dataArray[i];
			
			for(var j = 109; j < 500; ++j) {
				//if(i == j) document.getElementById('debug').innerHTML += ','+j+'-'+this.language[j];
				if(this.language[j]) {
					if(j >= 200 && j < 500) {
						if(dataArray[i] == this.language[j] || (dataArray[i - 1] + " " + dataArray[i]) == this.language[j]) {
							stringUtil = new StringUtil(dataArray[i + 1]);
							value = stringUtil.replace('.', '');
							
							this.shipData['shipData' + this.nfsSlotNo + 'Defender' + j] = value;
						}
					} else if(j >= 109 && j <= 111) {
						if(dataArray[i] == this.language[j] || (dataArray[i - 1] + " " + dataArray[i]) == this.language[j]) {
							stringUtil = new StringUtil(dataArray[i + 1]);
							value = stringUtil.replace('.', '');
							
							this.shipData['shipData' + this.nfsSlotNo + 'DefenderTech' + j] = value;
							document.getElementById('debug').innerHTML += 'b!!!';
						}
					}
					//document.getElementById('debug').innerHTML += 'a!!!';
				}
			}
		}
		
		// view data
		this.updateShipData();
	}
	
	/**
	 * Unsets the saved data
	 */
	this.reset = function() {
		this.shipData = new Object();
		
		return true;
	}
	
	/**
	 * Converts the saved data to a string and saves it in the form
	 */
	this.submit = function() {
		this.getShipData();
	
		document.getElementsByName('shipData')[0].value = lwUtil.serialize(this.shipData);
		
		return false;
	}

	this.init();
}

var simulator = new Simulator();