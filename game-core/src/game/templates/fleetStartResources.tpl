{include file="documentHeader"}
	<head>
		<title>{lang}wot.fleet.start.ships.title{/lang}</title>
		{include file="headInclude"}
		<link rel="stylesheet" type="text/css" href="style/fleet.css" />
		<script type="text/javascript" src="js/Fleet.class.js"></script>
		<script type="text/javascript">
			var pageNo = 2;
			var fleet = new Fleet({@$actualPlanet->galaxy}, {@$actualPlanet->system}, {@$actualPlanet->planet});
			
			var capacity = {@$capacity};
			var metal = {@$actualPlanet->metal|floor};
			var crystal = {@$actualPlanet->crystal|floor};
			var deuterium = {@$deuterium|floor};
		</script>
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<form action="index.php?action=FleetStartFire" name="fireForm" id="fireForm" method="post">
				<fieldset>
					<legend>
						{lang}wot.fleet.start.resourcesMenu{/lang}
					</legend>
					<div class="fleetStartResourcesContainer">
						<div class="fleetStartMission">
							<div class="formElement lwcontainer-{cycle values='1,2'}">
								<div class="formFieldLabel">
									<label for="mission">{lang}wot.fleet.mission{/lang}</label>
								</div>
								<div class="formField">
									<select id="mission" name="mission" size="1">
										{foreach from=$missions key='missionID' item='mission'}
											<option value="{@$missionID}"{if $fleetQueue->missionID == $missionID} selected="selected"{/if}>{lang}wot.fleet.mission{@$missionID}{/lang}</option>						
										{/foreach}
									</select>
								</div>
								{if $navalFormations|count}
									<div class="formFieldLabel">
										<label for="formation">{lang}wot.fleet.navalFormation{/lang}</label>
									</div>
									<div class="formField">
										<select id="formation" name="formation" size="1">
											{foreach from=$navalFormations key='formationID' item='formation'}
												<option value="{@$formationID}">{$formation->formationName}</option>						
											{/foreach}
										</select>
									</div>
								{/if}
								{if $missions.12|isset}
									<div class="formFieldLabel">
										<label for="standByTime">{lang}wot.fleet.standBy.time{/lang}</label>
									</div>
									<div class="formField">
										<select id="standByTime" name="standByTime" size="1">
											{foreach from=$availableTimes item='seconds'}
												<option value="{@$seconds}">{$seconds|timediff:'hours'}</option>						
											{/foreach}
										</select>
									</div>							
								{/if}
							</div>					
						</div>
						<div class="fleetStartResources" {* ... *}style="height: 127px;">
							<div class="formElement lwcontainer-{cycle values='1,2'}">						
								<div class="formFieldLabel">
									<label for="metal">{lang}wot.global.metal{/lang}</label>
									<a href="javascript:fleet.maxResource('metal');">max</a>
								</div> 
								<div class="formField">
									<input type="text" id="metal" name="metal" size="10" onkeyup="fleet.calcFreeCapacity();" onmouseup="fleet.calcFreeCapacity();" />
								</div>
							</div>
							
							<div class="formElement lwcontainer-{cycle values='1,2'}">						
								<div class="formFieldLabel">
									<label for="crystal">{lang}wot.global.crystal{/lang}</label>
									<a href="javascript:fleet.maxResource('crystal');">max</a>
								</div> 
								<div class="formField">
									<input type="text" id="crystal" name="crystal" size="10" onkeyup="fleet.calcFreeCapacity();" onmouseup="fleet.calcFreeCapacity();" />
								</div>
							</div>
							
							<div class="formElement lwcontainer-{cycle values='1,2'}">						
								<div class="formFieldLabel">
									<label for="deuterium">{lang}wot.global.deuterium{/lang}</label>
									<a href="javascript:fleet.maxResource('deuterium');">max</a>
								</div> 
								<div class="formField">
									<input type="text" id="deuterium" name="deuterium" size="10" onkeyup="fleet.calcFreeCapacity();" onmouseup="fleet.calcFreeCapacity();" />
								</div>
							</div>
							
							<div class="formElement lwcontainer-{cycle values='1,2'}">						
								<div class="formFieldLabel">
									{lang}wot.fleet.start.rest{/lang}
								</div> 
								<div class="formField">
									<span id="remainingResources" class="positive">{#$capacity}</span>
								</div>
							</div>
							
							<div class="formElement lwcontainer-{cycle values='1,2'}">						
								<div class="formFieldLabel">
									<a href="javascript:fleet.noResources()">{lang}wot.fleet.start.resources.no{/lang}</a>
								</div> 
								<div class="formField">
									<a href="javascript:fleet.maxResources()">{lang}wot.fleet.start.resources.max{/lang}</a>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
				
				<script type="text/javascript">
					document.getElementById('metal').focus();
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