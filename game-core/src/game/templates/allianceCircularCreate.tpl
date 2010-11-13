{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.circularCreate.title{/lang}</title>
		<script type="text/javascript" src="js/Alliance.class.js"></script>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			{if $errorField}
				<p class="error">
					{lang}wcf.global.form.error{/lang}
				</p>
			{/if}
			
			<form class="allianceCircularCreate" action="index.php?form=AllianceCircularCreate&amp;allianceID={@$allianceID}" method="post">
				<div class="formElement{if $errorField == 'allianceName'} formError{/if}">
					<div class="formFieldLabel">
						<label for="circularText">{lang}wot.alliance.circularCreate.text{/lang}</label>
					</div>
					<div class="formField">
						<textarea id="circularText" name="circularText" cols="40" rows="20"></textarea>		
						{if $errorField == 'applicationText'}
							<p class="innerError">
								{lang}wot.alliance.circularCreate.text.notValid{/lang}
							</p>
						{/if}
					</div>
					
					<div class="formFieldLabel">
						{lang}wot.alliance.circularCreate.alliances{/lang}
					</div>
					
					{* own alliance *}
					<div class="formField allianceCircularCreateFormField">
						<input id="alliance{@$allianceID}" type="checkbox" name="alliance{@$allianceID}" checked="checked" onkeyup="alliance.showInterrelationInformation({@$allianceID}, 'block');" onmouseup="alliance.showInterrelationInformation({@$allianceID}, 'block');" />
						
						<label class="allianceNameLabel" for="alliance{@$allianceID}">
							{@$alliance}
						</label>
						<div id="interrelation{@$allianceID}" class="allianceRank">
							{lang}wot.alliance.rank{/lang}:
							<select size="1" name="alliance{@$allianceID}Rank">
								<option value="-1" selected="selected">{lang}wot.alliance.rank.all{/lang}</option>
								<option value="0">{lang}wot.alliance.page.leader{/lang}</option>
								{assign var='ranks' value=$alliance->getRank()}
								{foreach from=$ranks key='rankID' item='rank'}
									<option value="{@$rankID}">{$rank.0}</option>
								{/foreach}
							</select>
						</div>
						<script type="text/javascript">
							alliance.showInterrelationInformation({@$allianceID}, 'block');
						</script>
					</div>
					
					{* interrelations *}
					{foreach from=$interrelations key='allianceID' item='alliance2'}
						<div class="formField allianceCircularCreateFormField">
							<input id="alliance{@$alliance2->allianceID}" type="checkbox" name="alliance{@$allianceID}" onkeyup="alliance.showInterrelationInformation({@$alliance2->allianceID}, 'block');" onmouseup="alliance.showInterrelationInformation({@$alliance2->allianceID}, 'block');" />
							
							<label class="allianceNameLabel" for="alliance{@$alliance2->allianceID}">
								{@$alliance2}
							</label>
							<div id="interrelation{@$alliance2->allianceID}" class="allianceRank">
								{lang}wot.alliance.rank{/lang}:
								<select size="1" name="alliance{@$alliance2->allianceID}Rank">
									<option value="-1" selected="selected">{lang}wot.alliance.rank.all{/lang}</option>
									<option value="0">{lang}wot.alliance.page.leader{/lang}</option>
									{assign var='ranks' value=$alliance2->getRank()}
									{foreach from=$ranks key='rankID' item='rank'}
										<option value="{@$rankID}">{$rank.0}</option>
									{/foreach}
								</select>
							</div>	
						</div>
					{/foreach}
				</div>
				<div class="formSubmit">
					<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
				</div>
			</form>
		</div>
		
		{include file="footer"}
	</body>
</html>