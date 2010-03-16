<html>
<head>
<title>Spionagebericht</title>
<link rel="SHORTCUT ICON" href="favicon.ico">
<link rel="stylesheet" type="text/css" href="{$dpath}formate.css" />
<meta http-equiv="content-type" content="text/html; charset=iso8859-2" />
</head>
<body>
<center>
{foreach from=$reports item=$report}
	<div>
		{@$report.report}
	</div>
	{if !$simulate|isset}
		{assign var='simulate' value=1}
		<a href="index.php?form=Simulator&amp;planetID={$planetID}" target="Mainframe">Simulieren</a>	
	{/if}
{/foreach}
</center>