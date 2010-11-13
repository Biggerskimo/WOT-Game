{include file="documentHeader"}
	<head>
		<title>{lang}wot.planet.actionsPage.title{/lang}</title>
		<script type="text/javascript" src="js/PlanetActions.class.js"></script>
		<script type="text/javascript">
			language = { };
		</script>
		{include file="headInclude"}
	</head>
	<body>
		{*capture assign='additionalTopnavContent'}
			<span class="serverTimeDesc">{lang}wot.global.serverTime{/lang}: <span id="serverTime">{@TIME_NOW|time:"%d.%m.%Y, %H:%M:%S"}</span></span>
			
			<script type="text/javascript">
				var ovent{@$c} = new NTime(document.getElementById("serverTime").childNodes[0]);
			</script>
		{/capture*}
		{include file="topnav"}
		<div class="main content planetActions">
			<fieldset>
				<legend>
					{if $this->planet->planetID != $this->user->id_planet}
						<input type="radio" name="action" id="actionRename" value="rename"{if $errorField != "password"} checked="checked"{/if} />
					{/if}
					<label for="actionRename">
						{lang}wot.planet.actionsPage.action.rename{/lang}
					</label>					
				</legend>
				
				{if $errorField == "newName"}
					<p class="error">
						{lang}wot.planet.actionsPage.error.newName.{@$errorType}{/lang}
					</p>
				{/if}
				
				<form action="index.php?form=PlanetActions" method="post">					
					<div class="planetActionRename" id="renameContainer">
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="galaxy">{lang}wot.planet.actionsPage.newName{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" size="15" maxlength="25" name="newName" id="newName" />
							</div>					
						</div>
						
						<div class="formSubmit">
							<input type="hidden" name="action" value="rename" />
							<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" id="renameSubmit" />
						</div>
					</div>
				</form>
			</fieldset>
			
			{if $this->planet->planetID != $this->user->id_planet}
				<fieldset>
					<legend>
						<input type="radio" name="action" id="actionDelete" value="delete"{if $errorField == "password"} checked="checked"{/if} />
						<label for="actionDelete">
							{lang}wot.planet.actionsPage.action.delete{/lang}
						</label>					
					</legend>
					
					{if $errorField == "password"}
						<p class="error">
							{lang}wot.planet.actionsPage.error.password.{@$errorType}{/lang}
						</p>
					{/if}
					
					<form action="index.php?form=PlanetActions" method="post">					
						<div class="planetActionDelete" id="deleteContainer">
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="password">{lang}wot.planet.actionsPage.password{/lang}</label>
								</div>
								<div class="formField">
									<input type="password" size="15" maxlength="25" name="password" id="password" />
								</div>					
							</div>
							
							<div class="formSubmit">
								<input type="hidden" name="action" value="delete" />
								<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" id="deleteSubmit" />
							</div>
						</div>
					</form>
				</fieldset>
			{/if}
		</div>
		
		<script type="text/javascript">			
			var planetActions = new PlanetActions();
		</script>
		
		{include file="footer"}
	</body>
</html>