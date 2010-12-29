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
		if(confirm(language['message.delete.sure']))
		{
			$.get("index.php?action=MessageManipulation&command=delete&messageID=" + messageID);
			$("#message" + messageID).slideUp("fast");
		}
	}
	
	this.remember = function(messageID)
	{
		$("#message" + messageID).toggleClass("messageRemembered");
		$.get("index.php?action=MessageManipulation&command=remember&messageID=" + messageID);
	}
}
var messages = new Messages();