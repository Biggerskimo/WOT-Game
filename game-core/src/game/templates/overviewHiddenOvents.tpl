{include file="documentHeader"}
	<head>
		<title>{lang}wot.overview.page.title{/lang}</title>
		<script type="text/javascript" src="js/Date.format.js"></script>
		<script type="text/javascript" src="js/NTime.class.js"></script>
		<script type="text/javascript" src="js/Overview.class.js"></script>
		<script type="text/javascript" src="js/Tooltip.class.js"></script>
		<script type="text/javascript" src="../js/jQuery.js"></script>
		<script type="text/javascript" src="../js/thickbox.js"></script>
		<script type="text/javascript">
			language = { };
			language["day"] = "{lang}wot.global.date.day{/lang}";
			language["days"] = "{lang}wot.global.date.days{/lang}";
			language["tomorrow"] = "{lang}wot.global.date.tomorrow{/lang}";
			language["theDayAfterTomorrow"] = "{lang}wot.global.date.theDayAfterTomorrow{/lang}";
			language["hideOvent.sure"] = "{lang}wot.overview.ovent.hide.sure{/lang}";
		</script>
		{include file="headInclude"}
		<link href="../css/thickbox.css" type="text/css" rel="stylesheet">
	</head>
	<body>
		<div class="main content overviewHiddenOvents">
			{* ovents *}
			{if $ovents|count}
				{include file='oventList' id='ovents' ovents=$ovents noHighlight=true}
			{/if}
		</div>
		
		{include file="footer"}
	</body>
</html>