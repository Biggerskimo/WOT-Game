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
 * @copyright	2007 - 2009 Lost Worlds <http://lost-worlds.net>
 */
function Time(container, time, countdown, newSite, tick) {
	this.startTime = time;
	this.currentTime = time;
	this.container = container;
	this.newSite = newSite;
	this.interval = null;
	this.timeout = null
	this.countdownFlag = countdown;
	this.inited = false;
	this.tick = tick;
	
	this.language = new Object();
	this.language['tomorrow'] = 'Morgen';
	this.language['theDayAfterTomorrow'] = 'Übermorgen';
	this.language['day'] = 'Tag';
	this.language['days'] = 'Tage';

	this.init = function() {
		if(!this.inited) {
			if(typeof this.tick == 'undefined') {
				this.tick = 1;
			}
			
			this.setTime(this.startTime);
		}

		this.inited = true;

		if(!this.countdownFlag) return;

		this.interval = window.setInterval(this.container + '.countdown()', 1000);
		this.timeout = window.setTimeout(this.container + '.finish(true)', this.startTime * 1000 + 500);
	}

	this.setTime = function(time) {
		this.finish(false);
		if(this.inited) this.init();

		var timeString = this.parseTime(time);
		this.currentTime = time;

		document.getElementById(this.container).innerHTML = timeString;
	}

	this.countdown = function() {
		if(this.currentTime >= 1) this.currentTime -= this.tick;

		this.setTime(this.currentTime);
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

		string = string + '<br /><span style="color: limegreen; font-weight: bold;">' + this.getTimeStringExt(time) + '</span>';

		return string;
	}
	
	this.getTimeStringExt = function(time) {
		var dateObj = new Date();
		var currentDate = dateObj.getDate();
		var currentMonth = dateObj.getMonth();
		
		// hours
		var hours = Math.floor(time / (60 * 60));

		// minutes
		time = time % (60 * 60);
		var minutes = Math.floor(time / 60);

		// seconds
		time = time % 60;
		var seconds = Math.floor(time);
		
		dateObj.setHours(dateObj.getHours() + hours);
		dateObj.setMinutes(dateObj.getMinutes() + minutes);
		dateObj.setSeconds(dateObj.getSeconds() + seconds);
		
		var date = dateObj.getDate();
		if(date < 10) date = '0' + date;
		
		var month = dateObj.getMonth() + 1;
		if(month < 10) month = '0' + month;
		
		hours = dateObj.getHours();
		if(hours < 10) hours = '0' + hours;
		
		minutes = dateObj.getMinutes();
		if(minutes < 10) minutes = '0' + minutes;
		
		seconds = dateObj.getSeconds();
		if(seconds < 10) seconds = '0' + seconds;
		
		if(dateObj.getDate() == currentDate && dateObj.getMonth() == currentMonth) {
			var string = hours+':'+minutes+':'+seconds;			
		}
		else if(date == currentDate + 1) {
			var string = this.language['tomorrow']+', '+hours+':'+minutes+':'+seconds;
		}
		else if(date == currentDate + 2) {
			var string = this.language['theDayAfterTomorrow']+', '+hours+':'+minutes+':'+seconds;
		}
		else {
			var string = date+'.'+month+', '+hours+':'+minutes+':'+seconds;			
		}
		
		return string;
	}
	
	this.getTimeString = function(time) {
		// days
		if(time > 60 * 60 * 24) {
			var days = Math.floor(time / (60 * 60 * 24));
			if(days == 1) {
				days += this.language['day']+' ';
			}
			else {
				days += this.language['days']+' ';
			}
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