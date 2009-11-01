{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu();
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userEditL.png" alt="" />
	<div class="headlineContainer">
		<h2>Benutzer editieren [&Uuml;bersicht]</h2>
	</div>
</div>
{pages print=true link="index.php?page=ViewFleetLog&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&userID=$userID"|concat:SID_ARG_2ND_NOT_ENCODED}
<div class="contentHeader">
	<div class="largeButtons">
		<ul>
			<li>
				<a href="index.php?page=ViewFleetLog&amp;userID={$userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
					<img src="{@RELATIVE_LW_DIR}icon/fleetLogM.png" alt="" title="Flottenlogs zeigen" />
					<span>
						Flottenlogs zeigen
					</span>
				</a>
			</li>
		</ul>
	</div>
</div>
<!--div class="border content" style="padding: 5px;"-->

	<div class="border content">
		<div class="border" style="padding: 0px;">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th{if $sortField == 'fleet_start_time'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=ViewFleetLog&amp;pageNo={@$pageNo}&amp;sortField=fleet_start_time&amp;sortOrder={if $sortField == 'fleet_start_time' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;userID={$userID}{@SID_ARG_2ND_NOT_ENCODED}">
										Einschlag
										{if $sortField == 'fleet_start_time'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'fleet_mission'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=ViewFleetLog&amp;pageNo={@$pageNo}&amp;sortField=fleet_mission&amp;sortOrder={if $sortField == 'fleet_mission' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;userID={$userID}{@SID_ARG_2ND}">
										Auftrag
										{if $sortField == 'fleet_mission'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'fleet_resource'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=ViewFleetLog&amp;pageNo={@$pageNo}&amp;sortField=fleet_resource&amp;sortOrder={if $sortField == 'fleet_resource' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;userID={$userID}{@SID_ARG_2ND}">
										Ressourcen
										{if $sortField == 'fleet_resource'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'fleet_start_koord'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=ViewFleetLog&amp;pageNo={@$pageNo}&amp;sortField=fleet_start_koord&amp;sortOrder={if $sortField == 'fleet_start_koord' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;userID={$userID}{@SID_ARG_2ND}">
										Startkoord.
										{if $sortField == 'fleet_start_koord'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'fleet_owner'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=ViewFleetLog&amp;pageNo={@$pageNo}&amp;sortField=fleet_owner&amp;sortOrder={if $sortField == 'fleet_owner' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;userID={$userID}{@SID_ARG_2ND}">
										Besitzer
										{if $sortField == 'fleet_owner'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'fleet_end_koord'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=ViewFleetLog&amp;pageNo={@$pageNo}&amp;sortField=fleet_end_koord&amp;sortOrder={if $sortField == 'fleet_end_koord' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;userID={$userID}{@SID_ARG_2ND}">
										Zielkoord.
										{if $sortField == 'fleet_end_koord'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						<th{if $sortField == 'fleet_ofiara'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=ViewFleetLog&amp;pageNo={@$pageNo}&amp;sortField=fleet_ofiara&amp;sortOrder={if $sortField == 'fleet_ofiara' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;userID={$userID}{@SID_ARG_2ND}">
										Angeflogener
										{if $sortField == 'fleet_ofiara'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$fleets item='fleet'}
						<tr class="container-{cycle values='1, 2' name='contcyc'}">
							<td class="columnTopic">
								{@$fleet->fleet_start_time|time}
							</td>
							<td class="columnRating">
								{$fleet->getMissionName()}
							</td>
							<td class="columnTopic" title="{@$fleet->getRessources('strWBR')}">
								{#$fleet->getRessources('all')}
							</td>
							<td class="columnRating" title="{$fleet->getStartPlanet()}">
								{$fleet->fleet_start_galaxy}:{$fleet->fleet_start_system}:{$fleet->fleet_start_planet}
							</td>
							<td class="columnTopic">
								{if $thisUser->username == $fleet->attackerName}
									<b>
										{$fleet->attackerName}
									</b>
								{else}
									{$fleet->attackerName}
								{/if}
							</td>
							<td class="columnRating" title="{$fleet->getEndPlanet()}">
								{$fleet->fleet_end_galaxy}:{$fleet->fleet_end_system}:{$fleet->fleet_end_planet}
							</td>
							<td class="columnTopic">
								{if $thisUser->username == $fleet->defenderName}
									<b>
										{$fleet->defenderName}
									</b>
								{else}
									{$fleet->defenderName}
								{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{*
	<div class="border" style="float: right; width: 43%;">
		Flottenbewegungen der letzten 7 Tage:

		<div class="border" style="padding: 0px;">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th class="columnTopic">
							<div>
								<span class="emptyHead">
									Flottenbewegung
								</span>
							</div>
						</th>
						<th class="columnRating">
							<div>
								<span class="emptyHead">
									Anzahl
								</span>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$missions item='mission'}
						<tr class="container-{cycle values='1, 2' name='contcyc'}">
							<td class="columnTopic" >
								{$mission.name}
							</td>
							<td class="columnRating" title="Tag 1: {$mission.day1}, Tag 2: {$mission.day2}, Tag 3: {$mission.day3}, Tag 4: {$mission.day4}, Tag 5: {$mission.day5}, Tag 6: {$mission.day6}, Tag 7: {$mission.day7}">
								{#$mission.day1 + $mission.day2 + $mission.day3 + $mission.day4 + $mission.day5 + $mission.day6 + $mission.day7}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>


	<span style="margin-top: 250px; display: block;">Gepushte User:</span>

	<div class="border" style="padding: 0px;">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnTopic">
						<div>
							<span class="emptyHead">
								Gepushter
							</span>
						</div>
					</th>
					<th class="columnRating">
						<div>
							<span class="emptyHead">
								Zeitpunkt
							</span>
						</div>
					</th>
					<th class="columnReplies">
						<div>
							<span class="emptyHead">
								Ressourcen
							</span>
						</div>
					</th>
					<th class="columnViews">
						<div>
							<span class="emptyHead">
								Platzierung
							</span>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$pushedUsers item='user'}
					<tr class="container-{cycle values='1,2' name='contcyc'}">
						<td class="columnTopic" title="{$user.userName2} bearbeiten">
							<a href="index.php?page=GameUser&amp;userID={@$user.userID2}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
								{$user.userName2}
							</a>
						</td>
						<td class="columnRating">
							{@$user.time|time}
						</td>
						<td class="columnReplies" title="Metal: {#$user.metal}, Kristall: {#$user.crystal}, Deuterium: {#$user.deuterium}">
							{#$user.metal + $user.crystal + $user.deuterium}
						</td>
						<td class="columnViews">
							{#$user.points2} Punkte (Platz {#$user.rank2})
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>

	Pusher:

	<div class="border" style="padding: 0px;">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnTopic">
						<div>
							<span class="emptyHead">
								Pusher
							</span>
						</div>
					</th>
					<th class="columnRating">
						<div>
							<span class="emptyHead">
								Zeitpunkt
							</span>
						</div>
					</th>
					<th class="columnReplies">
						<div>
							<span class="emptyHead">
								Ressourcen
							</span>
						</div>
					</th>
					<th class="columnViews">
						<div>
							<span class="emptyHead">
								Platzierung
							</span>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$pushingUsers item='user'}
					<tr class="container-{cycle values='1,2' name='contcyc'}">
						<td class="columnTopic" title="{$user.userName2} bearbeiten">
							<a href="index.php?page=GameUser&amp;userID={@$user.userID2}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
								{$user.userName2}
							</a>
						</td>
						<td class="columnRating">
							{@$user.time|time}
						</td>
						<td class="columnReplies" title="Metal: {#$user.metal}, Kristall: {#$user.crystal}, Deuterium: {#$user.deuterium}">
							{#$user.metal + $user.crystal + $user.deuterium}
						</td>
						<td class="columnViews">
							{#$user.points2} Punkte (Platz {#$user.rank2})
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>

	&Auml;hnliche User:

	<div class="border" style="padding: 0px;">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnTopic">
						<div>
							<span class="emptyHead">
								User
							</span>
						</div>
					</th>
					<th class="columnRating">
						<div>
							<span class="emptyHead">
								IP
							</span>
						</div>
					</th>
					<th class="columnReplies">
						<div>
							<span class="emptyHead">
								E-Mail
							</span>
						</div>
					</th>
					<th class="columnViews">
						<div>
							<span class="emptyHead">
								Passwort (MD5)
							</span>
						</div>
					</th>
					<th class="columnLastPost">
						<div>
							<span class="emptyHead">
								Platzierung
							</span>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				{assign var='userIPParts' value='.'|explode:$thisUser->user_lastip}
				{assign var='userEmailParts' value='@'|explode:$thisUser->email_2}
				{foreach from=$similiarUsers item='user'}
					<tr class="container-{cycle values='1,2' name='contcyc'}">
						<td class="columnTopic" title="{$user->username} bearbeiten">
							<a href="index.php?page=GameUser&amp;userID={@$user->userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
								{$user->username}
							</a>
						</td>
						<td class="columnRating">
							{assign var='ipParts' value='.'|explode:$user->user_lastip}
							{if $userIPParts.0 == $ipParts.0 && $userIPParts.1 == $ipParts.1}
								{if $userIPParts.2 == $ipParts.2 && $userIPParts.3 == $ipParts.3}
									<span style="color: red;">
										{@$user->user_lastip}
									</span>
								{else}
									{if $userIPParts.2 == $ipParts.2}
										<span style="color: orange;">
											{@$user->user_lastip}
										</span>
									{else}
										<span style="color: DarkOrange;">
											{@$user->user_lastip}
										</span>
									{/if}
								{/if}
							{else}
								{@$user->user_lastip}
							{/if}
						</td>
						<td class="columnReplies">
							{assign var='emailParts' value='.'|explode:$user->email_2}
							{if $emailParts.0 == $userEmailParts.0}
								<span style="color: red;">
									{@$user->email}
								</span>
							{else}
								{@$user->email}
							{/if}
						</td>
						<td class="columnViews">
							{if $thisUser->gamePassword == $user->gamePassword}
								<span style="color: red;">
									{@$user->gamePassword}
								</span>
							{else}
								{@$user->gamePassword}
							{/if}
						</td>
						<td class="columnLastPost">
							{#$user->points} Punkte (Platz {#$user->rankPoints})
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>*}
<!--/div-->
<div class="contentFooter">
	<div class="largeButtons">
		<ul>
			<li>
				<a href="index.php?page=ViewFleetLog&amp;userID={$userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
					<img src="{@RELATIVE_LW_DIR}icon/fleetLogM.png" alt="" title="Flottenlogs zeigen" />
					<span>
						Flottenlogs zeigen
					</span>
				</a>
			</li>
		</ul>
	</div>
</div>

{include file='footer'}