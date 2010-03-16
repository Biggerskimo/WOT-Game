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
 * @author		Biggerskimo, Michael Rentsch
 * @copyright	2008 Lost Worlds <http://lost-worlds.net>
 */
function LWUtil() {
	this.dimension = 0;
	this.string = '';
	this.textareas = new Array();
	this.spans = new Array();
	
	/**
	 * Repeats a given string
	 */
	this.repeat = function(string, multiplier) {
		var newString = '';
		for(var count = 0; count < multiplier; count++) {
			newString += string;
		}
		
		return newString;
	}
	
	/**
	 * Converts a array or object to a string
	 */
	this.serialize = function(givenValue) {
	
		++this.dimension;

		var value;
		var serialized = '';		
		
		for(var i in givenValue) {
			value = givenValue[i];
			
			if(value.constructor == Object || value.constructor == Array) {
				value = this.repeat('(', this.dimension) + this.serialize(value) + this.repeat(')', this.dimension);
			}
						
			serialized += i + ',' + value + ';';
		}

		--this.dimension;

		return serialized;
	}

	this.checkLength = function(length, name, spanID) {
		if (this.textareas[name] == null) {
			this.textareas[name] = document.getElementById(name);
		}
		
		if (this.spans[spanID] == null) {
			this.spans[spanID] = document.getElementById(spanID);
		} else {
			this.spans[spanID].innerHTML = this.textareas[name].value.length;
		}
		
		if (this.textareas[name].value.length > length) {
			this.spans[spanID].className = 'tooManyChars';
		} else {
			this.string = this.textareas[name].value;
			this.spans[spanID].className = '';
		}
		
		
	}
}
var lwUtil = new LWUtil();