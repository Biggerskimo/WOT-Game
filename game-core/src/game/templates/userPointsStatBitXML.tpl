<entry rank="{@$rank}" userID="{@$entry.userID}">
	<change>{@$entry.change}</change>
	<name><![CDATA[{@$entry.username}]]></name>
	<points>{@$entry.points}</points>
	{if $entry.allianceID}
		<alliance allianceID="{@$entry.allianceID}" tag="{$entry.allianceTag}"><![CDATA[{$entry.allianceName}]]></alliance>
	{/if}
</entry>