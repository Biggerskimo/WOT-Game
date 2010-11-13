{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.interrelation.apply.title{/lang}</title>
		<script type="text/javascript" src="../js/Alliance.class.js"></script>
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
			
			<form action="index.php?form=AllianceInterrelationApply&amp;allianceID2={$allianceID2}&amp;allianceID={$allianceID}" method="post">
				<div class="formElement{if $errorField == 'applicationText'} formError{/if}">
					<div class="formFieldLabel">
						<label for="applicationText">{lang}wot.alliance.interrelation.apply.text{/lang}</label>
					</div>
					<div class="formField">
						<textarea id="applicationText" name="applicationText" cols="40" rows="20">{$applicationTemplate}</textarea>		
						{if $errorField == 'applicationText'}
							<p class="innerError">
								{lang}wot.alliance.apply.text.notValid{/lang}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						{lang}wot.alliance.interrelation.apply.type{/lang}
					</div>
					<div class="formField allianceInterrelationApplyFormField">
						<ul>
							<li>
								<input type="radio" id="interrelationType1" name="interrelationType" value="1" checked="checked" />
								<label for="interrelationType1">
									{lang}wot.alliance.diplomacy.confederation{/lang}
								</label>
							</li>
								
							<li>
								<input type="radio" id="interrelationType2" name="interrelationType" value="2" />
								<label for="interrelationType2">
									{lang}wot.alliance.diplomacy.nonAgressionPact{/lang}
								</label>
							</li>
							
							<li>
								<input type="radio" id="interrelationType3" name="interrelationType" value="3" />
								<label for="interrelationType3">
									{lang}wot.alliance.diplomacy.war{/lang}
								</label>
							</li>
						</ul>
					</div>
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