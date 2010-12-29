{include file="documentHeader"}
	<head>
		<title>{lang}wot.messages.page.title{/lang}</title>		
		<script type="text/javascript" src="js/jq.js"></script>
		<script type="text/javascript" src="js/Messages.class.js"></script>
		{include file="headInclude"}
		<link href="../css/thickbox.css" type="text/css" rel="stylesheet">
		<script type="text/javascript">
			language = { };
			language['message.delete.sure'] = "{lang}wot.messages.message.delete.sure{/lang}";
		</script>
	</head>
	<body>
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