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
 * @copyright	2010 Lost Worlds <http://lost-worlds.net>
 */
function Overview() {
	this.newsCount = 0;
	this.oventCount = 0;
	this.setUnloadListener = false;
	
	this.closeNews = function(newsID) {
		var ajaxRequest = new AjaxRequest();
		
		ajaxRequest.openGet('index.php?action=CloseNews&newsID='+newsID, function() { });
				
		this.newsCount--;
		
		if(this.newsCount == 0) {
			// hide complete message box
			document.getElementById('news').style.display = 'none';
		}
		else {
			// hide only this news
			document.getElementById('news' + newsID).style.display = 'none';
		}
	}
	
	this.registerNews = function(newsID) {
		this.newsCount++;
		
		document.getElementById('newsLink' + newsID).setAttribute('target', '_blank');
	}
	
	this.registerOvent = function(oventID) {
		this.oventCount++;
	}
	
	this.hideOvent = function(oventID, restore) {
		if(restore || confirm(language['hideOvent.sure'])) {
			if(!restore) {
				var ajaxRequest = new AjaxRequest();
				
				ajaxRequest.openGet('index.php?action=HideOvent&oventID='+oventID, function() { });
			}
			
			this.oventCount--;
			
			if(this.oventCount == 0) {
				// hide complete ovent box
				document.getElementById('ovents').style.display = 'none';
			}
			else {
				// hide only this ovent
				document.getElementById('ovent' + oventID).style.display = 'none';
				
				document.getElementById('hiddenOventsLink').style.display = 'block';
			}
		}
	}
	
	this.restoreOvent = function(oventID) {
		document.getElementById('ovent' + oventID).style.display = 'none';
		
		var ajaxRequest = new AjaxRequest();
		
		ajaxRequest.openGet('index.php?action=HideOvent&checked=0&oventID='+oventID, function() { });
		
		this.hideOvent(oventID, true);
		
		if(!this.setUnloadListener) {
			window.onunload = function() { parent.location.reload(); };
			
			this.setUnloadListener = true;
		}
	}
}
var overview = new Overview();