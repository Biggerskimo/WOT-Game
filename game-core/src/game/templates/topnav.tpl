<div id="header_top">
	<div id="topnav">
		<div class="planetDropDown">
			<img src="{$dpath}planeten/small/s_{$actualPlanet->image}.jpg" alt="" />
			<div>
				<select size="1" onchange="eval('location = \'' + this.options[this.selectedIndex].value + '\';');">
					{foreach from=$planets item='planet'}
						<option value="{$site}.php{if !$args|empty}{$args}&amp;cp={@$planet->planetID}{else}?cp={@$planet->planetID}{/if}"{if $actualPlanet->planetID == $planet->planetID} selected="selected"{/if}>{$planet->name} [{@$planet->galaxy}:{@$planet->system}:{@$planet->planet}]</option>
					{/foreach}
				</select>
				{if $additionalTopnavContent|isset}
					<div class="additionalTopnavContent">
						{@$additionalTopnavContent}
					</div>
				{/if}
			</div>
		</div>
	
		<div class="resources">
			<table>
				<tr>
					<td>
						<img src="{$dpath}images/metall.gif" alt="{lang}wot.global.metal{/lang}" />
					</td>
					<td>
						<img src="{$dpath}images/kristall.gif" alt="{lang}wot.global.crystal{/lang}" />
					</td>
					<td>
						<img src="{$dpath}images/deuterium.gif" alt="{lang}wot.global.deuterium{/lang}" />
					</td>
					<td>
						<img src="{$dpath}images/energie.gif" alt="{lang}wot.global.energy{/lang}" />
					</td>
				</tr>
				<tr>
					<td>
						<span class="metalDescription">
							{lang}wot.global.metal{/lang}
						</span>
					</td>
					<td>
						<span class="crystalDescription">
							{lang}wot.global.crystal{/lang}
						</span>
					</td>
	     			<td>
						<span class="deuteriumDescription">
							{lang}wot.global.deuterium{/lang}
						</span>
	     			</td>
					<td>
						<span class="energyDescription">
							{lang}wot.global.energy{/lang}
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<span class="metal">
							{#$actualPlanet->metal|floor}
						</span>
					</td>
					<td>
						<span class="crystal">
							{#$actualPlanet->crystal|floor}
						</span>
					</td>
	     			<td>
						<span class="deuterium">
	     					{#$actualPlanet->deuterium|floor}
						</span>
	     			</td>
					<td>
						<span class="energy">
							{assign var='energy' value=$actualPlanet->getProductionHandler()->getProductorObject('resource')->getProduction('energy')}
							{assign var='firstEnergy' value=$energy.0 - $energy.1}
							{#$firstEnergy*3600|floor} / {#$energy.0*3600|floor}
							{*#$actualPlanet->energy_max-$actualPlanet->energy_used|floor} / {#$actualPlanet->energy_max|floor*}
						</span>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>