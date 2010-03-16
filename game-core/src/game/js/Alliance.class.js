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
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
function Alliance() {
	/**
	 * Views an other text on the alliance administration page.
	 *
	 * @param	string		text to activate (externalText, internalText or applicationTemplate)
	 */
	this.changeText = function(text) {
		// deactivate
		if(text !== 'externalText') {
			document.getElementById('externalTextLink').className = 'inactive';
			document.getElementById('externalText').style.display = 'none';
		}
		
		if(text !== 'internalText') {
			document.getElementById('internalTextLink').className = 'inactive';
			document.getElementById('internalText').style.display = 'none';
		}
		
		if(text !== 'applicationTemplate') {
			document.getElementById('applicationTemplateLink').className = 'inactive';
			document.getElementById('applicationTemplate').style.display = 'none';
		}
		
		// activate
		document.getElementById(text + 'Link').className = 'active';
		document.getElementById(text).style.display = 'block';
	}
	
	/**
	 * Shows/hides more information of a interrelation
	 *
	 * @param	int		id of the alliance
	 */
	this.showInterrelationInformation = function(allianceID2, displayType) {
		if(document.getElementById('interrelation' + allianceID2).style.display
		== displayType) {
			document.getElementById('interrelation' + allianceID2).style.display = 'none';
		} else document.getElementById('interrelation' + allianceID2).style.display = displayType;
	}
	
	/**
	 * Redirects to the rank change page.
	 *
	 * @param	int		user id
	 * @param	int		rank id
	 */
	 this.changeRank = function(userID, rankID) {
	 	// check leader
	 	if(rankID == -1) {
	 		var change = confirm(language['changeFounder.sure']);
	 		
	 		if(!change) return;
	 	}
	 	
	 	var newLocation = 'index.php?action=AllianceSetRank&userID=' + userID + '&allianceID=' + allianceID + '&rankID=' + rankID;
	 	
	 	window.location = newLocation;
	 }
}

var alliance = new Alliance();