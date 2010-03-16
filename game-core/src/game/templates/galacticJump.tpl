<html>
<head>
<title>Sprungtor</title>
<link rel="SHORTCUT ICON" href="favicon.ico">
<link rel="stylesheet" type="text/css" href="{@$dpath}formate.css" />
<meta http-equiv="content-type" content="text/html; charset=iso8859-2" />
<script type="text/javascript" src="../js/galacticJump.js"></script>
</head>
<body>
<center>
<form action="index.php?page=GalacticJumpResources" method="post">
	  <div><center>
		<table width="519" border="0" cellpadding="0" cellspacing="1">
		  <tr height="20">
			<td colspan="2" class="c">Flottenmen&uuml;</td>
		  </tr>
		  <tr height="20">
			<th width="50%">Ziel</th>
			<th>
			<select name="targetmoon">
			 <option id="targetmoondummy" selected>Mond ausw&auml;hlen</option>
			 {foreach from=$moons item='moon'}
			 	<option value="{@$moon->planetID}" onMouseUp="setTarget({@$moon->galaxy}, {@$moon->system}, {@$moon->planet}, 3); shortInfo();"  onKeyUp="setTarget({@$moon->galaxy}, {@$moon->system}, {@$moon->planet}, 3); shortInfo();">{$moon->name} [{$moon->galaxy}:{$moon->system}:{$moon->planet}]</option>
			 {/foreach}
			  </select>
			  <script type="text/javascript">
			  document.getElementById('targetmoondummy').setAttribute('disabled', 'disabled');
			  </script>
		  </tr>
		  <tr height="20">
			<th>Entfernung</th>
			<th><div id="distance">-</div></th>
		  </tr>
		   <tr height="20">
			<th>Max. Entfernung</th>
			<th><div id="maxdistance">{$maxDistance}</div></th>
		  </tr>
		  <tr height="20">
			<th>Abk&uuml;hlzeit</th>
			<th><div id="duration">-</div></th>
		  </tr>
		  <tr height="20">
			<th>Deuteriumverbrauch</th>
			<th><div id="consumption">-</div>
			<input name="consumption" type="hidden" value="" /></th>
		  </tr>

		  </table>
		  <table width="519" border="0" cellpadding="0" cellspacing="1">
		  <tr height="20">
		  <input type="hidden" name="galaxy" value="{@$actualPlanet->galaxy}" />
		<input type="hidden" name="system" value="{@$actualPlanet->system}" />
		<input type="hidden" name="planet" value="{@$actualPlanet->planet}" />
		<input type="hidden" name="planettype" value="{@$actualPlanet->planettype}" />
		  <input type="hidden" name="thisgalaxy" value="{@$actualPlanet->galaxy}" />
		<input type="hidden" name="thissystem" value="{@$actualPlanet->system}" />
		<input type="hidden" name="thisplanet" value="{@$actualPlanet->planet}" />
		<input type="hidden" name="thisplanettype" value="{@$actualPlanet->planettype}" />
		<input type="hidden" name="thisGJ" value="{@$thisGJ}" />
		<input type="hidden" name="pageNo" value="1" />
			<th colspan="2"><input type="submit" value="Weiter" /></th>
			</form>
		  </tr>
		</table>
		  </center>
		</div>

</center>
<script language="JavaScript" src="../scripts/wz_tooltip.js"></script>