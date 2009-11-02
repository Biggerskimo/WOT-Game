<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict //EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="language" content="de" />
<meta name="distribution" content="global" />
<meta name="audience" content="all" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="pragma" content="no-cache" />
<meta name="robots" content="index, follow" />
<meta name="author" content="Rene Kraxner" />
<meta name="author-mail" content="ride2fire@gmx.at" />
<meta name="description" content="Lost-Worlds. Baue Dein Imperium im Weltraum!" />
<meta name="keywords" content="Lost-Worlds, Lost, Worlds, Browsergame, gratis, Community, Flotten, Onlinegame, Spiel, Onlinespiel, MMOG" />
<meta name="copyright" content="(c) 2007 by Rene Kraxner, 2007 - 2008 by Biggerskimo, design 2007 by agent" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel='stylesheet' type='text/css' href='css/styles2.css' />
<title>Lost-Worlds.de</title>
</head>


<body topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0">
<div id="irc">
  <div id="content_irc" class="contentbox"> 
  		<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful, in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

  		if(isset($_REQUEST['userName'])) $nickName = trim($_REQUEST['userName']);
      	if(empty($nickName)) {
      		header('Location: irc-nick.htm');
      		exit;
		} else {
			?>
      	<applet code="IRCApplet.class" codebase="http://lost-worlds.net/pjirc" archive="irc.jar,pixx.jar" width="700" height="500">
			<param name="CABINETS" value="irc.cab,securedirc.cab,pixx.cab">
			<param name="nick" value="<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful, in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/
 echo $nickName; ?>">
			<param name="name" value="Lost Worlds Gast @ http://lost-worlds.net/irc-nick.htm">
			<param name="host" value="irc.kippeln.org">
			<param name="gui" value="pixx">
			
			<param name="command1" value="/join #lostWorlds">
			<param name="quitmessage" value="http://lost-worlds.net/irc-nick.htm">
			<param name="alternatenick" value="<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful, in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/
 echo $nickName; ?>`">
		</applet>
 	     	<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful, in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

		}
		?>
  </div>
</div>
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://lost-worlds.net/piwik/" : "http://lost-worlds.net/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://lost-worlds.net/piwik/piwik.php?idsite=1" style="border:0" alt=""/></p></noscript>
<!-- End Piwik Tag -->


</body>
</html>