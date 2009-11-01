<html>
<head>
<title>Simulator</title>
<link rel="SHORTCUT ICON" href="favicon.ico">
<link rel="stylesheet" type="text/css" href="{$dpath}formate.css" />
<link rel="stylesheet" type="text/css" href="../css/thickbox.css" />
<meta http-equiv="content-type" content="text/html; charset=iso8859-2" />
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/StringUtil.class.js"></script>
<script type="text/javascript" src="../js/LWUtil.class.js"></script>
<script type="text/javascript" src="../js/Simulator.class.js"></script>
<script type="text/javascript" src="../js/jQuery.js"></script>
<script type="text/javascript" src="../js/thickbox.js"></script>
<style type="text/css">
	td {
		padding: 2px;
	}
</style>
</head>
<body>
<center>
	<form action="index.php?form=Simulator" method="post" name="simulatorForm">
		<table width="519">
			<tr>
				<td rowspan="4">
					<fieldset>
						<legend>
							Flotte
						</legend>
						<div class="nfsSlotNo">
							Flottenslot:
							<input type="button" value="&lt;"  onMouseUp="simulator.nfsSlotNoDown()" onKeyUp="simulator.nfsSlotNoDown()" />
							<input type="text" size="2" maxlength="2" id="nfsSlotNo" name="nfsSlotNo" value="1" onMouseUp="simulator.changeNfsSlotNo()" onKeyUp="simulator.changeNfsSlotNo()" />
							<input type="button" value="&gt;" onMouseUp="simulator.nfsSlotNoUp()" onKeyUp="simulator.nfsSlotNoUp()" />
							<hr />
						</div>
						<table>
							<tr>
								<td>
									Typ
								</td>
								<td>
									Angreifer
								</td>
								<td>
									Verteidiger
								</th>
							</tr>
							{assign var='fleet' value='1'}
							{foreach from=$resource key='id' item='name'}
								{if $id >= 200 && $id < 500}
									{if $id >= 400 && $fleet == 1}
										{assign var='fleet' value='0'}
										<tr>
											<td>
												&nbsp;
											</td>
											<td>
												&nbsp;
											</td>
											<td>
												&nbsp;
											</th>
										</tr>
									{/if}
									<tr>
										<td>
											{@$shipTypeNames.$id}
										</td>
										{if $fleet == 1}
											<td>
												<input type="text" id="shipTypeIDAttacker{$id}" name="shipDataAttacker{$id}" size="5" maxlength="20" />
											</td>
										{else}
											<td>
												<center>
													-
												</center>
											</td>
										{/if}
										<td>
											<input type="text" id="shipTypeIDDefender{$id}" name="shipDataDefender{$id}" size="5" maxlength="20" />
										</td>
									</tr>
								{/if}
							{/foreach}
						</table>
					</fieldset>
				</td>
				<td>
					<fieldset>
						<legend>
							Optionen
						</legend>
						<table>
							<tr>
								<td>
									Technik
								</td>
								<td>
									Angreifer
								</td>
								<td>
									Verteidiger
								</td>
							</tr>
							<tr>
								<td>
									Waffentechnik
								</td>
								<td>
									<input type="text" name="shipDataAttackerTech109" size="2" maxlength="2" />
								</td>
								<td>
									<input type="text" name="shipDataDefenderTech109" size="2" maxlength="2" />
								</td>
							</tr>
							<tr>
								<td>
									Schildtechnik
								</td>
								<td>
									<input type="text" name="shipDataAttackerTech110" size="2" maxlength="2" />
								</td>
								<td>
									<input type="text" name="shipDataDefenderTech110" size="2" maxlength="2" />
								</td>
							</tr>
							<tr>
								<td>
									Raumschiffpanzerung
								</td>
								<td>
									<input type="text" name="shipDataAttackerTech111" size="2" maxlength="2" />
								</td>
								<td>
									<input type="text" name="shipDataDefenderTech111" size="2" maxlength="2" />
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td>
					<fieldset>
						<legend>
							Ergebnis (Durchschnitt)
						</legend>
						<table>
							<tr>
								<td>
									Sieger
								</td>
								<td>
								{foreach from=$winner key='winnerName' item='count'}
									{if $winnerO|isset}
										||
									{/if}
									{assign var='winnerO' value=$winnerName}
									
									{if $winnerName == 'attacker'}
										Angreifer ({$count}%)
									{else}
										{if $winnerName == 'defender'}
											Verteidiger ({$count}%)
										{else}
											Unentschieden ({$count}%)
										{/if}
									{/if}
								{/foreach}
								</td>
							</tr>
							<tr>
								<td>
									Verluste
								</td>
								<td>
									Angreifer: ~{$units.attacker|number_format:0:',':'.'} Units,
									Verteidiger: ~{$units.defender|number_format:0:',':'.'} Units
								</td>
							</tr>
							<tr>
								<td>
									Tr&uuml;mmerfeld
								</td>
								<td>
									~{$debris.metal|number_format:0:',':'.'} Metall und
									~{$debris.crystal|number_format:0:',':'.'} Kristall
								</td>
							</tr>
							<tr>
								<td>
									Abbau
								</td>
								<td>
									{assign var='resources' value=$debris|array_sum}
									{assign var='recycler' value=$resources/20000}
									~{$recycler|number_format:0:',':'.'} Recycler
								</td>
							</tr>
							<tr>
								<td>
									Beute
								</td>
								<td>
									~{$booty|number_format:0:',':'.'} Ressourcen (gesamt)
								</td>
							</tr>
							<tr>
								<td>
									Kampfbericht
								</td>
								<td>								
									<a class="thickbox" href="index.php?page=CombatReportView&reportID={$reportID}&amp;keepThis=true&amp;TB_iframe=true&amp;height=400&amp;width=500">Klick</a>
								</td>
							</tr>
						</table>
					</fieldset>
					<br />
					<fieldset>
						<legend>
							Ergebnis (Best-Case Angreifer)
						</legend>
						<table>
							<tr>
								<td>
									Sieger
								</td>
								<td>
								{if $winner.attacker|isset}
									Angreifer
								{else}
									{if $winner.draw|isset}
										Unentschieden
									{else}
										Verteidiger
									{/if}
								{/if}
								</td>
							</tr>
							<tr>
								<td>
									Verluste
								</td>
								<td>
									Angreifer: ~{$minUnits.attacker|number_format:0:',':'.'} Units,
									Verteidiger: ~{$minUnits.defender|number_format:0:',':'.'} Units
								</td>
							</tr>
							<tr>
								<td>
									Tr&uuml;mmerfeld
								</td>
								<td>
									~{$minDebris.metal|number_format:0:',':'.'} Metall und
									~{$minDebris.crystal|number_format:0:',':'.'} Kristall
								</td>
							</tr>
							<tr>
								<td>
									Abbau
								</td>
								<td>
									{assign var='resources' value=$minDebris|array_sum}
									{assign var='recycler' value=$resources/20000}
									~{$recycler|number_format:0:',':'.'} Recycler
								</td>
							</tr>
							<tr>
								<td>
									Beute
								</td>
								<td>
									~{$minBooty|number_format:0:',':'.'} Ressourcen (gesamt)
								</td>
							</tr>
						</table>
					</fieldset><br />
					<fieldset>
						<legend>
							Ergebnis (Worst-Case Angreifer)
						</legend>
						<table>
							<tr>
								<td>
									Sieger
								</td>
								<td>
								{if $winner.defender|isset}
									Verteidiger
								{else}
									{if $winner.draw|isset}
										Unentschieden
									{else}
										Verteidiger
									{/if}
								{/if}
								</td>
							</tr>
							<tr>
								<td>
									Verluste
								</td>
								<td>
									Angreifer: ~{$maxUnits.attacker|number_format:0:',':'.'} Units,
									Verteidiger: ~{$maxUnits.defender|number_format:0:',':'.'} Units
								</td>
							</tr>
							<tr>
								<td>
									Tr&uuml;mmerfeld
								</td>
								<td>
									~{$maxDebris.metal|number_format:0:',':'.'} Metall und
									~{$maxDebris.crystal|number_format:0:',':'.'} Kristall
								</td>
							</tr>
							<tr>
								<td>
									Abbau
								</td>
								<td>
									{assign var='resources' value=$maxDebris|array_sum}
									{assign var='recycler' value=$resources/20000}
									~{$recycler|number_format:0:',':'.'} Recycler
								</td>
							</tr>
							<tr>
								<td>
									Beute
								</td>
								<td>
									~{$maxBooty|number_format:0:',':'.'} Ressourcen (gesamt)
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			{*
			<tr>
				<td>
					<fieldset>
						<legend>
							Einlesen
						</legend>
						<textarea cols="20" rows="1" id="scanInput"></textarea>
						<input type="button" value="Einlesen" onMouseUp="simulator.readData()" onKeyUp="simulator.readData()" />
						<input type="button" value="X" onMouseUp="document.getElementById('scanInput').value = '';" onKeyUp="document.getElementById('scanInput').value = '';" />
					</fieldset>
				</td>
			</tr>*}
			<tr>
				<td>
					<fieldset>
						<legend>
							Aktionen
						</legend>
						<input type="hidden" name="shipData" value="" />
						<input type="reset" value="Zur&uuml;cksetzen" onMouseUp="simulator.reset()" onKeyUp="simulator.reset()" />
						<input type="submit" value="Abschicken" onMouseUp="simulator.submit()" onKeyUp="simulator.submit()" />
					</fieldset>
				</td>
			</tr>
		</table>
	</form>
