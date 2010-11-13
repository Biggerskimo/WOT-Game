{include file="documentHeader"}
	<head>
		<title>{lang}wot.fleet.start.ships.title{/lang}</title>
		{include file="headInclude"}
		<link rel="stylesheet" type="text/css" href="style/fleet.css" />
		<script type="text/javascript" src="js/Time.class.js"></script>
		<script type="text/javascript" src="js/Fleet.class.js"></script>
		<script type="text/javascript">
			var fleet = new Fleet({@$actualPlanet->galaxy}, {@$actualPlanet->system}, {@$actualPlanet->planet});
			
			var maxSpeed = {@$maxSpeed};
			var speedFactor = {@$speedFactor};
			
			var shipTypes = new Object();
			
			{foreach from=$specs key='specID' item='spec'}
				var shipType = new Object();
				shipType['count'] = {@$spec->level};
				shipType['speed'] = {@$spec->getSpeed()};
				shipType['consumption'] = {@$spec->getConsumption()};
				shipType['capacity'] = {@$spec->capacity};
				
				shipTypes[{@$specID}] = shipType;
			{/foreach}
			
			fleet.shipTypes = shipTypes;
		</script>
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<form action="index.php?form=FleetStartResources" method="post">
				<fieldset>
					<legend>
						{lang}wot.fleet.start.coordinatesMenu{/lang}
					</legend>
					<div class="fleetStartCoordinates">					
						<div class="formElement lwcontainer-{cycle values='1,2'}">
							<div class="formFieldLabel">
								<label for="galaxy">{lang}wot.fleet.end.coordinates{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" id="galaxy" name="galaxy" value="{if $fleetQueue->galaxy}{@$fleetQueue->galaxy}{else}{@$actualPlanet->galaxy}{/if}" size="1" maxlength="1" onmouseup="fleet.shortInfo()" onkeyup="fleet.shortInfo()" />
								:<input type="text" id="system" name="system" value="{if $fleetQueue->system}{@$fleetQueue->system}{else}{@$actualPlanet->system}{/if}" size="3" maxlength="3" onmouseup="fleet.shortInfo()" onkeyup="fleet.shortInfo()" />
								:<input type="text" id="planet" name="planet" value="{if $fleetQueue->planet}{@$fleetQueue->planet}{else}{@$actualPlanet->planet}{/if}" size="2" maxlength="2" onmouseup="fleet.shortInfo()" onkeyup="fleet.shortInfo()" />
								<select id="planetType" name="planetType" onmouseup="fleet.shortInfo()" onkeyup="fleet.shortInfo()">
									<option value="1"{if $fleetQueue->planetType != 2 && $fleetQueue->planetType != 3} selected="selected"{/if}>{lang}wot.global.planet{/lang}</option>
									<option value="2"{if $fleetQueue->planetType == 2} selected="selected"{/if}>{lang}wot.global.debris{/lang}</option>
									<option value="3"{if $fleetQueue->planetType == 3} selected="selected"{/if}>{lang}wot.global.moon{/lang}</option>
								</select>
							</div>
						</div>
						
						<div class="formElement lwcontainer-{cycle values='1,2'}">						
							<div class="formFieldLabel">
								<label for="speed">{lang}wot.fleet.speed{/lang}</label>
							</div> 
							<div class="formField">
								<select id="speed" name="speed" onmouseup="fleet.shortInfo()" onkeyup="fleet.shortInfo()">
									<option value="1" selected="selected">100%</option>
									<option value="0.9">90%</option>
									<option value="0.8">80%</option>
									<option value="0.7">70%</option>
									<option value="0.6">60%</option>
									<option value="0.5">50%</option>
									<option value="0.4">40%</option>
									<option value="0.3">30%</option>
									<option value="0.2">20%</option>
									<option value="0.1">10%</option>
								</select>
							</div>
						</div>
						
						<div class="formElement lwcontainer-{cycle values='1,2'}">						
							<div class="formFieldLabel">
								{lang}wot.fleet.distance{/lang}
							</div>
							<div class="formField">
								<div id="distance">
									-
								</div>
							</div>
						</div>
						
						<div class="formElement lwcontainer-{cycle values='1,2'}">						
							<div class="formFieldLabel">
								{lang}wot.fleet.duration{/lang}
							</div>
							<div class="formField">
								<div id="duration">
									-
								</div>
							</div>
						</div>					
						
						<div class="formElement lwcontainer-{cycle values='1,2'}">
							<div class="formFieldLabel">
								{lang}wot.fleet.durationRe{/lang}
							</div>
							<div class="formField">
								<div id="durationRe">
									<i>browser-cache geleert?</i>
								</div>
							</div>
						</div>
						
						
						<div class="formElement lwcontainer-{cycle values='1,2'}">
							<div class="formFieldLabel">
								{lang}wot.fleet.consumption{/lang}
							</div>
							<div class="formField">
								<div id="consumption">
									-
								</div>
							</div>
						</div>
						
						<div class="formElement lwcontainer-{cycle values='1,2'}">						
							<div class="formFieldLabel">
								{lang}wot.fleet.speed.max{/lang}
							</div>
							<div class="formField">
								<div id="maxSpeed">
									{#$maxSpeed}
								</div>
							</div>
						</div>
						
						<div class="formElement lwcontainer-{cycle values='1,2'}">
							<div class="formFieldLabel">
								{lang}wot.fleet.capacity{/lang}
							</div>
							<div class="formField">
								<div id="storage">
									{#$capacity}
								</div>
							</div>
						</div>					
					</div>
				</fieldset>
				
				{* shortcuts *}
				<fieldset>
					<legend>
						{lang}wot.fleet.start.planetShortcuts{/lang}
					</legend>
					<div class="fleetStartCoordinatesShortcutsPlanets lwcontainer-{cycle values='1,2'}">
						<ul>
							{counter start=-1 skip=1 print=false}
							{foreach from=$planets item='planet'}
								{if $planet->planetID != $actualPlanet->planetID}
									{counter assign='count' print=false}
									<li{* class="mod2_col_{@$count%2} mod2_row_{@$count/2|floor} mod3_col_{@$count%3} mod3_row_{@$count/3|floor} mod4_col_{@$count%4} mod4_row_{@$count/4|floor} mod5_col_{@$count%5} mod5_row_{@$count/5|floor}"*}>
										<a href="javascript:fleet.setTarget({@$planet->galaxy}, {@$planet->system}, {@$planet->planet}, {@$planet->planet_type})">
											{$planet->name} [{@$planet->galaxy}:{@$planet->system}:{@$planet->planet}]
										</a>
									</li>
								{/if}
							{/foreach}
						</ul>
					</div>
				</fieldset>
				
				{* naval formations *}
					{if $navalFormations|count}
					<table class="fleetStartCoordinatesShortcutsNavalFormations">
						<tr>
							<th>
								<div>
									<p>
										{lang}wot.fleet.start.navalFormation.time{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.navalFormation.name{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.navalFormation.target{/lang}
									</p>
								</div>
							</th>
						</tr>
						
						{counter start=-1 skip=1 print=false}
						{foreach from=$navalFormations item='navalFormation'}
							{assign var='planet' value=$navalFormation->getTargetPlanet()}
							{counter assign='count' print=false}
							<tr class="lwcontainer-{cycle values='1,2'}">
								<td class="column">
									<span id="formation{@$navalFormation->formationID}"></span>
									<script type="text/javascript">
										var formation{@$navalFormation->formationID} = new Time('formation{@$navalFormation->formationID}', {@$navalFormation->impactTime - TIME_NOW}, true);
									</script>
								</td>
								
								<td class="column">
									<a href="javascript:fleet.setTarget({@$planet->galaxy}, {@$planet->system}, {@$planet->planet}, {@$planet->planet_type})">
										{$navalFormation->formationName}
									</a>
								</td>
								
								<td class="column">
									<a href="javascript:fleet.setTarget({@$planet->galaxy}, {@$planet->system}, {@$planet->planet}, {@$planet->planet_type})">
										{@$planet->name} [{@$planet->galaxy}:{@$planet->system}:{@$planet->planet}]
									</a>
								</td>
							</tr>
						{/foreach}
					</table>
				{/if}
				
				<script type="text/javascript">
					fleet.shortInfo();
					document.getElementById('galaxy').focus();
				</script>
				
				<div class="formSubmit" style="margin-top: 10px;">
					<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
				</div>
			</form>
		</div>
		
		{include file="footer"}
	</body>
</html>