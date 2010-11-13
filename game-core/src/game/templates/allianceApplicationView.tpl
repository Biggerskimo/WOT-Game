{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.applicationView.title{/lang}</title>
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
			
			<div class="allianceApplicationViewApplication">
				<h3>
					{lang}wot.alliance.applicationView.application{/lang}
				</h3>
				<hr />
				<p>
					{$application}
				</p>
			</div>
		
			<form action="index.php?form=AllianceApplicationView&amp;userID={$user->userID}" method="post">
				<div class="formElement{if $errorField == 'allianceName'} formError{/if}">
					<div class="formFieldLabel">
						<label for="answerText">{lang}wot.alliance.applicationView.text{/lang}</label>
					</div>
					<div class="formField">
						<textarea id="answerText" name="answerText" cols="40" rows="20">{$answerText}</textarea>		
						{if $errorField == 'answerText'}
							<p class="innerError">
								{lang}wot.alliance.applicationView.text.notValid{/lang}
							</p>
						{/if}
					</div>
				</div>
				<div class="formElement{if $errorField == 'allianceName'} formError{/if}">
					<div class="formFieldLabel">
						<legend>{lang}wot.alliance.applicationView.agree.legend{/lang}</legend>
					</div>
					<div class="formField">
						<ul class="formOptions">
							<li>
								<label>
									<input type="radio" name="agreed" value="1" />
									{lang}wot.alliance.applicationView.agree{/lang}
								</label>
							</li>
							<li>
								<label>
									<input type="radio" name="agreed" value="0" />
									{lang}wot.alliance.applicationView.disagree{/lang}
								</label>
							</li>
						</ul>
						{if $errorField == 'answerText'}
							<p class="innerError">
								{lang}wot.alliance.applicationView.text.notValid{/lang}
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