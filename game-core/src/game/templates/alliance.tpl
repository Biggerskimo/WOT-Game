{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.page.title{/lang}</title>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<div class="alliancePage">
				{if $alliance->ally_image != ""}
					<div class="allianceImage">
						<img src="{$alliance->ally_image}" alt="{lang}wot.alliance.page.image{/lang}" />
					</div>
				{/if}
				<div class="allianceData">
					<div class="lwcontainer-{cycle values='1,2'}">
						<div class="informationName">
							{lang}wot.alliance.name{/lang}
						</div>
						<div class="informationValue">
							{$alliance->ally_name}
							{if $alliance->id == $this->user->ally_id}
								{if $alliance->ally_owner != $this->user->userID}
									(<a href="index.php?action=AllianceLeave&amp;allianceID={@$alliance->allianceID}&amp;userID={@$this->user->userID}" onclick="return confirm('{lang}wot.alliance.leave.sure{/lang}');">{lang}wot.alliance.leave{/lang}</a>)
								{/if}
								{if $alliance->getRank(true, 1)}
									(<a href="index.php?action=AllianceDelete&amp;allianceID={@$alliance->allianceID}&amp;userID={@$this->user->userID}" onclick="return confirm('{lang}wot.alliance.delete.sure{/lang}');">{lang}wot.alliance.delete{/lang}</a>)
								{/if}
							{/if}
						</div>
					</div>
					<div class="lwcontainer-{cycle values='1,2'}">
						<div class="informationName">
							{lang}wot.alliance.tag{/lang}
						</div>
						<div class="informationValue">
							[{$alliance->ally_tag}]
						</div>
					</div>
					<div class="lwcontainer-{cycle values='1,2'}">
						<div class="informationName">
							{lang}wot.alliance.page.member{/lang}
						</div>
						<div class="informationValue">
							{#$alliance->ally_members}{if $alliance->allianceID == $this->user->ally_id && $alliance->getRank(true, 4)} (<a href="index.php?page=AllianceMembersList">{lang}wot.alliance.page.membersList{/lang}</a>){/if}
						</div>
					</div>
					{if $alliance->id == $this->user->ally_id && $applicationsCount && $alliance->getRank(true, 3)}
						<div class="lwcontainer-{cycle values='1,2'}">
							<div class="informationName">
								{lang}wot.alliance.applications{/lang}
							</div>
							<div class="informationValue">
								{#$applicationsCount} (<a href="index.php?page=AllianceApplicationsList">{lang}wot.alliance.page.applicationsList{/lang}</a>)
							</div>
						</div>
					{/if}
					{if $alliance->id == $this->user->ally_id}
						<div class="lwcontainer-{cycle values='1,2'}">
							<div class="informationName">
								{lang}wot.alliance.rank{/lang}
							</div>
							<div class="informationValue">
								{assign var='rankID' value=$this->user->ally_rank_id-1}
								{if $rankID == -1}
									{if $alliance->ally_owner == $this->user->userID}
										{$alliance->ally_owner_range}
									{else}
										{lang}wot.alliance.newcomer{/lang}
									{/if}
								{else}
									{$alliance->getRank($rankID, 0)}
								{/if}{if $alliance->getRank(true, 6)} (<a href="index.php?form=AllianceAdministration&amp;allianceID={$alliance->allianceID}">{lang}wot.alliance.page.administrate{/lang}</a>){/if}
							</div>
						</div>
					{/if}
					<div class="lwcontainer-{cycle values='1,2'}">
						<div class="informationName">
							{lang}wot.alliance.page.leader{/lang}
						</div>
						<div class="informationValue">
							{$leader}
						</div>
					</div>
					{if $alliance->id == $this->user->ally_id && $alliance->getRank(true, 8)}
						<div class="lwcontainer-{cycle values='1,2'}">
							<div class="informationName">
								{lang}wot.alliance.circular{/lang}
							</div>
							<div class="informationValue">
								<a href="index.php?form=AllianceCircularCreate">{lang}wot.alliance.circular.create{/lang}</a>
							</div>
						</div>
					{/if}
					{if $alliance->getInterrelation(null, 1, 3) !== null}
						<div class="lwcontainer-{cycle values='1,2'}">
							<div class="informationName">
								{lang}wot.alliance.diplomacy.confederations{/lang}
							</div>
							<div class="informationValue">
								{foreach from=$alliance->getInterrelation(-1, 1, 3) key='allianceID2' item='alliance2'}
									{if $moreConfederations|isset}, {/if}{@$alliance2}
									{assign var='moreConfederations' value=true}
								{/foreach}
							</div>
						</div>
					{/if}
					{if $alliance->getInterrelation(null, 2, 3) !== null}
						<div class="lwcontainer-{cycle values='1,2'}">
							<div class="informationName">
								{lang}wot.alliance.diplomacy.nonAgressionPacts{/lang}
							</div>
							<div class="informationValue">
								{foreach from=$alliance->getInterrelation(-1, 2, 3) key='allianceID2' item='alliance2'}
									{if $moreNonAgressionPacts|isset}, {/if}{@$alliance2}
									{assign var='moreNonAgressionPacts' value=true}
								{/foreach}
							</div>
						</div>
					{/if}
					{if $alliance->getInterrelation(null, 3, 3) !== null}
						<div class="lwcontainer-{cycle values='1,2'}">
							<div class="informationName">
								{lang}wot.alliance.diplomacy.wars{/lang}
							</div>
							<div class="informationValue">
								{foreach from=$alliance->getInterrelation(-1, 3, 3) key='allianceID2' item='alliance2'}
									{if $moreWars|isset}, {/if}{@$alliance2}
									{assign var='moreWars' value=true}
								{/foreach}
							</div>
						</div>
					{/if}
				</div>
				{if $alliance->ally_web != ''}
					<div class="lwcontainer-{cycle values='1,2'}">
						<div class="informationName">
							{lang}wot.alliance.homepage{/lang}
						</div>
						<div class="informationValue">
							<a href="{$alliance->ally_web}" class="externalURL">{$alliance->ally_web}</a>
						</div>
					</div> 
				{/if}
				{if $alliance->ally_description != ""}
					<div class="allianceExternalText lwcontainer-{cycle values='1,2'}">
						<h3>
							{lang}wot.alliance.externalText{/lang}
						</h3>
						<hr />
						<p>
							{@$alliance->ally_description|nl2br}
						</p>
					</div>
				{/if}
				{if $alliance->ally_description != "" && $alliance->id == $this->user->ally_id}
					<div class="allianceInternalText lwcontainer-{cycle values='1,2'}">
						<h3>
							{lang}wot.alliance.internalText{/lang}
						</h3>
						<hr />
						<p>
							{@$alliance->ally_text|nl2br}
						</p>
					</div>
				{/if}
			</div>
		</div>
		
		{include file="footer"}
	</body>
</html>