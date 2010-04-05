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
 * Adds a tooltip and makes it (in)visible when needed.
 * @author		Biggerskimo
 * @copyright	2009 Lost Worlds <http://lost-worlds.net>
 */
var tooltips = [];
function Tooltip(source, destination, fixed) {	
	this.source = source;
	this.destination = destination;
	this.overCount = 0;
	this.fixed = (typeof fixed == 'boolean') ? fixed : false;
	this.instanceNo = tooltips.length;
	tooltips[this.instanceNo] = this;
	this.tFixed = this.fixed;
	
	/**
	 * Adds the tooltip.
	 */
	this.init = function() {
		this.prepareSource();
		
		this.destination.tooltipInstance = this;
		this.destination.onmouseover = function(e) { this.tooltipInstance.show(e); };
		this.destination.onmouseout = function() { this.tooltipInstance.hide(); };
		window.onkeydown = function(e) { for(var no in tooltips) tooltips[no].onkeydown(e); };
		
		this.source.parentNode.removeChild(this.source);
		document.getElementById("tooltipContainer").appendChild(this.source);
		
		this.source.tooltipInstance = this;
		this.source.onmouseover = function() { this.tooltipInstance.show(); };
		this.source.onmouseout = function() { this.tooltipInstance.hide(); };
		
		if(!this.fixed) {
			this.destination.onmousemove = function(e) { this.tooltipInstance.reposition(e); };	
			this.destination.onmousedown = function() { this.tooltipInstance.fix(); };
		}
	}
	
	/**
	 * Makes the source invisible etc.
	 */
	this.prepareSource = function() {
		this.source.style.position = "absolute";
		this.source.style.display = "none";
	}
	
	/**
	 * Shows the tooltip.
	 */
	this.show = function(e) {
		if(!e) e = window.event;
		
		this.overCount++;
		// run-in doesnt work with most browsers ...
		this.source.style.display = "block";
		
		if(typeof e == 'object') {
			this.reposition(e);
		}
	}
	
	/**
	 * Hides the tooltip.
	 */
	this.hide = function() {
		this.overCount--;
		
		if(!this.overCount) {
			if(this.fixed || !this.tFixed) {
				this.doHide();
			}
			else {
				window.setTimeout("tooltips[" + this.instanceNo + "].doHide()", 250);
			}
		}
	}
	
	/**
	 * Does the hiding.
	 */
	this.doHide = function() {
		if(!this.overCount) {
			this.source.style.display = "none";
			this.tFixed = false;
		}		
	}
	
	/**
	 * Sets a new position.
	 */
	this.reposition = function(e) {
		if(!e) e = window.event;
		
		if(!this.tFixed) {
			this.source.style.left = e.clientX + "px";
			this.source.style.top = e.clientY + "px";
		}
	}
	
	/**
	 * Fixes the tooltip temporarily.
	 */
	this.fix = function() {
		this.tFixed = true;
	}
	
	/**
	 * Fixes when the Ctrl-Button is pressed.
	 */
	this.onkeydown = function(e) {
		if(!e) e = window.event;
		
		if(this.overCount && e.keyCode == 17) {
			this.fix();
		}
	}
	
	this.init();
}