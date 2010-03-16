<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
	<td class="rank">
		<a id="rank{@$entry.rank}"></a>		
		{#$entry.rank}
	</td>
	<td class="change">
		<span class="statChange{if !$entry.change}None{else}{if $entry.change > 0}Up{else}Down{/if}{/if}">{#$entry.change|abs}</span>
	</td>
	<td>
		{if $entry.allianceID == $this->user->ally_id}
			<script type="text/javascript">
			// <![CDATA[
				{assign var='optimalRank' value=$entry.rank-10}
				window.location.hash = 'rank{@$optimalRank|max:$showStart}';
			// ]]>			
			</script>
				<a id="self">
				</a>
			{assign var='class' value='self'}
		{else}
			{if $this->user->ally_id && $this->getAlliance()->getInterrelation($entry.allianceID, 1, 1) !== null}
				{assign var='class' value='mate'}
			{else}
				<span>
				{assign var='class' value=''}
			{/if}
		{/if}
		<a class="{@$class}" href="index.php?page=Alliance&amp;allianceID={@$entry.allianceID}" title="{$entry.allianceTag}">
			{$entry.allianceName}
		</a>
	</td>
	<td>
		{if !$this->user->ally_id && !$this->user->ally_request_id}
			<a href="index.php?form=AllianceApply&amp;allianceID={@$entry.allianceID}">
				<img src="{$dpath}pic/key.gif" alt="{lang}wot.alliance.apply{/lang}" />
			</a>
		{else}
		{if $this->user->ally_id && $this->getAlliance()->getRank(true, 6)}
			<a href="index.php?form=AllianceInterrelationApply&amp;allianceID={@$entry.allianceID}&amp;allianceID2={@$this->user->ally_id}">
				<img src="{$dpath}pic/key.gif" alt="{lang}wot.alliance.diplomacy{/lang}" />
			</a>
		{/if}
		{/if}
	</td>
	<td class="member">
		{#$entry.membersCount}
	</td>
	<td class="points averagePoints">
		{#$entry.points}
	</td>
	<td class="points">
		{#$entry.entirePoints}
	</td>
</tr>