
 <center>
  <!-- begin stat header -->
  <form method="post">
  <table width="519">
   <tr>
    <td class="c">{Statistics}  Stand: {data}</td>

   </tr>
   <tr>
    <th>
     {Show}&nbsp;
     <select name="who" onChange="javascript:document.forms[0].submit()">
		{who}
     </select>
     &nbsp;{by}&nbsp;
     <select name="type" onChange="javascript:document.forms[0].submit()">
		{type}
           </select>
     &nbsp;{InThePositions}     <select name="start" onChange="javascript:document.forms[0].submit()">
		{start}
          </select>
    </th>
    </tr>

    </table>
    </form>
    <!-- end stat header -->
    {body_table}
    {body_values}

   </tr>
  </table>
 </center>
  <script language="JavaScript" type="text/javascript" src="js/wz_tooltip.js"></script>
 </body>
</html>
