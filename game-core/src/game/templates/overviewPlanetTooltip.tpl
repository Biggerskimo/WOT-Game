<div id="planetTooltip{@$id}" class="tooltip planetTooltip">
	<div class="planetDesc">
		{$planet->name} [{$planet->galaxy}:{$planet->system}:{$planet->planet}] {if $main|isset && $main}(<a href="index.php?form=PlanetActions">{lang}wot.overview.planet.change{/lang}</a>){/if}
	</div>
	
	<div class="double">
		<div class="doublePart1">
			{lang}wot.overview.planet.size{/lang}
		</div>
		<div class="doublePart2">
			{#$planet->diameter} ({#$planet->getUsedFields()} / {#$planet->getMaxFields()} {lang}wot.overview.planet.fields{/lang})
		</div>
	</div>
	
	<div class="double metal">
		<div class="doublePart1">
			{lang}wot.global.metal{/lang}
		</div>
		<div class="doublePart2">
			{#$planet->metal|floor}
		</div>
	</div>
	
	<div class="double crystal">
		<div class="doublePart1">
			{lang}wot.global.crystal{/lang}
		</div>
		<div class="doublePart2">
			{#$planet->crystal|floor}
		</div>
	</div>
	
	<div class="double deuterium">
		<div class="doublePart1">
			{lang}wot.global.deuterium{/lang}
		</div>
		<div class="doublePart2">
			{#$planet->deuterium|floor}
		</div>
	</div>
	
	{if	$planet->activity}
	<div class="double">
		<div class="doublePart1">
			{lang}wot.overview.planet.fleets{/lang}
		</div>
		<div class="doublePart2">
			{#$planet->activity}{if $planet->hostileActivity}<span class="hostileActivity">({#$planet->hostileActivity} {lang}wot.overview.planet.fleets.hostile{/lang}){/if}</span>
		</div>
	</div>
	{/if}
	
	{if $planet->b_building_id && $planet->b_building > TIME_NOW}
		<div class="double">
			<div class="doublePart1">
				{lang}wot.overview.planet.construction{/lang}
			</div>
			<div class="doublePart2">
				{assign var='buildingID' value=$planet->b_building_id}
				{lang}wot.spec.spec{@$buildingID}{/lang} {#$planet->getLevel($buildingID) + 1} (<span id="construction{@$id}">&nbsp;</span>)
			</div>
		</div>
		<script type="text/javascript">
			new NTime(document.getElementById("construction{@$id}").childNodes[0], new Date({@$planet->b_building - TIME_NOW} * 1000), -1, -1);
		</script>
	{/if}
	
	{if $planet->b_hangar_id != ""}
		<div class="double">
			<div class="doublePart1">
				{lang}wot.overview.planet.hangar{/lang}
			</div>
			<div class="doublePart2">
				{lang}wot.overview.planet.hangar.timeRemaining{/lang}: <span id="hangar{@$id}">&nbsp;</span>
			</div>
		</div>
		<script type="text/javascript">
			new NTime(document.getElementById("hangar{@$id}").childNodes[0], new Date({@$planet->getProductionHandler()->getProductorObject('hangar')->getOverallTime()} * 1000), -1, -1);
		</script>
	{/if}
</div>
<script type="text/javascript">
	new Tooltip(document.getElementById("planetTooltip{@$id}"), document.getElementById("{@$id}"));
</script>