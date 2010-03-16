<script language="JavaScript">
    function galaxy_submit(value) {
      document.getElementById('auto').name = value;
      document.getElementById('galaxy_form').submit();
    }

    function fenster(target_url,win_name) {
      var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=640,height=480,top=0,left=0');
new_win.focus();
    }
function f(target_url,win_name) {
  var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=550,height=280,top=0,left=0');
  new_win.focus();
}
</script>

<script language="JavaScript" src="wcf/js/default.js"></script>
<script language="JavaScript" src="wcf/js/AjaxRequest.class.js"></script>
<script language="JavaScript" src="js/Galaxy.class.js"></script>
<script language="JavaScript" src="js/overlib.js"></script>

<script type="text/javascript">
var galaxy = new Galaxy({galaxy}, {system}, {spio_anz});

</script>
<center>
<div id="galaxy_top">
<form action="" method="post" id="galaxy_form">
<input id="auto" value="dr" type="hidden">
<table border="0">
  <tr>
    <td>
      <table>
        <tr>
         <td class="c" colspan="3">{Galaxy}</td>
        </tr>
        <tr>
          <td class="l"><input name="galaxyLeft" value="&lt;-" onClick="galaxy_submit('galaxyLeft')" type="button"></td>
          <td class="l"><input name="galaxy" value="{galaxy}" size="5" maxlength="3" tabindex="1" type="text">
          </td><td class="l"><input name="galaxyRight" value="-&gt;" onClick="galaxy_submit('galaxyRight')" type="button"></td>
        </tr>
       </table>
      </td>
      <td>
       <table>
        <tr>
         <td class="c" colspan="3">{Solar_system}</td>
        </tr>
         <tr>
          <td class="l"><input name="systemLeft" value="&lt;-" onClick="galaxy_submit('systemLeft')" type="button"></td>
          <td class="l"><input name="system" value="{system}" size="5" maxlength="3" tabindex="2" type="text">
          </td><td class="l"><input name="systemRight" value="-&gt;" onClick="galaxy_submit('systemRight')" type="button"></td>
         </tr>
        </table>
       </td>
      </tr>
      <tr>
        <td colspan="2" align="center"> <input value="{Show}" type="submit"></td>
      </tr>
     </table>
</form>
</div>
<br />
<table width="569">
<tr>
	<td class="c" colspan="8">{Solar_system_at}</td>
	</tr>
	<tr>
	  <td class="c planetNo">{Pos}</td>
	  <td class="c planetImg">{Planet}</td>
	  <td class="c planetName">{Name}</td>
	  <td class="c moonImg">{Moon}</td>
	  <td class="c debrisImg">{Debris}</td>
	  <td class="c playerName">{Player} ({State})</td>
	  <td class="c allianceName">{Alliance}</td>
	  <td class="c actionLinks">{Actions}</td>
	</tr>
    {echo_galaxy}
	<tr>
<td class="c" colspan="6">{planet_count} bewohnte Planeten</td>
<td class="c" colspan="2"><a href="#" onmouseover="this.T_WIDTH=150;return escape('<table><tr><td class=\'c\' colspan=\'2\'>Legende</td></tr><tr><td width=\'125\'>Starker Spieler</td><td><span class=\'strong\'>s</span></td></tr><tr><td>Schwacher Spieler</td><td><span class=\'noob\'>n</span></td></tr><tr><td>Urlaubsmodus</td><td><span class=\'vacation\'>u</span></td></tr><tr><td>Gesperrt</td><td><span class=\'banned\'>g</span></td></tr><tr><td>7 Tage inaktiv</td><td><span class=\'inactive\'>i</span></td></tr><tr><td>28 Tage inaktiv</td><td><span class=\'longinactive\'>l</span></td></tr><tr><td>Gameadmin</td><td><span class=\'admin\'>a</span></td></tr></table>')">Legende</a></td>
</tr>
<tr>
<td class="c" colspan="4">
<span id="missiles">{iraks}</span> Interplantarraketen verf&uuml;gbar.
<br />
<span id="phalanx_costs">{phalanx_costs}</span> Kosten / Phalanxscan.</td><td class="c" colspan="2">
<span id="slots">{slots}</span> von {max_slots} Flottenslots belegt.</td><td class="c" colspan="2">
<span id="recyclers">{recycler}</span> Recycler<br><span id="probes">{probes}</span> Spionagesonden</td>
</tr>
<tr style="display: none; align:left" id="fleetstatusrow">
	  <th colspan="8"><div style="align:left" id="fleetstatus"></div>
		<table style="font-weight: bold; align:left" id="fleetstatustable" width="100%">
		</table>
	  </th>
	</tr>
</table>
<br />
<form action="game/index.php?action=FireInterplanetaryMissiles" name="interplanetaryrmissiles" method="post">
<table width="569" id="interplanetaryrmissilestable" style="display:none">
	<tr>
		<td class="c" colspan="2">Interplanetarraketen abschie&szlig;en</td>
	</tr>
	<tr>
	  	<td class="l">Ziel</td>
	  	<td class="l" style="width: 50%;">
	  		<input name="galaxy" size="3" maxlength="2" value="{galaxy}" />
			<input name="system" size="3" maxlength="3" value="{system}" />
			<input name="planet" size="3" maxlength="2" id="ipmplanet" />
			<select name="planetKind" size="1">
				<option value="1" selected="selected">Planet</option>
				<option value="3">Mond</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="l">Prim&auml;rziel</td>
	  	<td class="l" style="width: 50%;">
			<select name="primaryDestination" size="1">
				<option value="0" selected="selected"> - Kein Prim&auml;rziel - </option>
				<option value="401">Raketenwerfer</option>
				<option value="402">Leichter Laser</option>
				<option value="403">Schwerer Laser</option>
				<option value="404">Gau&szlig;kanone</option>
				<option value="405">Ionenwerfer</option>
				<option value="406">Plasmawerfer</option>
				<option value="407">Kleine Schildkuppel</option>
				<option value="408">Gro&szlig;e Schildkuppel</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="l">Anzahl</td>
		<td class="l"><input name="spec503" size="3" maxlength="2" /></td>
	</tr>
	<tr>
		<th colspan="2"><input type="submit" value="Abschicken" /></th>
	</tr>
</table>
</form>
</center>
<script language="JavaScript" src="js/wz_tooltip.js"></script>