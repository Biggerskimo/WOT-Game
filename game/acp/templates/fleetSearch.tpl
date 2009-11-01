{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	//var tabMenu = new TabMenu();
	//onloadEvents.push(function() { tabMenu.showSubTabMenu('profile') });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userSearchL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wot.acp.fleet.search{/lang}</h2>
	</div>
</div>

{if $errorField == 'search'}
<p class="error">{lang}wot.acp.fleet.search.error.noMatches{/lang}</p>
{/if}

<form method="post" action="index.php?form=FleetSearch">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wot.acp.fleet.search.conditions.general{/lang}</legend>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="userID">{lang}wot.fleet.fleetID{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" id="fleetID" name="fleetID" value="{if $fleetID}{$fleetID}{/if}" />
					</div>
				</div>
				
				{include file='optionFieldList' langPrefix='wot.fleet.option.'}
			
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	document.getElementById('fleetID').focus();
	//]]>
</script>

{include file='footer'}