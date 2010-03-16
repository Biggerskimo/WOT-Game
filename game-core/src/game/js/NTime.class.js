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
 * @copyright	2009 - 2010 Lost Worlds <http://lost-worlds.net>
 */
function NTime(targetNode, date, tick, format) {
	this.node = targetNode;
	this.date = (typeof date == "undefined") ? new Date() : date;
	this.tick = (typeof tick == "undefined") ? 1 : tick;
	this.format = (typeof format == "undefined") ? "%T" : format;
	this.interval = null;
	
	this.init = function() {
		if(this.tick != 0) {
			var instance = this;
			
			this.interval = window.setInterval(function() { instance.doTick(); }, 1000);
		}
		
		this.print();
	}
		
	this.doTick = function() {
		this.date.setSeconds(this.date.getSeconds() + this.tick);
		
		if(this.date.getTime() <= 0) {
			this.date.setSeconds(0);
			window.clearInterval(this.interval);
			targetNode.data = "---";
		}
		else {
			this.print();
		}
	}
	
	this.print = function() {
		if(typeof this.format == "string") {
			targetNode.data = this.date.format(this.format);
		}
		else if(this.format == -1) {
			targetNode.data = this.formatVariableRelative(this.date.getTime() / 1000);
		}
		else if(this.format == -2) {
			targetNode.data = this.formatVariableAbsolute(this.date.getTime() / 1000);
		}
	}
	
	this.formatVariableRelative = function(time) {
		if(time > 60 * 60 * 24) {
			var days = Math.floor(time / (60 * 60 * 24));
			if(days == 1) {
				days += language['day']+' ';
			}
			else {
				days += language['days']+' ';
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
	
	this.formatVariableAbsolute = function(time) {
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
			var string = language['tomorrow']+', '+hours+':'+minutes+':'+seconds;
		}
		else if(date == currentDate + 2) {
			var string = language['theDayAfterTomorrow']+', '+hours+':'+minutes+':'+seconds;
		}
		else {
			var string = date+'.'+month+', '+hours+':'+minutes+':'+seconds;			
		}
		
		return string;
	}
	
	this.init();
}