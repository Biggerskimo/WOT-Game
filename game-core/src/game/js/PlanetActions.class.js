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
 * Handles the PlanetActionsForm.
 * 
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
function PlanetActions() {
	this.init = function() {
		if(document.getElementById('actionRename')) {
			document.getElementById('actionRename').planetActions = this;
			document.getElementById('actionRename').onfocus = function() { this.planetActions.focusRename(); return true; };
			
			document.getElementById('actionDelete').planetActions = this;
			document.getElementById('actionDelete').onfocus = function() { this.planetActions.focusDelete(); return true; };
			
			if(document.getElementById('actionRename').checked) {
				this.disableDelete();
			}
			else {
				this.disableRename();
			}
		}
	}
	
	this.focusRename = function(element) {
		this.disableDelete();
	}
	
	this.focusDelete = function(element) {
		this.disableRename();
	}
	
	this.disableDelete = function() {
		document.getElementById('deleteContainer').className += ' disabled';
		
		document.getElementById('actionDelete').checked = false;
		
		document.getElementById('password').setAttribute('disabled', 'disabled');
		document.getElementById('deleteSubmit').setAttribute('disabled', 'disabled');
		
		this.enableRename();
	}
	
	this.disableRename = function() {
		document.getElementById('renameContainer').className += ' disabled';
		
		document.getElementById('actionRename').checked = false;
		
		document.getElementById('newName').setAttribute('disabled', 'disabled');
		document.getElementById('renameSubmit').setAttribute('disabled', 'disabled');
		
		this.enableDelete();
	}
	
	this.enableDelete = function() {
		document.getElementById('deleteContainer').className = document.getElementById('deleteContainer').className.replace(/\s?disabled/, '');
		
		document.getElementById('actionDelete').checked = true;
		
		document.getElementById('password').removeAttribute('disabled');
		document.getElementById('deleteSubmit').removeAttribute('disabled');		
	}
	
	this.enableRename = function() {
		document.getElementById('renameContainer').className = document.getElementById('renameContainer').className.replace(/\s?disabled/, '');
		
		document.getElementById('actionRename').checked = true;
		
		document.getElementById('newName').removeAttribute('disabled')
		document.getElementById('renameSubmit').removeAttribute('disabled')
	}
	
	this.init();
}