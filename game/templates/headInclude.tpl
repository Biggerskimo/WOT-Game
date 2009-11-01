<meta http-equiv="content-type" content="text/html; charset={@CHARSET}" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />
<meta name="description" content="{META_DESCRIPTION}" />
<meta name="keywords" content="{META_KEYWORDS}" />
{if !$allowSpidersToIndexThisPage|isset}<meta name="robots" content="noindex,nofollow" />{/if}

{if $specialStyles|isset}
	<!-- special styles -->
	{@$specialStyles}
{/if}
<!-- fix styles -->
<link rel="stylesheet" type="text/css" href="style/lostWorlds.css" />

<!-- user styles -->
<link rel="stylesheet" type="text/css" href="{@$dpath}formate.css" />

<!-- print styles -->
<link rel="stylesheet" type="text/css" media="print" href="{@RELATIVE_WCF_DIR}style/extra/print.css" />

<script type="text/javascript">
	//<![CDATA[
	var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
	var RELATIVE_WCF_DIR = '{@RELATIVE_WCF_DIR}';
	//]]>
</script>

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/default.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/PopupMenuList.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>