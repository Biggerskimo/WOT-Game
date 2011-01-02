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
			{* messages *}
			{if $messages|count}
				{include file='messagesList' id='messages' messages=$messages}
			{/if}
		</div>
		{include file='footer'}
	</body>
</html>