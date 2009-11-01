<br>
<center>
<h2>Konfiguracja Gry</h2>

<form action="?mode=change" method="post">
 <table width="519">

     <tbody><tr><td class="c" colspan="2">Info Gry</td></tr>
<tr>
      <th>Nazwa Gry<br><small></small></th>
   <th><input name="game_name" size="20" value="{game_name}" type="text"></th>
    </tr>
  <tr>
  <th>Text - copyright<br><small>Tekst wyswietlany przy prawach autorskich</small></th>
   <th><input name="copyright" size="40" maxlength="254" value="{copyright}" type="text"></th>
  </tr>

   <tr><th colspan="2"></th></tr>


	<!-- Planet Settings -->

  <tr>
  <td class="c" colspan="2">Konfiguracja Planet</td>
  </tr>
  <tr>
   <th>Poczatkowa Liczba Pol</th>
   <th><input name="initial_fields" maxlength="80" size="10" value="{initial_fields}" type="text"> pol
   </th>
  </tr>
  <tr>
   <th>Mnoznik Wydobycia</th>
   <th>x<input name="resource_multiplier" maxlength="80" size="10" value="{resource_multiplier}" type="text">
   </th>
  </tr>
  <tr>
   <th>Szybkosæ Gry (domyslnie: 2500)</th>
   <th><input name="game_speed" maxlength="80" size="10" value="{game_speed}" type="text">
  </th>
  </tr>
  <tr>
   <th>Szybkosæ Flot (domyslnie: 2500)</th>
   <th><input name="fleet_speed" maxlength="80" size="10" value="{fleet_speed}" type="text">
  </th>
  </tr>
  <tr>
   <th>Ilosc floty przerabiajacej sie na zlom w % - mozna ustawic wiecej niz 100</th>
   <th><input name="flota_na_zlom" maxlength="80" size="10" value="{flota_na_zlom}" type="text">
  </th>
  </tr>
  <tr>
   <th>Ilosc obrony przerabiajacej sie na zlom w % - mozna ustawic wiecej niz 100</th>
   <th><input name="obrona_na_zlom" maxlength="80" size="10" value="{obrona_na_zlom}" type="text">
  </th>
  </tr>


  </tr>
  <tr>
   <th>Podstawowe wydobycie metalu</th>
   <th><input name="metal_basic_income" maxlength="80" size="10" value="{metal_basic_income}" type="text"> na godzine
  </tr>
  <tr>
   <th>Podstawowe wydobycie krysztalu</th>
   <th><input name="crystal_basic_income" maxlength="80" size="10" value="{crystal_basic_income}" type="text"> na godzine
   </th>
  </tr>
  <tr>
   <th>Podstawowe wydobycie deuteru</th>
   <th><input name="deuterium_basic_income" maxlength="80" size="10" value="{deuterium_basic_income}" type="text"> na godzine
   </th>
  </tr>
  <tr>
   <th>Energia Poczatkowa</th>
   <th><input name="energy_basic_income" maxlength="80" size="10" value="{energy_basic_income}" type="text"> na godzine
   </th>
  </tr>

	<!-- Miscelaneos Settings -->

    <tr>
     <td class="c" colspan="2">Miscelaneos</td>
	</tr>

  <tr>
     <th>Tryb debug'owania</a></th>
   <th>
    <input name="debug"{debug} type="checkbox" />
   </th>
  </tr>

  <tr>
   <th colspan="2"><input value="Zapisz zmiany" type="submit"></th>
  </tr>



 </tbody></table>


</form>

</center>
