{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.applicationsList.title{/lang}</title>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th{if $sortField == 'username'} class="active"{/if}>
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
						<th{if $sortField == 'ally_register_time'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=AllianceMembersList&amp;pageNo={@$pageNo}&amp;sortField=ally_register_time&amp;sortOrder={if $sortField == 'ally_register_time' && $sortOrder == 'DESC'}ASC{else}DESC{/if}">
										{lang}wot.alliance.applicationTime{/lang}
										{if $sortField == 'ally_register_time'}
											<img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					{if $applications|count}
						{foreach from=$applications item='application'}
							<tr class="lwcontainer-{cycle values='1, 2' name='contcyc'}">
								<td class="column">
									<a href="index.php?form=AllianceApplicationView&amp;userID={$application->userID}">
										{$application->username}
									</a>
								</td>
								<td class="column">
									{#$application->points}
								</td>
								<td class="column">
									{@$application->ally_register_time|time}
								</td>
							</tr>
						{/foreach}
					{else}
						<tr class="lwcontainer-1">
							<td class="coloumn" colspan="3">
								{lang}wot.alliance.applicationsList.noApplications{/lang}
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
		
		{include file="footer"}
	</body>
</html>