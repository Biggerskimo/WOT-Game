{include file="documentHeader"}
	<head>
		<title>{lang}wot.overview.page.title{/lang}</title>		
		<script type="text/javascript" src="js/Date.format.js"></script>
		<script type="text/javascript" src="js/NTime.class.js"></script>
		<script type="text/javascript" src="js/Overview.class.js"></script>
		<script type="text/javascript" src="js/Tooltip.class.js"></script>
		<script type="text/javascript" src="../js/jQuery.js"></script>
		<script type="text/javascript" src="../js/thickbox.js"></script>
		<script type="text/javascript">
			language = { };
			language["day"] = "{lang}wot.global.date.day{/lang}";
			language["days"] = "{lang}wot.global.date.days{/lang}";
			language["tomorrow"] = "{lang}wot.global.date.tomorrow{/lang}";
			language["theDayAfterTomorrow"] = "{lang}wot.global.date.theDayAfterTomorrow{/lang}";
			language["hideOvent.sure"] = "{lang}wot.overview.ovent.hide.sure{/lang}";
		</script>
		{include file="headInclude"}
		<link href="../css/thickbox.css" type="text/css" rel="stylesheet">
	</head>
	<body>
		<div id="tooltipContainer" class="tooltipContainer"></div>
		{capture append='additionalTopnavContent'}
			<span class="serverTimeDesc">{lang}wot.global.serverTime{/lang}: <span id="serverTime">{@TIME_NOW|time:"%d.%m.%Y, %H:%M:%S"}</span></span>
			
			<span class="overviewOptionsLink"><a class="thickbox" href="index.php?form=OverviewOptions&amp;keepThis=true&amp;TB_iframe=true&amp;height=300&amp;width=600">{lang}wot.overview.options{/lang}</a></span>
		{/capture}
		{include file="topnav"}
		<script type="text/javascript">
			var ovent{@$c} = new NTime(document.getElementById("serverTime").childNodes[0], new Date({@TIME_NOW} * 1000));
		</script>
		<div class="main content overview">
			{* news *}
			{assign var='viewNews' value=0}
			{capture assign='newsStr'}
				{foreach from=$news key='newsID' item='newsItem'}
					{if $newsItem|is_object && !$newsItem->isViewed() && !$newsItem->disabled}
						<div class="newsItem" id="news{@$newsID}">
							<h3 class="newsItemTitle">
								{$newsItem->title}
							</h3>
							<p class="newsItemTime">
								{@$newsItem->time|time}
							</p>
							<p class="newsItemClose">
								<a href="javascript:overview.closeNews({@$newsID})">
									<img src="{$dpath}pic/abort.gif" alt="{lang}wot.overview.news.close{/lang}" />
								</a>
							</p>
							<p class="newsItemText">
								{@$newsItem->text} <a href="{@$newsItem->link}" id="newsLink{@$newsID}">{lang}wot.overview.news.more{/lang}</a>
							</p>
						</div>
						{assign var='viewNews' value=$viewNews+1}
						<script type="text/javascript">
							overview.registerNews({@$newsID});
						</script>
					{/if}
				{/foreach}
			{/capture}
			
			{if $viewNews}
				<fieldset class="news" id="news">
					<legend>
						{lang}wot.overview.news{/lang}
					</legend>
					
				{@$newsStr}
				</fieldset>
			{/if}
			
			{* messages *}
			{if $this->user->new_message}
			<p class="newMessage lwcontainer-1">
				{if $this->user->new_message == 1}
					<a href="../messages.php">{lang}wot.overview.newMessage{/lang}</a>
				{else}
					<a href="../messages.php">{lang}wot.overview.newMessages{/lang}</a>
				{/if}
			</p>
			{/if}
			
			{* ovents *}
			{if $ovents|count}
				{include file='oventList' id='ovents' ovents=$ovents}
			{/if}
			<p class="hiddenOventsLink" id="hiddenOventsLink"{if !$hovents|count} style="display: none;"{/if}>
				<a href="index.php?page=OverviewHiddenOvents&amp;keepThis=true&amp;TB_iframe=true&amp;height=400&amp;width=500" class="thickbox">
					{lang}wot.overview.ovent.showHidden{/lang}
				</a>
			</p>
			
			{* planets *}
			<fieldset class="planetsFieldset">
				<legend>
					{lang}wot.overview.planets{/lang}
				</legend>
				
				<div class="planetThumbnails">
					{* user information *}
					{if !$this->user->getSetting('hideInformation')}
						<div class="userInfo">
							<div class="doubleList lwcontainer-1 accountInfo">
								<div class="doubleDesc">
									{lang}wot.overview.info.user{/lang}
								</div>
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.user.name{/lang}
									</div>
									<div class="doublePart2">
										{$this->user->username}
									</div>
								</div>
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.alliance.alliance{/lang}
									</div>
									<div class="doublePart2">
										{if !$this->user->allianceID}
											<span class="noAlliance">{lang}wot.overview.info.noAlliance{/lang}</span>
										{else}
											<a href="index.php?page=Alliance">[{$this->user->allianceTag}]</a>
										{/if}
									</div>
								</div>
								
								{assign var='stats' value=$this->user->stats}
								{assign var='pointStats' value=$stats.1} {* TODO statTypeID *}
								{assign var='fleetStats' value=$stats.3}
								{assign var='researchStats' value=$stats.5}
								{assign var='attackStats' value=$stats.7}
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.user.points{/lang}
									</div>
									<div class="doublePart2">
										{#$pointStats.points} ({lang}wot.overview.info.pointsRank{/lang})
									</div>
								</div>
								
								{if $fleetStats.points}
									<div class="double">
										<div class="doublePart1">
											{lang}wot.overview.info.fleetPoints{/lang}
										</div>
										<div class="doublePart2">
											{#$fleetStats.points} ({lang}wot.overview.info.fleetRank{/lang})
										</div>
									</div>
								{/if}
								
								{if $researchStats.points}
									<div class="double">
										<div class="doublePart1">
											{lang}wot.overview.info.researchPoints{/lang}
										</div>
										<div class="doublePart2">
											{#$researchStats.points} ({lang}wot.overview.info.researchRank{/lang})
										</div>
									</div>
								{/if}
								
								{if $attackStats.points != 1000}
									<div class="double">
										<div class="doublePart1">
											{lang}wot.overview.info.attackPoints{/lang}
										</div>
										<div class="doublePart2">
											{#$attackStats.points} ({lang}wot.overview.info.attackRank{/lang})
										</div>
									</div>
								{/if}
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.global.dilizium{/lang}
									</div>
									<div class="doublePart2">
										<a href="../dilizium.php">{#$this->user->dilizium+$this->user->additionalDilizium-$this->user->lostDilizium}</a>
									</div>
								</div>
							</div>
							
							<div class="doubleList lwcontainer-1 planetInfo">
								<div class="doubleDesc">
									{lang}wot.overview.info.planet{/lang}
								</div>
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.planet.name{/lang}
									</div>
									<div class="doublePart2">
										{$currentPlanet->name} ({lang}wot.planet.name.change{/lang})
									</div>
								</div>
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.planet.size{/lang}
									</div>
									<div class="doublePart2">
										{lang}wot.overview.info.planetSize{/lang}
									</div>
								</div>
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.planet.temperature{/lang}
									</div>
									<div class="doublePart2">
										{lang}wot.overview.info.planetTemperature{/lang}
									</div>
								</div>
								
								<div class="double">
									<div class="doublePart1">
										{lang}wot.planet.position{/lang}
									</div>
									<div class="doublePart2">
										{include file="planetLink" plPlanet=$currentPlanet noName=1 noPrefix=1}
									</div>
								</div>
								
								{if $currentPlanet->b_building_id && $currentPlanet->b_building > TIME_NOW}
									<div class="double">
										<div class="doublePart1">
											{lang}wot.overview.planet.construction{/lang}
										</div>
										<div class="doublePart2">
											{assign var='buildingID' value=$currentPlanet->b_building_id}
											{lang}wot.spec.spec{@$buildingID}{/lang} {#$currentPlanet->getLevel($buildingID) + 1} (<span id="construction">&nbsp;</span>)
										</div>
										<script type="text/javascript">
											new NTime(document.getElementById("construction").childNodes[0], new Date({@$currentPlanet->b_building - TIME_NOW} * 1000), -1, -1);
										</script>
									</div>
								{/if}
								
								{if $currentPlanet->b_hangar_id != ""}
									<div class="double">
										<div class="doublePart1">
											{lang}wot.overview.planet.hangar{/lang}
										</div>
										<div class="doublePart2">
											{lang}wot.overview.planet.hangar.timeRemaining{/lang}: <span id="hangar">&nbsp;</span>
										</div>
										<script type="text/javascript">
											new NTime(document.getElementById("hangar").childNodes[0], new Date({@$currentPlanet->getProductionHandler()->getProductorObject('hangar')->getOverallTime()} * 1000), -1, -1);
										</script>
									</div>
								{/if}
							</div>
						</div>
					{/if}
					
					{if $this->planet->planetTypeID == 1 && $this->planet->getMoon() != null}
						{assign var='correspondID' value=$this->planet->getMoon()->planetID}
					{else}
						{if $this->planet->planetTypeID == 3 && $this->planet->getPlanet() != null}
							{assign var='correspondID' value=$this->planet->getPlanet()->planetID}
						{else}
							{assign var='correspondID' value=0}
						{/if}
					{/if}
					<div class="current">
						{if $this->planet->planetTypeID == 1 && $this->planet->getMoon() !== null}
							<!-- moon -->
							<div class="correspond{if $this->planet->getMoon()->hostileActivity} attackedPlanet{/if}">
								<span class="planetName">{include file='planetLink' plPlanet=$this->planet->getMoon() noPrefix=1}</span>
								<a href="index.php?page=Overview&amp;cp={@$this->planet->getMoon()->planetID}">
									<img id="correspondImg" src="{$dpath}planeten/small/s_{$this->planet->getMoon()->image}.jpg" alt="" />
								</a>
							</div>
						{/if}
						{if $this->planet->planetTypeID == 3 && $this->planet->getPlanet() !== null}
							<!-- planet -->
							<div class="correspond{if $this->planet->getPlanet()->hostileActivity} attackedPlanet{/if}">
								<span class="planetName">{include file='planetLink' plPlanet=$this->planet->getPlanet() noPrefix=1}</span>
								<a href="index.php?page=Overview&amp;cp={@$this->planet->getPlanet()->planetID}">
									<img id="correspondImg" src="{$dpath}planeten/small/s_{$this->planet->getPlanet()->image}.jpg" alt="" />
								</a>
							</div>
						{/if}
						
						<div class="planet{if $this->planet->hostileActivity} attackedPlanet{/if}">
							<img id="currentPlanet" src="{$dpath}planeten/{$this->planet->image}.jpg" alt="" />
						</div>
					</div>
					
					{if !$this->user->getSetting('hideColonies')}
						<div class="colonies">
							{foreach from=$planets key='planetID' item='planet'}
								{if $planetID != $this->planet->planetID && $planet->planetKind == 1 && $planetID != $correspondID}
									<div class="colony{if $planet->hostileActivity} attackedPlanet{/if}">
										<span class="planetName">{include file='planetLink' plPlanet=$planet noPrefix=1}</span>
										<a href="index.php?page=Overview&amp;cp={@$planetID}">
											<img id="colony{@$planetID}" src="{$dpath}planeten/small/s_{$planet->image}.jpg" alt="" />
										</a>
									</div>
								{/if}
							{/foreach}
						</div>
					{/if}
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
		
		{include file='footer'}
	</body>
</html>