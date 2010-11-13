{include file="documentHeader"}
	<head>
		<link rel="stylesheet" type="text/css" href="style/statistics.css" />
		<title>{lang}wot.stat.page.title{/lang}</title>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<div class="statTypeSelect">
				<form action="index.php?page=Statistics" method="post" id="statSelect">
					<div>
						{capture assign='typeSelect'}
							<select name="type" onchange="if (document.getElementById('startInput').value == 1) document.getElementById('startInput').value = ''; document.forms.statSelect.submit()">
								{foreach from=$types item=$type}
									<option value="{$type}"{if $showType == $type} selected="selected"{/if}>{lang}wot.stat.type.{$type}{/lang}</option>
								{/foreach}
							</select>					
						{/capture}
						{capture assign='nameSelect'}
							<select name="name" onchange="if (document.getElementById('startInput').value == 1) document.getElementById('startInput').value = ''; document.forms.statSelect.submit()">
								{foreach from=$names item=$name}
									<option value="{$name}"{if $optionName == $name} selected="selected"{/if}>{lang}wot.stat.name.{$name}{/lang}</option>
								{/foreach}
							</select>					
						{/capture}
						{capture assign='rankSelected'}
							<span class="rankSelected">
								{assign var='startRow' value=$rows|current}
								{assign var='startRank' value=$startRow.rank}
								{assign var='lastRow' value=$rows|end}
								{assign var='lastRank' value=$lastRow.rank}
								{#$startRank} - {#$lastRank}
							</span>
						{/capture}
						{capture assign='rankSelect'}
							<a href="index.php?page=Statistics&amp;type={$showType}&amp;name={$showName}&amp;start={$showStart-100|max:1}">&#8656;</a>
							<ul class="rankSelect lwcontainer-1">
								<li>
									<a href="#" onmouseover="document.getElementById('startInput').select();document.getElementById('startInput').focus();">{lang}wot.stat.rank{/lang} {@$rankSelected}</a>
									<ul class="lwcontainer-2">
										<li class="lwcontainer-1"><a href="index.php?page=Statistics&amp;type={$showType}&amp;name={$showName}&amp;start=0">{lang}wot.stat.rank.ownPosition{/lang}</a></li>
										<li class="lwcontainer-2"><a href="index.php?page=Statistics&amp;type={$showType}&amp;name={$showName}&amp;start=1">{lang}wot.stat.rank.top{/lang}</a></li>
										<li class="lwcontainer-1"><span>{lang}wot.stat.rank.input{/lang}<input name="startInput" id="startInput" type="text" size="4" value="{$showStart}" /></span></li>
									</ul>
								</li>
							</ul>
							<a href="index.php?page=Statistics&amp;type={$showType}&amp;name={$showName}&amp;start={$showStart+100}">&#8658;</a>
						{/capture}
					
						{lang}wot.stat.select{/lang}
						<input type="hidden" name="showType" value="{$showType}" />
						<input type="hidden" name="showName" value="{$showName}" />
					</div>
				</form>
			</div>
		
			<table>
				<thead>
					{include file=$statEntryTemplate|concat:'Header'}
				</thead>
				<tbody>
					{foreach from=$rows key=$rank item=$entry}
						{include file=$statEntryTemplate}
					{/foreach}
				</tbody>
			</table>
		</div>
		
		{include file="footer"}
	</body>
</html>