<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
	<td class="rank">
		<a id="rank{@$entry.rank}"></a>		
		{#$entry.rank}
	</td>
	<td class="change">
		<span class="statChange{if !$entry.change}None{else}{if $entry.change > 0}Up{else}Down{/if}{/if}">{#$entry.change|abs}</span>
	</td>
	<td>
		{if $entry.userID == $this->user->userID}
			<script type="text/javascript">
			// <![CDATA[
				{assign var='optimalRank' value=$entry.rank-10}
				window.location.hash = 'rank{@$optimalRank|max:$showStart}';
			// ]]>			
			</script>
			<span class="self">
				<a id="self">
				</a>
		{else}
			{if $entry.allianceID == $this->user->ally_id && $this->user->ally_id}
				<span class="mate">
			{else}
				{if $this->user->hasBuddy($entry.userID)}
					<span class="buddy">
				{else}
					<span>
				{/if}
			{/if}
		{/if}
			{if $entry.userID == $showRelationalID}
				<script type="text/javascript">
				// <![CDATA[
					{assign var='optimalRank' value=$entry.rank-10}
					window.location.hash = 'rank{@$optimalRank|max:$showStart}';
				// ]]>			
				</script>
				<span class="search">
					{$entry.username}
				</span>
			{else}
				{$entry.username}
			{/if}
		</span>
	</td>
	<td>
		<a href="../messages.php?mode=write&amp;id={@$entry.userID}"><img src="{$dpath}img/m.gif" alt="{lang}wot.message.write{/lang}" /></a>
	</td>
	<td>
		{if $entry.allianceID !== null}
			<a href="index.php?page=Alliance&amp;allianceID={@$entry.allianceID}">[{$entry.allianceTag}]</a>
		{/if}
	</td>
	<td class="points">
		{#$entry.points}
	</td>
</tr>