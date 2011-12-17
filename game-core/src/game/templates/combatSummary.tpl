<div class="combat {if $winner == 'attacker'}attackerWin{else}{if $winner == 'defender'}defenderWin{else}draw{/if}{/if}">
	{if $winner == 'attacker'}
		<p class="oneLine">Der Angreifer hat die Schlacht gewonnen!</p>
		<br />
		{* list booty *}
		<p class="booty">
		<span>Er erbeutet</span>
		<br />
		<span>{#$booty.metal|round} Metall, {#$booty.crystal|round} Kristall und {#$booty.deuterium|round} Deuterium.</span></p>
	{else}
		{if $winner == 'defender'}
			<p class="oneLine">Der Verteidiger hat die Schlacht gewonnen!</p>
		{else} {* draw *}
			<p class="oneLine">Die Schlacht endet unentschieden, beide Flotten ziehen sich auf ihre Heimatplaneten zur&uuml;ck.</p>
		{/if}
	{/if}
	<br />
	<p class="attackerUnits">Der Angreifer hat insgesamt {$units.attacker|number_format:0:',':'.'} Units verloren.</p>
	<p class="defenderUnits">Der Verteidiger hat insgesamt {$units.defender|number_format:0:',':'.'} Units verloren.</p>
	<br />
	<p class="debris">Auf diesen Raumkoordinaten liegen nun {$debris.metal|number_format:0:',':'.'} Metall und {$debris.crystal|number_format:0:',':'.'} Kristall.</p>
	<br />
	{if $moon.chance !== 0}
		<p class="moonChance">Die Chance einer Mondentstehung betr&auml;gt {$moon.chance}%.</p>
		{if $moon.size !== null}
			<br />
			<p class="moonCreation">Die enormen Mengen an freiem Metall und Kristall ziehen sich an und formen einen Trabanten um den Planeten.</p>
		{/if}
	{/if}
</div>