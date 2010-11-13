{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.apply.title{/lang}</title>
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
			
			<form action="index.php?form=AllianceApply&amp;allianceID={$allianceID}" method="post">
				<div class="formElement{if $errorField == 'allianceName'} formError{/if}">
					<div class="formFieldLabel">
						<label for="applicationText">{lang}wot.alliance.apply.text{/lang}</label>
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
				<div class="formSubmit">
					<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
				</div>
			</form>
		</div>
		
		{include file="footer"}
	</body>
</html>