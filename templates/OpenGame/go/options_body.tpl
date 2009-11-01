<br>
<center>
<h2>Konfiguracja Ugameli</h2>

<form action="?mode=change" method="post">
 <table width="519">

     <tbody><tr><td class="c" colspan="2">Dane Ogólne</td></tr>
<tr>
      <th>Tytuł strony<br><small>Tytuł strony jest na samej górze okna przeglądarki.</small></th>
   <th><input name="game_name" size="20" value="{game_name}" type="text"></th>
    </tr>
  <tr>
  <th>Info od admina<br><small>Wyświetlane na dole strony</small></th>
   <th><input name="copyright" size="40" maxlength="254" value="{copyright}" type="text"></th>
  </tr>

   <tr><th colspan="2"></th></tr>
  

	<!-- Planet Settings -->
  
  <tr>
  <td class="c" colspan="2">Konfiguracja planet</td>
  </tr>
  <tr>
   <th>Ilość Pól</th>
   <th><input name="initial_fields" maxlength="80" size="10" value="{initial_fields}" type="text"> pól
   </th>
  </tr>
  <tr>
   <th>Przyśpieszenie wydobycia</th>
   <th>x<input name="resource_multiplier" maxlength="80" size="10" value="{resource_multiplier}" type="text">
   </th>
  </tr>
  <tr>
   <th>Podstawowe wydobecie Metalu</th>
   <th><input name="metal_basic_income" maxlength="80" size="10" value="{metal_basic_income}" type="text"> na godzine
  </tr>
  <tr>
   <th>Podstawowe wydobycie Kryształu</th>
   <th><input name="crystal_basic_income" maxlength="80" size="10" value="{crystal_basic_income}" type="text"> na godzine
   </th>
  </tr>
  <tr>
   <th>Podstawowe wydobycie Deuteru</th>
   <th><input name="deuterium_basic_income" maxlength="80" size="10" value="{deuterium_basic_income}" type="text"> na godzine
   </th>
  </tr>
  <tr>
   <th>Podstawowa Energia</th>
   <th><input name="energy_basic_income" maxlength="80" size="10" value="{energy_basic_income}" type="text"> 
   </th>
  </tr>

	<!-- Miscelaneos Settings -->

    <tr>
     <td class="c" colspan="2">Monitorowanie</td>
	</tr>

  <tr>
     <th>Wyświetlanie zmian w bazie</a></th>
   <th>
    <input name="debug"{debug} type="checkbox" />
   </th>
  </tr>

  <tr>
   <th colspan="2"><input value="Zastosuj zmiany" type="submit"></th>
  </tr>


   
 </tbody></table>

 
</form>

</center>
