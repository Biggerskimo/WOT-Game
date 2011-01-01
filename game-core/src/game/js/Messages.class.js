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
function Messages()
{
	this.delete = function(messageID)
	{
		$.get("index.php?action=MessageManipulation&command=delete&messageID=" + messageID);
		$("#message" + messageID).slideUp("fast");
	}
	
	this.remember = function(messageID)
	{
		$("#message" + messageID).toggleClass("messageRemembered");
		$.get("index.php?action=MessageManipulation&command=remember&messageID=" + messageID);
	}
	
	this.notify = function(messageID)
	{
		if(confirm(language['message.notify.sure']))
		{
			$.get("index.php?action=MessageManipulation&command=notify&messageID=" + messageID,
				function() { alert(language['message.notify.done']); } );
		}
	}

	this.ignore = function(userID)
	{
		if(confirm(language['message.ignore.sure']))
		{
			$.get("index.php?action=UserIgnore&doIgnore=1&userID=" + userID,
				function() { alert(language['message.ignore.done']); } );
		}
	}
	
	this.toggle = function(messageID)
	{
		$("#message" + messageID + " .messageMore").slideToggle("fast", function() {
			$("#message" + messageID).toggleClass("showMessage").toggleClass("hideMessage");
		});
	}
}
var messages = new Messages();