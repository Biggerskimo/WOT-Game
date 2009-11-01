<html>
<head>
<title>Phalanx</title>
<link rel="SHORTCUT ICON" href="favicon.ico">
<link rel="stylesheet" type="text/css" href="{$dpath}formate.css" />
<link rel="stylesheet" type="text/css" href="style/lostWorldsGlobal.css" />
<meta http-equiv="content-type" content="text/html; charset=iso8859-2" />
<script language="JavaScript" src="../js/Time.class.js"></script>
<script language="JavaScript" src="../js/overlib.js"></script>
</head>
<body>
<center>
<table width="519">
<tr>
<td colspan="4" class="c">
Aktuelle Flottenbewegungen (Kosten: {$costs} Deuterium)
</td>
</tr>
{foreach from=$fleets item=fleetObj}
{@$fleetObj->view()}
{/foreach}
</table>
</center>