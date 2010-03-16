{include file='header'}
{*<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>*}
<script type="text/javascript" src="js/OnlineTimeViewer.class.js"></script>
<script type="text/javascript">
	// <[![CDATA
	
	// ]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userEditL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wot.acp.user.show{/lang}</h2>
	</div>
</div>

<div class="contentHeader">
	<div class="largeButtons">
		<ul>
			<li>
				<a href="index.php?form=FleetSearch&amp;startUserID={@$userID}&amp;doSearch&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
					<img src="{@RELATIVE_LW_DIR}icon/fleetLogM.png" alt="{lang}wot.acp.user.fleet.showLogs{/lang}" />
					<span>
						{lang}wot.acp.user.fleet.showLogs{/lang}
					</span>
				</a>
			</li>
		</ul>
	</div>
</div>

<div class="border content" style="padding: 5px;">
	<fieldset>
		<legend>
			{lang}wot.acp.user.onlinetime{/lang}
		</legend>
		
		<canvas id="onlinetime{@$userID}" height="100px" width="500px">
			{lang}wot.acp.user.onlinetime.noCanvasCompatibleBrowser{/lang}
		</canvas>
	</fieldset>
</div>
<div class="contentFooter">
	<div class="largeButtons">
		<ul>
			<li>
				<a href="index.php?form=FleetSearch&amp;startUserID={@$userID}&amp;doSearch&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
					<img src="{@RELATIVE_LW_DIR}icon/fleetLogM.png" alt="{lang}wot.acp.user.fleet.showLogs{/lang}" />
					<span>
						{lang}wot.acp.user.fleet.showLogs{/lang}
					</span>
				</a>
			</li>
		</ul>
	</div>
</div>

{include file='footer'}