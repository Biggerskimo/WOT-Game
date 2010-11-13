{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.diplomacy.title{/lang}</title>
		<script type="text/javascript" src="js/Alliance.class.js"></script>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<div class="diplomacy">
				<fieldset class="allianceConfederations">
					<legend>
						{lang}wot.alliance.diplomacy.confederations{/lang}
					</legend>
					<table class="tableList">
						<thead>
							<tr class="tableHead">
								<th colspan="3">
									<div>
										<p>
											{lang}wot.alliance.name{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.page.leader{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.page.member{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.diplomacy.state{/lang}
										</p>
									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$interrelations key='allianceID2' item='alliance2'}
								{if $alliance2->interrelationType == 1}
									<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
										<td class="column">
											{@$alliance2}
										</td>
										<td class="column">
											{if $alliance2->interrelationState == 1 && $alliance2->ownAlliancePosition == 2}
												<a href="index.php?action=AllianceInterrelation&amp;allianceID={@$alliance->allianceID}&amp;allianceID2={@$allianceID2}&amp;interrelationType=1&amp;interrelationState=3">
													<img src="{$dpath}pic/key.gif" alt="{lang}wot.alliance.diplomacy.agree{/lang}" />
												</a>
											{/if}
										</td>
										<td class="column">
											{if $alliance2->interrelationState == 1 || $alliance2->interrelationState == 3}
												<a href="index.php?action=AllianceInterrelation&amp;allianceID={@$alliance->allianceID}&amp;allianceID2={@$allianceID2}&amp;interrelationType=1&amp;interrelationState=0">
													<img src="{$dpath}pic/abort.gif" alt="{lang}wot.alliance.diplomacy.disagree{/lang}" />
												</a>
											{/if}
										</td>
										<td class="column">
											{$alliance2->getLeader()}
										</td>
										<td class="column">
											{$alliance2->ally_members}
										</td>
										<td class="column">
											{if $alliance2->interrelationState == 1}
												{if $alliance2->ownAlliancePosition == 1}
													<span class="allianceInterrelationWait">
														{lang}wot.alliance.diplomacy.interrelation.wait{/lang}
													</span>
												{else}
													<span class="allianceInterrelationActive">
														<a href="javascript:alliance.showInterrelationInformation({$alliance2->allianceID}, 'block')">
															{lang}wot.alliance.diplomacy.interrelation.view{/lang}
														</a>
													</span>
												{/if}
											{else}
												<span class="allianceInterrelationActive">
													{lang}wot.alliance.diplomacy.interrelation.active{/lang}
												</span>
											{/if}
										</td>
									</tr>
									{if $alliance2->interrelationState == 1 && $alliance2->ownAlliancePosition == 2}
										{cycle values='1,2' name='contcyc' print=false}
										<tr id="interrelation{$alliance2->allianceID}" class="allianceInterrelationApplicationText lwcontainer-{cycle values='1,2' name='contcyc'}">
											<td colspan="6" class="column">
												<p>
													{assign var='data' value=$alliance2->data}
													{assign var='data' value=$this->getLWUtil()->unserialize($data)}
													{assign var='text' value=$data.text}
													{$text}
												</p>
											</td>
										</tr>
									{/if}
								{/if}
							{/foreach}
						</tbody>
					</table>
				</fieldset>
				
				<fieldset class="allianceNonAggressionPacts">
					<legend>
						{lang}wot.alliance.diplomacy.nonAggressionPacts{/lang}
					</legend>
					<table class="tableList">
						<thead>
							<tr class="tableHead">
								<th colspan="3">
									<div>
										<p>
											{lang}wot.alliance.name{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.page.leader{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.page.member{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.diplomacy.state{/lang}
										</p>
									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$interrelations key='allianceID2' item='alliance2'}
								{if $alliance2->interrelationType == 2}
									<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
										<td class="column">
											{@$alliance2}
										</td>
										<td class="column">
											{if $alliance2->interrelationState == 1 && $alliance2->ownAlliancePosition == 2}
												<a href="index.php?action=AllianceInterrelation&amp;allianceID={@$alliance->allianceID}&amp;allianceID2={@$allianceID2}&amp;interrelationType=2&amp;interrelationState=2">
													<img src="{$dpath}pic/key.gif" alt="{lang}wot.alliance.diplomacy.agree{/lang}" />
												</a>
											{/if}
										</td>
										<td class="column">
											{if $alliance2->interrelationState == 1 || $alliance2->interrelationState == 2}
												<a href="index.php?action=AllianceInterrelation&amp;allianceID={@$alliance->allianceID}&amp;allianceID2={@$allianceID2}&amp;interrelationType=2&amp;interrelationState=0">
													<img src="{$dpath}pic/abort.gif" alt="{lang}wot.alliance.diplomacy.disagree{/lang}" />
												</a>
											{/if}
										</td>
										<td class="column">
											{$alliance2->getLeader()}
										</td>
										<td class="column">
											{$alliance2->ally_members}
										</td>
										<td class="column">
											{if $alliance2->interrelationState == 1}
												{if $alliance2->ownAlliancePosition == 1}
													<span class="allianceInterrelationWait">
														{lang}wot.alliance.diplomacy.interrelation.wait{/lang}
													</span>
												{else}
													<span class="allianceInterrelationView">
														{lang}wot.alliance.diplomacy.interrelation.view{/lang}
													</span>
												{/if}
											{else}
												<span class="allianceInterrelationActive">
													{lang}wot.alliance.diplomacy.interrelation.active{/lang}
												</span>
											{/if}
										</td>
									</tr>
									{if $alliance2->interrelationState == 1 && $alliance2->ownAlliancePosition == 2}
										<tr id="interrelation{$alliance2->allianceID}" class="allianceInterrelationApplicationText">
											<td colspan="6">
												<p>
													{assign var='data' value=$alliance2->data}
													{assign var='data' value=$this->getLWUtil()->unserialize($data)}
													{assign var='text' value=$data.text}
													{$text}
												</p>
											</td>
										</tr>
									{/if}
								{/if}
							{/foreach}
						</tbody>
					</table>
				</fieldset>
				
				<fieldset class="allianceEars">
					<legend>
						{lang}wot.alliance.diplomacy.wars{/lang}
					</legend>
					<table class="tableList">
						<thead>
							<tr class="tableHead">
								<th colspan="3">
									<div>
										<p>
											{lang}wot.alliance.name{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.page.leader{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.page.member{/lang}
										</p>
									</div>
								</th>
								<th>
									<div>
										<p>
											{lang}wot.alliance.diplomacy.state{/lang}
										</p>
									</div>
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$interrelations key='allianceID2' item='alliance2'}
								{if $alliance2->interrelationType == 3}
									<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
										<td class="column">
											{@$alliance2}
										</td>
										{*<td class="column">
											{if $alliance2->interrelationState == 1 && $alliance2->ownAlliancePosition == 2}
												<a href="index.php?action=AllianceInterrelation&amp;allianceID={@$alliance->allianceID}&amp;allianceID2={@$allianceID2}&amp;interrelationType=3&amp;interrelationState=2">
													<img src="{$dpath}pic/key.gif" alt="{lang}wot.alliance.diplomacy.agree{/lang}" />
												</a>
											{/if}
										</td>*}
										<td class="column">
											{if $alliance2->interrelationState == 3 && $alliance2->ownAlliancePosition == 1}
												{assign var='abortWar' value=true}
											{else}
												{assign var='abortWar' value=false}
											{/if}
										
											{if $abortWar || $alliance2->interrelationState == 4}
												<a href="index.php?action=AllianceInterrelation&amp;allianceID={@$alliance->allianceID}&amp;allianceID2={@$allianceID2}&amp;interrelationType=3&amp;interrelationState=4">
													<img src="{$dpath}pic/abort.gif" alt="{lang}wot.alliance.diplomacy.war.disagree{/lang}" />
												</a>
											{/if}
										</td>
										<td class="column">
											{$alliance2->getLeader()}
										</td>
										<td class="column">
											{$alliance2->ally_members}
										</td>
										<td class="column">
											<a href="javascript:alliance.showInterrelationInformation({$alliance2->allianceID}, 'block')">
												{if $alliance2->interrelationState == 1}
													<span class="allianceInterrelationView">
														{lang}wot.alliance.diplomacy.interrelation.wait{/lang}
													</span>
												{else}
													{if $alliance2->interrelationState == 4}
														<span class="allianceInterrelationActive">
															{lang}wot.alliance.diplomacy.interrelation.inactive{/lang}
														</span>
													{else}
														<span class="allianceInterrelationActive">
															{lang}wot.alliance.diplomacy.interrelation.active{/lang}
														</span>
													{/if}
												{/if}
											</a>
										</td>
									</tr>
									{if $alliance2->interrelationState == 1 && $alliance2->ownAlliancePosition == 2}
										<tr id="interrelation{$alliance2->allianceID}" class="allianceInterrelationApplicationText">
											<td colspan="6">
												<p>
													{assign var='data' value=$alliance2->data}
													{assign var='data' value=$this->getLWUtil()->unserialize($data)}
													{assign var='text' value=$data.text}
													{$text}
												</p>
											</td>
										</tr>
									{/if}
								{/if}
							{/foreach}
						</tbody>
					</table>
				</fieldset>
			</div>
		</div>
		
		{include file="footer"}
	</body>
</html>