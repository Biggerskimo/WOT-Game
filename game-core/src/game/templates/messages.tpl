{include file="documentHeader"}
	<head>
		<title>{lang}wot.messages.page.title{/lang}</title>		
		<script type="text/javascript" src="js/Date.format.js"></script>
		<script type="text/javascript" src="js/NTime.class.js"></script>
		{include file="headInclude"}
		<link href="../css/thickbox.css" type="text/css" rel="stylesheet">
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