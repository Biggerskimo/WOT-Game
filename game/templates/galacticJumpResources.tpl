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
<form action="index.php?action=GalacticJump" method="post">
	  <div><center>
		<table width="519" border="0" cellpadding="0" cellspacing="1">
		  <tr height="20">
			<td colspan="2" class="c">Flottenmen&uuml;</td>
		  </tr>
		  <tr height="20">
			<th width="50%">Ressourcen</th>
			<th>
			<center>
			<table width="100" border="0" cellpadding="0" cellspacing="1">
		  </th>
		  <tr height="20">
			<td colspan="3" class="c">Rohstoffe</td>
		  </tr>
		   <tr height="20">
		  <th>Metall</th>
		  <th><a href="javascript:maxResource('1'); shortInfo();">max</a></th>
		  <th width="50%"><input name="resource1" type="text" alt="Metall 456847639" size="21" onKeyUp="shortInfo();" /></th>

		 </tr>
		   <tr height="20">
		  <th>Kristall</th>
		  <th><a href="javascript:maxResource('2'); shortInfo();">max</a></th>
		  <th width="50%"><input name="resource2" type="text" alt="Kristall 157591454" size="21" onKeyUp="shortInfo();" /></th>
		 </tr>
		   <tr height="20">
		  <th>Deuterium</th>

		  <th><a href="javascript:maxResource('3'); shortInfo();">max</a></th>
		  <th width="50%"><input name="resource3" type="text" alt="Deuterium 9940712716" size="21" onKeyUp="shortInfo();" /></th>
		 </tr>
		   <tr height="20">
	  <th>Rest</th>
		  <th colspan="2"><div id="remainingresources">-</div></th>
		 </tr>
		 <tr height="20">
	  <th colspan="2"><a href="javascript:void(0)" onClick="noResources(); shortInfo();">Keine Rohstoffe</a></th>
	  <th><a href="javascript:void(0)" onClick="maxResources(); shortInfo();">Alle Rohstoffe</a></th>
		 </tr>
		 </table>
		  <input name="thisresource1" type="hidden" value="{@$actualPlanet->metal}" />
		  <input name="thisresource2" type="hidden" value="{@$actualPlanet->crystal}" />
		  <input name="thisresource3" type="hidden" value="{@$actualPlanet->deuterium}" />
		  <input name="storage" type="hidden" value="{@$storage}" />
		 </center>
		 </th>
		  </tr>
		  <tr height="20">
			<th>Entfernung</th>
			<th><div id="distance">{$distance}</div></th>
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
		<input type="hidden" name="pageNo" value="2" />
		<input type="hidden" name="dist" value="{@$distance}" />
			<th colspan="2"><input type="submit" value="Weiter" /></th>
			</form>
		  </tr>
		</table>
		  </center>
		</div>

</center>
<script language="JavaScript" src="../scripts/wz_tooltip.js"></script>