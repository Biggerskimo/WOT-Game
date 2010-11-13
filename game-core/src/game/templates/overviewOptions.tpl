{include file="documentHeader"}
	<head>
		<title>{lang}wot.overview.page.title{/lang}</title>		
		<script type="text/javascript" src="js/Overview.class.js"></script>
		<script type="text/javascript">
			language = { };
		</script>
		{include file="headInclude"}
		<link href="../css/thickbox.css" type="text/css" rel="stylesheet">
	</head>
	<body>
		<div class="main content overviewOptions">
			<form action="index.php?form=OverviewOptions" method="post">
				{* hide ovent types *}
				<div class="doubleList">
					<div class="doubleDesc">
						{lang}wot.ovent.type.hide{/lang}
					</div>
					{foreach from=$oventTypes key='oventTypeID' item='oventTypeData'}
						<div class="double">
							<div class="doublePart1">
								{lang}wot.ovent.type{@$oventTypeID}{/lang}
							</div>
							<div class="doublePart2">
								{assign var='setting' value='hideOventType'|concat:$oventTypeID}
								<input type="checkbox" id="hideOventType{@$oventTypeID}" name="hideOventType{@$oventTypeID}" value="on"{if $this->user->getSetting($setting)} checked="checked"{/if} />
							</div>
						</div>
					{/foreach}
				</div>
				
				{* general options *}
				<div class="doubleList">
					<div class="doubleDesc">
						{lang}wot.overview.options.general{/lang}
					</div>
					
					<div class="double">
						<div class="doublePart1">
							{lang}wot.overview.options.dontAskOnHiding{/lang}
						</div>
						<div class="doublePart2">
							<input type="checkbox" id="dontAskOnHiding" name="dontAskOnHiding" value="on"{if $this->user->getSetting('dontAskOnOventHiding')} checked="checked"{/if} />
						</div>
					</div>
					
					<div class="double">
						<div class="doublePart1">
							{lang}wot.overview.options.hideInformation{/lang}
						</div>
						<div class="doublePart2">
							<input type="checkbox" id="hideInformation" name="hideInformation" value="on"{if $this->user->getSetting('hideInformation')} checked="checked"{/if} />
						</div>
					</div>
					
					<div class="double">
						<div class="doublePart1">
							{lang}wot.overview.options.hideColonies{/lang}
						</div>
						<div class="doublePart2">
							<input type="checkbox" id="hideColonies" name="hideColonies" value="on"{if $this->user->getSetting('hideColonies')} checked="checked"{/if} />
						</div>
					</div>
				</div>
				
				<div class="formSubmit">
					<input type="submit" name="blablub" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
				</div>
			</form>
		</div>
		
		{include file="footer"}
	</body>
</html>