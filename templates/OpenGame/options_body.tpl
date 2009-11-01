
<center>
<br>

<form action="{PHP_SELF}?mode=change" method="post">
 <table width="519">

     <tbody>
   <tr>
  <td class="c" colspan="2">{general_settings}</td>
  </tr>
  <tr>
   <th>{skins_example}</th>
   <th><input name="dpath" maxlength="80" size="40" value="{dpath}" type="text"> <br>
  </tr>
  <tr>
  	<th colspan="2">
  		<a href="game/index.php?form=PlanetSort">Planeten sortieren</a>
  	</th>
  </tr>
  <tr>
   <td class="c" colspan="2">{galaxyvision_options}</td>
  </tr>
  <tr>
   <th><a title="{spy_cant_tip}">{spy_cant}</a></th>
   <th><input name="spio_anz" maxlength="2" size="2" value="{user_spio_anz}" type="text"></th>
  </tr>
  <tr>
   <th>{tooltip_time}</th>
   <th><input name="settings_tooltiptime" maxlength="2" size="2" value="{user_settings_tooltiptime}" type="text"> {seconds}</th>
  </tr>
  <tr>
   <th>{mess_ammount_max}</th>
   <th><input name="settings_fleetactions" maxlength="2" size="2" value="{user_settings_fleetactions}" type="text"></th>
  </tr>
  <tr>
   <th>{show_ally_logo}</th>
   <th><input name="settings_allylogo"{user_settings_allylogo} type="checkbox" /></th>
  </tr>
     <tr>
   <th>{shortcut}</th>
   <th>{show}</th>
  </tr>
      <tr>
   <th><img src="{dpath}img/e.gif" alt="">   {spy}</th>
   <th><input name="settings_esp"{user_settings_esp} type="checkbox" /></th>
   </tr>
      <tr>
   <th><img src="{dpath}img/m.gif" alt="">   {write_a_messege}</th>
   <th><input name="settings_wri"{user_settings_wri} type="checkbox" /></th>
   </tr>
      <tr>
   <th><img src="{dpath}img/b.gif" alt="">   {add_to_buddylist}</th>
   <th><input name="settings_bud"{user_settings_bud} type="checkbox" /></th>
   </tr>
      <tr>
   <th><img src="{dpath}img/r.gif" alt="">   {attack_with_missile}</th>
   <th><input name="settings_mis"{user_settings_mis} type="checkbox" /></th>
   </tr>
      <tr>
   <th><img src="{dpath}img/s.gif" alt="">   {show_report}</th>
   <th><input name="settings_rep"{user_settings_rep} type="checkbox" /></th>
   </tr>

    <tr>
     <td class="c" colspan="2">{delete_vacations}</td>
  </tr>
  <tr>
   {urlaubs_modus}
  </tr>
  <tr>
   <th><a title="{deleteaccount_tip}">{deleteaccount}</a></th>
   <th><input name="db_deaktjava"{user_db_deaktjava} type="checkbox" />



   </th>
  </tr>
  <tr>
   <th colspan="2"><input value="{save_settings}" type="submit"></th>
  </tr>



 </tbody></table>


</form>

</center>
