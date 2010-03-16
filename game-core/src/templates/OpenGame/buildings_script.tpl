
<br>Aktuelle Produktion:
<br>
<span id="shipName"></span> <span id="shipTime"></span>

<script type="text/javascript">
	var shipTypeArray = new Object();
	{shipTypeArrayText}
	var doneTime = {b_hangar_id_plus};
	var shipTime = null;
  </script>
  <br>
  <form name="Atr" method="get" action="buildings.php">
  <input type="hidden" name="mode" value="fleet">
  <table width="530">
   <tr>
      <td class="c" >{work_todo}</td>

   </tr>
   <tr>
    <th ><select name="auftr" size="10"></select></th>
     </tr>
   <tr>
    <td class="c" ></td>
   </tr>
  </table>
  </form>
  {total_left_time}

  {pretty_time_b_hangar}<br></center>
  <script type="text/javascript" src="js/Time.class.js"></script>
  <script type="text/javascript" src="js/Shipyard.class.js"></script>