{include file="documentHeader"}
	<head>
		<title>{lang}wot.overview.page.title{/lang}</title>		
		<script type="text/javascript" src="js/Date.format.js"></script>
		<script type="text/javascript" src="js/NTime.class.js"></script>
		<script type="text/javascript" src="js/Time.class.js"></script>
		<script type="text/javascript" src="js/Tooltip.class.js"></script>
		<script type="text/javascript">
			language = { };
			language["day"] = "{lang}wot.global.date.day{/lang}";
			language["days"] = "{lang}wot.global.date.days{/lang}";
			language["tomorrow"] = "{lang}wot.global.date.tomorrow{/lang}";
			language["theDayAfterTomorrow"] = "{lang}wot.global.date.theDayAfterTomorrow{/lang}";
		</script>
		{include file="headInclude"}
	</head>
	<body>
		{capture assign='additionalTopnavContent'}
			<span class="serverTimeDesc">{lang}wot.global.serverTime{/lang}: <span id="serverTime">{@TIME_NOW|time:"%d.%m.%Y, %H:%M:%S"}</span></span>
			
			<script type="text/javascript">
				var ovent{@$c} = new NTime(document.getElementById("serverTime").childNodes[0]);
			</script>
		{/capture}
		{include file="topnav"}
		<div class="main content overview">
			{* ovents *}
			{if $ovents|count}
				<table>
					<thead>
						<tr>
							<th>
								{lang}wot.ovent.time{/lang}
							</th>
							<th>
								{lang}wot.ovent.ovent{/lang}
							</th>
							<th>
								{lang}wot.ovent.extended{/lang}
							</th>
						</tr>
					</thead>
					<tbody>
						{counter assign='c' print=false}
						{foreach from=$ovents item='ovent'}
							{counter print=false}
							<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
								<td>
									<div>
										<div id="relativeTime{@$c}" class="relativeTime">&nbsp;</div>
										<div id="absoluteTime{@$c}" class="absoluteTime">&nbsp;</div>
									</div>
									<script type="text/javascript">
										new NTime(document.getElementById("relativeTime{@$c}").childNodes[0], new Date({$ovent->time - TIME_NOW} * 1000), -1, -1);
										new NTime(document.getElementById("absoluteTime{@$c}").childNodes[0], new Date({$ovent->time - TIME_NOW} * 1000), 0, -2);
									</script>
								</td>
								<td>
									{include file=$ovent->getTemplateName()}
								</td>
								<td>
									<input type="checkbox"{if $ovent->checked} checked="checked"{/if} />
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			{/if}
			
			{* planets *}
			<fieldset>
				<legend>
					{lang}wot.overview.planets{/lang}
				</legend>
				
				<div class="planetThumbnails">
					{if $this->planet->planetTypeID == 1 && $this->planet->getMoon() != null}
						{assign var='correspondID' value=$this->planet->getMoon()->planetID}
					{else}
						{if $this->planet->planetTypeID == 3 && $this->planet->getPlanet() != null}
							{assign var='correspondID' value=$this->planet->getPlanet()->planetID}
						{else}
							{assign var='correspondID' value=0}
						{/if}
					{/if}
					<div class="colonies">
						{foreach from=$planets key='planetID' item='planet'}
							{if $planetID != $this->planet->planetID && $planet->planetKind == 1 && $planetID != $correspondID}
								<div class="colony{if $planet->hostileActivity} attackedPlanet{/if}">
									<span class="planetName">{include file='planetLink' plPlanet=$planet}</span>
									<a href="index.php?page=Overview&amp;cp={@$planetID}">
										<img id="colony{@$planetID}" src="{$dpath}planeten/small/s_{$planet->image}.jpg" alt="" />
									</a>
									{include file='overviewPlanetTooltip' planet=$planet id='colony'|concat:$planetID}							
								</div>							
							{/if}
						{/foreach}
					</div>
					
					<div class="current">
						{if $this->planet->planetTypeID == 1 && $this->planet->getMoon() !== null}
							<!-- moon -->
							<div class="correspond{if $this->planet->getMoon()->hostileActivity} attackedPlanet{/if}">
								<span class="planetName">{include file='planetLink' plPlanet=$this->planet->getMoon()}</span>
								<a href="index.php?page=Overview&amp;cp={@$this->planet->getMoon()->planetID}">
									<img id="correspondImg" src="{$dpath}planeten/small/s_{$this->planet->getMoon()->image}.jpg" alt="" />
								</a>
								
								{include file='overviewPlanetTooltip' planet=$this->planet->getMoon() id='correspondImg'}
							</div>
						{/if}
						{if $this->planet->planetTypeID == 3 && $this->planet->getPlanet() !== null}
							<!-- planet -->
							<div class="correspond{if $this->planet->getPlanet()->hostileActivity} attackedPlanet{/if}">
								<span class="planetName">{include file='planetLink' plPlanet=$this->planet->getPlanet()}</span>
								<a href="index.php?page=Overview&amp;cp={@$this->planet->getPlanet()->planetID}">
									<img id="correspondImg" src="{$dpath}planeten/small/s_{$this->planet->getPlanet()->image}.jpg" alt="" />
								</a>
								
								{include file='overviewPlanetTooltip' planet=$this->planet->getPlanet() id='correspondImg'}
							</div>
						{/if}
						
						<div class="planet{if $this->planet->hostileActivity} attackedPlanet{/if}">
							<img id="currentPlanet" src="{$dpath}planeten/{$this->planet->image}.jpg" alt="" />
							
							{include file='overviewPlanetTooltip' planet=$this->planet id='currentPlanet' main=true}
						</div>
					</div>
				</div>
			</fieldset>
			
			{* resource overview *}
			{if $fleetOverview !== null}
				{assign var='resources' value=$fleetOverview->getOverall()}
				{assign var='totalResources' value=$resources.metal + $resources.crystal + $resources.deuterium}
				{if $fleetOverview->getOverallCount() && $totalResources > 0}
					<div class="resourcesOverview">
						<table>
							<thead>
								<tr>
									<th>
										{lang}wot.overview.resourcesOverview{/lang}
									</th>
									<th>
										{lang}wot.overview.resourcesOverview.fleetCount{/lang}
									</th>
									<th>
										{lang}wot.global.metal{/lang}
									</th>
									<th>
										{lang}wot.global.crystal{/lang}
									</th>
									<th>
										{lang}wot.global.deuterium{/lang}
									</th>
								</tr>
							</thead>
							
							<tbody>
								<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
									<td>
										{lang}wot.overview.resourcesOverview.total{/lang}
									</td>
									<td>
										{#$fleetOverview->getOverallCount()}
									</td>
									<td>
										{#$resources.metal}
									</td>
									<td>
										{#$resources.crystal}
									</td>
									<td>
										{#$resources.deuterium}
									</td>							
								</tr>
								
								{foreach from=$fleetOverview->getMissions() key='missionID' item='resources'}
									{assign var='totalResources' value=$resources.metal + $resources.crystal + $resources.deuterium}
									{if $totalResources}
										<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
											<td>
												{lang}wot.mission.mission{@$missionID}{/lang}
											</td>
											<td>
												{#$fleetOverview->getMissionCount($missionID)}
											</td>
											<td>
												{#$resources.metal}
											</td>
											<td>
												{#$resources.crystal}
											</td>
											<td>
												{#$resources.deuterium}
											</td>							
										</tr>
									{/if}
								{/foreach}							
							</tbody>
						</table>
					</div>
				{/if}
			{/if}
		</div>
	</body>
</html>