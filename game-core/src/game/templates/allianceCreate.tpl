{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.create.title{/lang}</title>
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
		
			<form action="index.php?form=AllianceCreate" method="post">
				<div class="formElement{if $errorField == 'allianceName'} formError{/if}">
					<div class="formFieldLabel">
						<label for="allianceName">{lang}wot.alliance.name{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="allianceName" name="allianceName" value="{$allianceName}" />															
						{if $errorField == 'allianceName'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wot.alliance.create.name.notValid{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wot.alliance.create.name.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				<div class="formElement{if $errorField == 'allianceTag'} formError{/if}">
					<div class="formFieldLabel">
						<label for="allianceTag">{lang}wot.alliance.tag{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="allianceTag" name="allianceTag" value="{$allianceTag}" />															
						{if $errorField == 'allianceTag'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wot.alliance.create.tag.notValid{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wot.alliance.create.tag.notUnique{/lang}{/if}
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