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
 * @copyright	2007 - 2008 Lost Worlds <http://lost-worlds.net>
 */
function Time(container, time, countdown, newSite) {
	this.startTime = time;
	this.actualTime = time;
	this.container = container;
	this.newSite = newSite;
	this.interval = null;
	this.timeout = null
	this.countdownFlag = countdown;
	this.inited = false;

	this.init = function() {
		if(!this.inited) this.setTime(this.startTime);

		this.inited = true;

		if(!this.countdownFlag) return;

		this.interval = window.setInterval(this.container + '.countdown()', 1000);
		this.timeout = window.setTimeout(this.container + '.finish(true)', this.startTime * 1000 + 500);
	}

	this.setTime = function(time) {
		this.finish(false);
		if(this.inited) this.init();

		var timeString = this.parseTime(time);
		this.actualTime = time;

		document.getElementById(this.container).innerHTML = timeString;
	}

	this.countdown = function() {
		if(this.actualTime >= 1) --this.actualTime;

		this.setTime(this.actualTime);
	}

	this.finish = function(changeLocation) {
		window.clearInterval(this.interval);
		window.clearTimeout(this.timeout);

		if(this.newSite && changeLocation) location.href = this.newSite;
	}

	this.parseTime = function(time) {
		if(time < 1) {
			this.finish(true);
			return '-';
		}

		string = this.getTimeString(time);
		
		// absolute time
		var dateObj = new Date();
		
		time += dateObj.getHours() * 60 * 60 + dateObj.getMinutes() * 60 + dateObj.getSeconds();

		string = string + '<br /><span style="color: limegreen; font-weight: bold;">' + this.getTimeString(time) + '</span>';

		return string;
	}
	
	this.getTimeString = function(time) {
		// days
		if(time > 60 * 60 * 24) {
			var days = Math.floor(time / (60 * 60 * 24)) + 'T ';
			time = time % (60 * 60 * 24); 
		} else var days = '';
	
		// hours
		var hours = Math.floor(time / (60 * 60));

		// minutes
		time = time % (60 * 60);
		var minutes = Math.floor(time / 60);
		if(minutes < 10) minutes = '0' + minutes;

		// seconds
		time = time % 60;
		var seconds = Math.floor(time);
		if(seconds < 10) seconds = '0' + seconds;

		var string = days + hours + ':' + minutes + ':' + seconds;
	
		return string;
	}

	this.init();
}