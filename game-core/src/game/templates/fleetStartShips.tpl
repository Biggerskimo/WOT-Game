{include file="documentHeader"}
	<head>
		<title>{lang}wot.fleet.start.ships.title{/lang}</title>
		{include file="headInclude"}
		<link rel="stylesheet" type="text/css" href="style/fleet.css" />
		<script type="text/javascript" src="js/Time.class.js"></script>
		<script type="text/javascript" src="js/Fleet.class.js"></script>
		<script type="text/javascript">
			var fleet = new Fleet({@$actualPlanet->galaxy}, {@$actualPlanet->system}, {@$actualPlanet->planet});
			
			var shipTypes = new Object();
						
			{assign var='specs' value=$this->getSpecUtil()->getBySpecTypeID(3)}
			{foreach from=$specs key='specID' item='count'}
				{if $specID != 212}
					shipTypes[{@$specID}] = new Object();
					shipTypes[{@$specID}]['count'] = {@$count};
				{/if}
			{/foreach}
			
			fleet.shipTypes = shipTypes;
			
			var language = new Object();
			language['wot.fleet.start.ships.max'] = '{lang}wot.fleet.start.ships.max{/lang}';
			language['wot.fleet.start.ships.min'] = '{lang}wot.fleet.start.ships.min{/lang}';
		</script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			{* fleets list *}
			<fieldset class="fleetsList">
				<legend>
					{lang}wot.fleet.fleetsList{/lang}
				</legend>
				<table class="tableList">
					<thead>
						<tr>
							<th>
								<div>
									<p> 
										{lang}wot.fleet.no{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.mission{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.shipCount{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.start.coordinates{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.arrivalTime{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.end.coordinates{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.comeBackTime{/lang}
									</p>
								</div>
							</th>
							<th>
								<div>
									<p>
										{lang}wot.fleet.actions{/lang}
									</p>
								</div>
							</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$fleets key='fleetID' item='fleet'}
							<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
								<td class="column">
									{counter}
								</td>
								<td class="column">
									{lang}wot.fleet.mission{$fleet->missionID}{/lang}
								</td>
								<td class="column">
									{#$fleet->fleet|array_sum}
								</td>
								<td class="column">
									{@$fleet->getStartPlanet()->getLinkedCoordinates()}
								</td>
								<td class="column">
									{if $fleet->getCancelDuration() !== false}
										{@$fleet->impactTime|shorttime}
									{else}
										---
									{/if}
								</td>
								<td class="column">
									{@$fleet->getTargetPlanet()->getLinkedCoordinates()}
								</td>
								<td class="column">
									{@$fleet->returnTime|shorttime}
								</td>
								<td class="column">
									{if $fleet->getCancelDuration() !== false}
										<a href="index.php?action=FleetCancel&fleetID={@$fleet->fleetID}" onclick="return confirm('{lang}wot.fleet.cancel.sure{/lang}');"><img src="{$dpath}pic/abort.gif" alt="{lang}wot.fleet.cancel{/lang}"></a>
									{/if}
									<a href="javascript:fleet.showDetails({@$fleet->fleetID})"><img id="fleetDetails{@$fleet->fleetID}Img" src="{@RELATIVE_WCF_DIR}icon/plusS.png" alt="{lang}wot.fleet.start.details{/lang}" /></a>
								</td>
							</tr>
							<tr class="lwcontainer-{cycle values='1,2' name='contcyc'} border fleetDetails" id="fleetDetails{@$fleet->fleetID}" style="display: none;">
								{cycle values='1,2' name='contcyc' print=false}
								<td class="column" colspan="8">
									{* navigation *}
									<div class="fleetInformationActions">
										<ul>
											{if $fleet->getCancelDuration() !== false}
												<li>
													<button onclick="location.href = 'index.php?action=FleetCancel&amp;fleetID={@$fleet->fleetID}'">
														{lang}wot.fleet.cancel{/lang}
													</button>
												</li>										
											{/if}
											{if $fleet->getCancelDuration() !== false && $fleet->navalFormation === null && $fleet->missionID == 1}
												<li>
													<button onclick="location.href = 'index.php?action=FleetNavalFormationCreate&amp;fleetID={@$fleet->fleetID}'">
														{lang}wot.fleet.navalFormation.create{/lang}
													</button>
												</li>
											{/if}
										</ul>
									</div>
									
									{* ships list *}
									<table class="tableList">
										<thead>
											<tr>
												<th class="column">
													{lang}wot.fleet.ship.name{/lang}
												</th>
												<th class="column">
													{lang}wot.fleet.ship.inFleet{/lang}
												</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$fleet->fleet key='specID' item='shipCount'}
												<tr>
													<td class="column">
														{lang}wot.spec.spec{$specID}{/lang}
													</td>
													<td class="column">
														{#$shipCount}
													</td>
													
												</tr>
											{/foreach}
										</tbody>
									</table>
									
									{* other details *}									
									<table class="tableList">
										<thead>
											<tr>
												<th class="column">
													
												</th>
												<th class="column">
													
												</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="column">
													{lang}wot.fleet.durationAfterRecall{/lang}
												</td>
												<td class="column">
													<span id="cancelDuration{@$fleetID}">
														{#$fleet->getCancelDuration()|ceil}
													</span>
													<script type="text/javascript">
														var cancelDuration{@$fleetID} = new Time('cancelDuration{@$fleetID}', {@$fleet->getCancelDuration()|ceil}, true, false, {if $fleet->missionID == 12 && $fleet->wakeUpEventID >= 1 && $fleet->wakeUpTime > TIME_NOW}0{else}-1{/if});
													</script>
												</td>
											</tr>
											<tr>
												<td class="column">
													{lang}wot.fleet.startPlanet{/lang}
												</td>
												<td class="column">
													{@$fleet->getStartPlanet()->getLinkedCoordinates(true)}
												</td>
											</tr>
											<tr>
												<td class="column">
													{lang}wot.fleet.targetPlanet{/lang}
												</td>
												<td class="column">
													{@$fleet->getTargetPlanet()->getLinkedCoordinates(true)}
												</td>
											</tr>
											{if $fleet->getOfiara()->userID}
												<tr>
													<td class="column">
														{lang}wot.fleet.ofiara{/lang}
													</td>
													<td class="column">
														{@$fleet->getOfiara()->getLinkedUsername()}
													</td>
												</tr>
											{/if}
											{if $fleet->metal >= 1}												
												<tr>
													<td class="column">
														{lang}wot.global.metal{/lang}
													</td>
													<td class="column">
														{#$fleet->metal}
													</td>
												</tr>
											{/if}
											{if $fleet->crystal >= 1}												
												<tr>
													<td class="column">
														{lang}wot.global.crystal{/lang}
													</td>
													<td class="column">
														{#$fleet->crystal}
													</td>
												</tr>
											{/if}
											{if $fleet->deuterium >= 1}												
												<tr>
													<td class="column">
														{lang}wot.global.deuterium{/lang}
													</td>
													<td class="column">
														{#$fleet->deuterium}
													</td>
												</tr>
											{/if}
											{if $fleet->navalFormation !== null}											
												<tr>
													<td class="column">
														{lang}wot.fleet.navalFormation{/lang}
													</td>
													<td class="column">
														{if $fleetID == $fleet->navalFormation->leaderFleetID}
															<a href="javascript:void(0)" onclick="var name = escape(prompt('{lang}wot.fleet.navalFormation.name.change{/lang}', unescape('{$fleet->navalFormation->formationName|rawurlencode}'))); if(name == 'undefined') return false; location.href='index.php?action=FleetNavalFormationNameChange&navalFormationID={@$fleet->navalFormation->formationID}&name='+name;">
																{$fleet->navalFormation->formationName}
															</a>
														{else}
															{$fleet->navalFormation->formationName}
														{/if}
													</td>
												</tr>
											{/if}
										</tbody>
									</table>
									
									{if $fleet->navalFormation !== null && $fleetID == $fleet->navalFormation->leaderFleetID}
										{assign var='navalFormation' value=$fleet->navalFormation}
										
										{* user list *}
										<form class="navalFormationUsers" action="index.php?action=FleetNavalFormationUserAdd&navalFormationID={@$navalFormation->formationID}" method="post">
											<table class="tableList">
												<thead>
													<tr>
														<th>
															{lang}wot.user.username{/lang}
														</th>
														<th>
															{lang}wot.fleet.navalFormation.joinTime{/lang}
														</th>
														<th>
															-
														</th>
													</tr>
												</thead>
												<tbody>
													{assign var='users' value=$navalFormation->users}
													{foreach from=$users key='userID' item='user'}
														<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
															<td class="column">
																{$user->username}
															</td>
															<td class="column">
																{@$user->joinTime|time}
															</td>
															<td class="column">
																{if $navalFormation->checkUsers($userID)}
																	<a href="index.php?action=FleetNavalFormationUserDelete&navalFormationID={@$navalFormation->formationID}&userID={@$userID}">
																		<img src="{$dpath}pic/abort.gif" alt="{lang}wot.alliance.user.kick{/lang}" />
																	</a>
																{/if}
															</td>
														</tr>
													{/foreach}
													{if !$navalFormation->usersLimitReached()}
														<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
															<td class="column">
																<input type="text" name="username" id="nfsUserAdd{@$navalFormation->formationID}" class="inputText" size="15" maxlength="25" />
																<script type="text/javascript">
																	//<![CDATA[
																		suggestion.enableMultiple(false);
																		suggestion.setSource('index.php?page=PublicUserSuggest'+SID_ARG_2ND);
																		suggestion.init('nfsUserAdd{@$navalFormation->formationID}');
																	//]]>
																</script>
															</td>
															<td class="column" colspan="2">
																<input type="submit" value="{lang}wot.fleet.navalFormation.addUser{/lang}" />
															</td>
														</tr>
													{/if}
												</tbody>
											</table>
										</form>
									{/if}	
								</td>
							</tr>
						{/foreach}
						
						{if !$fleets|count}
							<tr>
								<td class="column" colspan="8">
									{lang}wot.fleet.start.noFleets{/lang}
								</td>
							</tr>						
						{/if}
					</tbody>
				</table>
			</fieldset>
			
			{* fleet start *}
			<fieldset class="fleetsStart">
				<legend>
					{lang}wot.fleet.fleetStart{/lang}
				</legend>
				<form class="navalFormationNameChange" action="index.php?form=FleetStartCoordinates" method="post" name="fleet" id="fleet">
					<table class="tableList">
						<thead>
							<tr>
								<th>
									<div>
										<p>
											{lang}wot.fleet.ship.name{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.fleet.ship.available{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											<a href="javascript:fleet.maxShips()" id="generalShipCountsLink">{lang}wot.fleet.start.ships.max{/lang}</a>
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											-
										</p>
									</div> 
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$specs key='specID' item='count'}
								<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
									<td class="column">
										{assign var='langVarName' value='wot.spec.spec'|concat:$specID} 
										{$this->getLanguage()->get($langVarName)}
									</td>
									<td class="column">
										{#$count}
									</td>
									{if $specID != 212}
										<td class="column">
											<a href="javascript:fleet.maxShip({@$specID})">max</a>
										</td>
										<td class="column">
											<input type="text" name="ship{@$specID}" size="10" value="0" onfocus="javascript:if(this.value == '0') this.value='';" onblur="javascript:if(this.value == '') this.value='0';" />
										</td>
									{else}
										<td class="column">
											-
										</td>
										<td class="column">
											-
										</td>									
									{/if}
								</tr>
							{/foreach}
						</tbody>
					</table>
					<div class="formSubmit">
						<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
						<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
						{if $actualPlanet->quantic_jump > 0}
							<input type="submit" value="{lang}wot.fleet.start.galacticJump.submit{/lang}" onclick="document.fleet.action = 'index.php?page=GalacticJump';" />
						{/if}
					</div>
				</form>
			</fieldset>
		</div>
	</body>
</html>