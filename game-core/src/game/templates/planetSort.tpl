<html>
<head>
<title>Planeten sortieren</title>
<link rel="SHORTCUT ICON" href="favicon.ico">
<link rel="stylesheet" type="text/css" href="{$dpath}formate.css" />
<link rel="stylesheet" type="text/css" href="../css/thickbox.css" />
<meta http-equiv="content-type" content="text/html; charset=iso8859-2" />
</head>
<body>
<center>
	<form action="index.php?form=PlanetSort" method="post" name="simulatorForm">
		<table width="519">
			<tr>
				<td class="c">
					Planet
				</td>
				<td class="c">
					Position
				</td>
			</tr>				
			{assign var='planetCount' value=$planets|count}
			{foreach from=$planets item=$planet}
				<tr>
					<td class="l">
						{*
						{$planet->name} [{$planet->galaxy}:{$planet->system}:{$planet->planet}]
						*}
						{@$planet}
					</td>
					<td class="l">
						<select size="1" name="planet{$planet->planetID}">
							{section loop=$planetCount name='i'}
								<option value="{$i}"{if $planet->sortID == $i} selected="selected"{/if}>{$i + 1}&nbsp;&nbsp;</option>
							{/section}
						</select>
					</td>
				</tr>
			{/foreach}
			<tr>
				<th colspan="2">
					<input type="submit" value="Abschicken" />
				</th>
			</tr>
		</table>
	</form>
</center>