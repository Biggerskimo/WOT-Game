{include file="documentHeader"}
	<head>
		<title>{lang}wot.messages.page.title{/lang}</title>		
		<script type="text/javascript" src="js/jq.js"></script>
		<script type="text/javascript" src="js/Messages.class.js"></script>
		{include file="headInclude"}
		<link href="../css/thickbox.css" type="text/css" rel="stylesheet">
		<script type="text/javascript">
			language = { };
			language['message.notify.sure'] = "{lang}wot.messages.message.notify.sure{/lang}";
			language['message.notify.done'] = "{lang}wot.messages.message.notify.done{/lang}";
			language['message.ignore.sure'] = "{lang}wot.messages.message.ignore.sure{/lang}";
			language['message.ignore.done'] = "{lang}wot.messages.message.ignore.done{/lang}";
		</script>
	</head>
	<body>
		{capture append='additionalTopnavContent'}
			{if $remembered === null}
				<span class="showRememberedMessages"><a href="index.php?page=Messages&amp;remembered=1">{lang}wot.messages.showRemembered{/lang}</a></span>
			{else}
				<span class="showAllMessages"><a href="index.php?page=Messages">{lang}wot.messages.showAll{/lang}</a></span>
			{/if}
		{/capture}
		{include file="topnav"}
		<div class="main content messages">
			{* folders *}
			{if $remembered === null}
				<div class="messageFolders">
					<div class="contentDescriptor">
						<div class="check">
							{lang}wot.messages.folders.check{/lang}
						</div>
						<div class="name">
							{lang}wot.messages.folders.name{/lang}
						</div>
						<div class="holdBackTime">
							{lang}wot.messages.folders.holdBackTime{/lang}
						</div>
						<div class="unviewed">
							{lang}wot.messages.folders.unviewed{/lang}
						</div>
						<div class="all">
							{lang}wot.messages.folders.all{/lang}
						</div>
					</div>
					{foreach from=$folders key='folderID' item='folder'}
						<div class="messageFolder {if $folderID|in_array:$active}active{else}inactive{/if}" id="messageFolder{@$folderID}">
							{if $active !== null && $folderID|in_array:$active}
								{if $active|count == 1}
									{assign var='link' value='index.php?page=Messages'}
								{else}
									{assign var='active2' value=$active|array_flip}
									{assign var='activeKey' value=$active2.$folderID}
									{assign var='active2' value=$active}
									{$active2|array_splice:$activeKey:1}
									{assign var='active2' value=','|implode:$active2}
									{assign var='link' value='index.php?page=Messages&amp;active='|concat:$active2}
								{/if}
							{else}
								{if $active === null || !$active|count}
									{assign var='link' value='index.php?page=Messages&amp;active='|concat:$folderID}
								{else}
									{assign var='active2' value=','|implode:$active}
									{assign var='link' value='index.php?page=Messages&amp;active='|concat:$active2:',':$folderID}
								{/if}
							{/if}
							<div class="check">
								<span><a href="{@$link}">&nbsp;</a></span>
							</div>
							<div class="name">
								{if $active !== null && $folderID|in_array:$active}
									<a href="index.php?page=Messages">{lang}{$folder->name}{/lang}</a>
								{else}
									<a href="index.php?page=Messages&amp;active={@$folderID}">{lang}{$folder->name}{/lang}</a>
								{/if}
							</div>
							<div class="holdBackTime" title="{#$folder->holdBackTime} {lang}wot.global.time.seconds{/lang}">
								{@$folder->holdBackTime/86400|floor} {lang}wot.global.time.days{/lang}
							</div>
							<div class="unviewed">
								{#$folder->unviewedCount}
							</div>
							<div class="all">
								{#$folder->messageCount}
							</div>
						</div>
					{/foreach}
				</div>
			{/if}
			
			{* messages *}
			{if $messages|count}
				<a name="unread"></a>
				{include file='messagesList' id='messages' messages=$messages}
			{/if}
		</div>
		{include file='footer'}
	</body>
</html>