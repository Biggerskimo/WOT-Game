<center>
<br>
<table width="600">
<td class="c" colspan="5"><b>Kontakty</b></td></tr>
<tr><th colspan=5></th></tr>
<tr><th colspan=5 id="server_time">
<script type="text/javascript">
var currTime = 1180528889;

var days = new Array('Nie','Pon','Wto','&#346;ro','Czw','Pi&#261;','Sob');
var months = new Array('Stycznia','Lutego','Marca','Kwietnia','Maja','Czerwca','Lipca','Sierpnia','Wrze&#347;nia','Grudnia');
function czasomierz(f)
{
    if(f == false) currTime++;
    var date = new Date(parseInt(currTime+"000"));
    var seconds = date.getSeconds();
        if(seconds < 10) seconds = "0"+seconds;
    var minutes = date.getMinutes();
        if(minutes < 10) minutes = "0"+minutes;
    
    var czas = days[date.getDay()] + ", " + date.getDate() + " " + months[date.getMonth()] + " " + date.getHours() + ":" + minutes + ":" + seconds;
    document.getElementById('server_time').innerHTML = czas;
}
czasomierz(true);
setInterval("czasomierz(false)",1000)
</script>
</th></tr>
<tr><th colspan=5></th></tr>
  <tr>
	<td colspan="5" class="c"> <b>Sposoby kontaktowania si&#281; z GO</b></td>
  </tr>
<tr><font color="lime"><th colspan="5"><font color="orange">Poni&#380;sza lista umo&#380;liwia skontaktowanie si&#281; z poszczególnym GO:
<tr><th><font color="lime">Funkcja</th><th><font color="lime">Nick</th><th><font color="lime">PW</th><th><font color="lime">E-mail</th><th><font color="lime">Gadu-Gadu</th>
{contact_list}
</table></center>