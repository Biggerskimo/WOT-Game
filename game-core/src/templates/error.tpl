{include file="head.tpl"}
<center>
<center><hr>- <a onMouseOver="overlib('<center> Strona Glowna!</center>', FGCOLOR, 'black', BGCOLOR, '#FAEBD7', TEXTCOLOR, '#FFFFFF', STATUS, 'Dymek zwykly')" onMouseOut="nd();" href="http://www.ogamek.xt.pl/"><b><font class=test>Strona G��wna</font></a></b> -<hr></center><br>
<h3><font class=admin>B��dny e-mail</font></h3>
<br /><br />
{if $Action != "haslo"}
Najprawdopodobniej wpisa�e� z�y e-mail, lub �aden z graczy na ogamek'u, nie posiada takiego e-maila. Upewnij si�, czy nie pomyli�e� si� wpisuj�c maila, oraz czy napewno jeste� zarejestrowany w grze, a adres, kt�ry poda�e� przy rejestracji, zgadza si� z tym, kt�ry wpisa�e� w poni�szej tabelce! Je�li wszystko si� zgadza, a nadal wyst�puje ten sam b��d, skontaktuj si� z <a href="gg:4396785">Administratorem</a> stony. <br>Je�li jednak uzna�e� �e pope�ni�e� gdzie� b��d, wpisz sw�j e-mail ponownie.</td></tr><tr><td align="center" colspan="2" align="center" cellSpacing=0 cellPadding=-00 width=700 bgColor=#1c1c1c border=0>
	<form method="post" action="lost.php?step=lostpasswd&amp;action=haslo">
	<table bgColor=#1c1c1c border=0>
	<tr><TD><font class=test>E-Mail:</font></td><td><input type="text" name="email" /></td></tr>
	<tr><td><input type="submit" value="Wy�lij" /></td></tr>
	</table>
	</form>
	</td></tr>
	<tr><TD cellSpacing=0 cellPadding=-00 width=156 bgColor=#1c1c1c>
{/if}
</center>
{if $Action == "haslo"}
    </td></tr></table><TABLE cellSpacing=0 cellPadding=0 width=700 bgColor=#1c1c1c border=0>
        <TR>
          <TD cellSpacing=0 cellPadding=-00 width=700 bgColor=#1c1c1c border=0><center>Mail z has�em zosta� wys�any na podany adres e-mail.</tr></td>
{/if}
<TABLE cellSpacing=0 cellPadding=-00 width=700 bgColor=#1c1c1c border=0>
<tr><td>&nbsp;</td></tr>
</table>
{include file="foot1.tpl"}