</center>
<script type="text/javascript">
	{if $attackerFleets|count || $defenderFleets|count}
		var tmpShipData = new Object();
		
		{* attacker fleet *}
		{foreach from=$attackerFleets key='nfsSlotNo' item='value'}
			{if $nfsSlotNo < 10}
				{assign var='nfsSlotNo' value='0'|concat:$nfsSlotNo}
			{/if}
			
			
			{if $value.fleet|isset}
				{foreach from=$value.fleet key='shipTypeID' item='shipCount'}
					tmpShipData['shipData{@$nfsSlotNo}Attacker{@$shipTypeID}'] = {@$shipCount};
				{/foreach}
			{/if}
			
			{if $value.tech|isset}
				{foreach from=$value.tech key='techTypeID' item='techLevel'}
					tmpShipData['shipData{@$nfsSlotNo}AttackerTech{@$techTypeID}'] = {@$techLevel};
				{/foreach}
			{/if}
		{/foreach}
		
		{* defender fleets *}
		{foreach from=$defenderFleets key='nfsSlotNo' item='value'}
			{if $nfsSlotNo < 10}
				{assign var='nfsSlotNo' value='0'|concat:$nfsSlotNo}
			{/if}
			
			
			{if $value.fleet|isset}
				{foreach from=$value.fleet key='shipTypeID' item='shipCount'}
					tmpShipData['shipData{@$nfsSlotNo}Defender{@$shipTypeID}'] = {@$shipCount};
				{/foreach}
			{/if}
			
			{if $value.tech|isset}
				{foreach from=$value.tech key='techTypeID' item='techLevel'}
					tmpShipData['shipData{@$nfsSlotNo}DefenderTech{@$techTypeID}'] = {@$techLevel};
				{/foreach}
			{/if}
		{/foreach}
		
		simulator.storeShipData(tmpShipData);
	{/if}
	
	{* language *}
	{foreach from=$shipTypeNames key='shipTypeID' item='shipTypeName'}
		simulator.language[{$shipTypeID}] = '{@$shipTypeName|html_entity_decode}';
	{/foreach}
</script>