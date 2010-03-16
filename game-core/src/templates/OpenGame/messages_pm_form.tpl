
<script src="js/LWUtil.class.js" type="text/javascript"></script>

<br />
<center>
<form enctype="multipart/form-data" action="messages.php?mode=write&id={id}" method="post">
 <table width="519">
   <tr>
   <td class="c" colspan="2">{Send_message}</td>
  </tr>
  <tr>
   <th>{Recipient}</th>
   <th><input type="text" name="to" size="40" value="{to}" /></th>
  </tr>
  <tr>
   <th>{Subject}</th>
   <th>
    <input type="text" name="subject" size="40" maxlength="40" value="{subject}" />
   </th>
  </tr>
  <tr>
   <th>
    {Message} (<span id="CountLetters">0</span> / 500 {characters})
   </th>
   <th>
    <textarea name="text" id="text" cols="40" rows="10" size="100" onKeypress="lwUtil.checkLength(500, 'text', 'CountLetters');" onkeyup="lwUtil.checkLength(500, 'text', 'CountLetters');">{text}</textarea>
   </th>
  </tr>
  <tr>
   <th colspan="2"><input type="submit" value="Absenden" /></th>
  </tr>
   </table>
</form>
</center>
