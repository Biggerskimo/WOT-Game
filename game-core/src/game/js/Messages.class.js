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
	this.checkedCount = 0;
	this.dblClickHref = "";
	
	this.init = function()
	{
		$(document).ready(function() {
			messages.initMenu();
		});
	}
	
	this.initMenu = function()
	{
		$("#checkedMessages").click(function() {
			messages.toggleMenu("fast");
		});
		$(".checkedMessagesTitle").dblclick(function() {
			if(messages.dblClickHref)
				location.href = messages.dblClickHref;
		});
	}
	
	this.toggleMenu = function(duration)
	{
		$("#checkedMessages").toggleClass("showMenu");
		
		// choose correct menu
		if(!messages.checkedCount)
			$("#checkedMessagesActionsNone").slideToggle("fast");
		else
			$("#checkedMessagesActionsSome").slideToggle("fast");
	}
	
	this.deleteMsg = function(messageID)
	{
		$.get("index.php?action=MessageManipulation&command=delete&messageID=" + messageID);
		$("#message" + messageID).slideUp("fast");
	}
	
	this.check = function(messageID, checkbox)
	{
		$("#message" + messageID).toggleClass("messageChecked");
		$.get("index.php?action=MessageManipulation&command=check&messageID=" + messageID);
		
		if(!checkbox)
		{
			checkbox = $("#checkMessage" + messageID);
			if(checkbox.is(":checked"))
				checkbox.removeAttr("checked");
			else
				checkbox.attr("checked", "checked");
		}
		
		if($("#message" + messageID).hasClass("messageChecked"))
			this.checkedCount++;
		else
			this.checkedCount--;
		
		$("#checkedCount").text(this.checkedCount);
		
		// choose correct trigger (and menu)
		$(".checkedMessagesTitle").hide();
		if(!this.checkedCount)
		{
			$("#checkedMessagesTitleNone").show();
			
			if($("#checkedMessagesActionsSome").is(":visible"))
			{
				$("#checkedMessagesActionsSome").hide();
				$("#checkedMessagesActionsNone").show();
			}
		}
		else if(this.checkedCount == 1)
		{
			$("#checkedMessagesTitleOne").show();

			if($("#checkedMessagesActionsNone").is(":visible"))
			{
				$("#checkedMessagesActionsNone").hide();
				$("#checkedMessagesActionsSome").show();
			}
		}
		else if(this.checkedCount > 1)
		{
			$("#checkedMessagesTitleMore").show();
		}
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
	
	this.init();
}
var messages = new Messages();