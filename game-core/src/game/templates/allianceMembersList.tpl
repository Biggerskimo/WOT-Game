{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.membersList.title{/lang}</title>
		<script type="text/javascript" src="js/Alliance.class.js"></script>
		<script type="text/javascript">
			var allianceID = {@$alliance->allianceID};
			
			var language = new Object();
			language['changeFounder.sure'] = '{lang}wot.alliance.changeFounder.sure{/lang}';
		</script>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th colspan="3"{if $sortField == 'username'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=AllianceMembersList&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'DESC'}ASC{else}DESC{/if}">
										{lang}wot.user.username{/lang}
										{if $sortField == 'username'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'ally_rank_id'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=AllianceMembersList&amp;pageNo={@$pageNo}&amp;sortField=ally_rank_id&amp;sortOrder={if $sortField == 'ally_rank_id' && $sortOrder == 'DESC'}ASC{else}DESC{/if}">
										{lang}wot.alliance.rank{/lang}
										{if $sortField == 'ally_rank_id'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'points'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=AllianceMembersList&amp;pageNo={@$pageNo}&amp;sortField=points&amp;sortOrder={if $sortField == 'points' && $sortOrder == 'DESC'}ASC{else}DESC{/if}">
										{lang}wot.user.points{/lang}
										{if $sortField == 'points'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'coordinates'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=AllianceMembersList&amp;pageNo={@$pageNo}&amp;sortField=coordinates&amp;sortOrder={if $sortField == 'coordinates' && $sortOrder == 'DESC'}ASC{else}DESC{/if}">
										{lang}wot.global.coordinates{/lang}
										{if $sortField == 'coordinates'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'ally_register_time'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=AllianceMembersList&amp;pageNo={@$pageNo}&amp;sortField=ally_register_time&amp;sortOrder={if $sortField == 'ally_register_time' && $sortOrder == 'DESC'}ASC{else}DESC{/if}">
										{lang}wot.alliance.joinTime{/lang}
										{if $sortField == 'ally_register_time'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						{if $alliance->getRank(true, 7)}
							<th{if $sortField == 'onlinetime'} class="active"{/if}>
								<div>
									<p>
										<a href="index.php?page=AllianceMembersList&amp;pageNo={@$pageNo}&amp;sortField=onlinetime&amp;sortOrder={if $sortField == 'onlinetime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}">
											{lang}wot.user.online{/lang}
											{if $sortField == 'onlinetime'}
												<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
											{/if}
										</a>
									</p>
								</div>
							</th>
						{/if}
					</tr>
				</thead>
				<tbody>
					{foreach from=$users item='user'}
						<tr class="container-{cycle values='1, 2' name='contcyc'}">
							<td class="column">
								{$user->username}
							</td>
							<td class="column">
								<a href="../messages.php?mode=write&amp;id={@$user->userID}">
									<img src="{$dpath}img/m.gif" alt="{lang}wot.global.write{/lang}" />
								</a>
							</td>
							<td class="column">
								{if $alliance->getRank(true, 6) && $alliance->ally_owner != $user->userID && $this->user->userID != $user->userID}
									<a href="index.php?action=AllianceKickUser&amp;userID={@$user->userID}&amp;allianceID={@$alliance->allianceID}" onclick="return confirm('{lang}wot.alliance.user.kick.sure{/lang}');">
										<img src="{$dpath}pic/abort.gif" alt="{lang}wot.alliance.user.kick{/lang}" />
									</a>
								{/if}
							</td>
							<td class="column">
								{if !$alliance->getRank(true, 6) || $alliance->ally_owner == $user->userID || $this->user->userID == $user->userID}
									{* can not edit ranks *}
									{assign var='rankID' value=$user->ally_rank_id-1}
									{if $rankID == -1}
										{if $alliance->ally_owner == $user->userID}
											{$alliance->ally_owner_range}
										{else}
											{lang}wot.alliance.newcomer{/lang}
										{/if}
									{else}
										{$alliance->getRank($rankID, 0)}
									{/if}
								{else}
									{* can edit ranks *}
									{assign var='rankID' value=$user->ally_rank_id-1}
									{*<select name="rank{@$user->userID}" onchange="eval('location = \'' + this.options[this.selectedIndex].value + '\';');">
									*}<select name="rank{@$user->userID}" onchange="alliance.changeRank({@$user->userID}, this.options[this.selectedIndex].value);">
										{* newcomer *}
										<option value="0"{if 0 == $user->ally_rank_id} selected="selected"{/if}>{lang}wot.alliance.newcomer{/lang}</option>
										
										{* owner *}
										{if $alliance->ally_owner == $this->user->userID}
											<option value="-1">{$alliance->ally_owner_range} ({lang}wot.alliance.founder.short{/lang})</option>
										{/if}
										
										{foreach from=$alliance->getRank() key='rankID' item='rank'}
											{assign var='rankID' value=$rankID+1}
											<option value="{@$rankID}"{if $rankID == $user->ally_rank_id} selected="selected"{/if}>{$rank.0}</option>
										{/foreach}
									</select>
								{/if}
							</td>
							<td class="column">
								{#$user->wotPoints}
							</td>
							<td class="column">
								<a href="../galaxy.php?g={$user->getPlanet()->galaxy}&amp;s={$user->getPlanet()->system}">
									[{$user->getPlanet()->galaxy}:{$user->getPlanet()->system}:{$user->getPlanet()->planet}]
								</a>
							</td>
							<td class="column">
								{@$user->ally_register_time|time}
							</td>
							{if $alliance->getRank(true, 7)}
								<td class="column">
									{if TIME_NOW < $user->onlinetime + 900}
										<span class="online">
											On
										</span>
									{else}
										{assign var='seconds' value=TIME_NOW-$user->onlinetime}
										{if TIME_NOW < $user->onlinetime + 3600}
											<span class="onlineTime">
												{assign var='minutes' value=$seconds/60}
												{$minutes|floor} min
											</span>
										{else}
											{* calc days *}
											{assign var='days' value=$seconds/86400}
											<span class="offline" title="{$days|floor}d">
												Off
											</span>
										{/if}
									{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		
		{include file="footer"}
	</body>
</html>