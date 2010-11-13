{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.administration.title{/lang}</title>
		<script type="text/javascript" src="js/Alliance.class.js"></script>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<div class="allianceAdministrationActions">
				<ul>
					<li class="lwcontainer-1">
						<a href="index.php?page=AllianceDiplomacy&amp;allianceID={$alliance->allianceID}">
							{lang}wot.alliance.administration.diplomacy{/lang}
						</a>
					</li>
					<li class="lwcontainer-1">
						<a href="index.php?form=AllianceRankList&amp;allianceID={$alliance->allianceID}">
							{lang}wot.alliance.administration.editRights{/lang}
						</a>
					</li>
				</ul>
			</div>
			
			<form action="index.php?form=AllianceAdministration&amp;allianceID={$alliance->allianceID}" method="post">
				<div class="allianceAdministrationTexts">
					<div class="allianceAdministrationTextsButtons">
						<ul>
							<li>
								<a href="javascript:alliance.changeText('externalText')" class="active" id="externalTextLink">
									{lang}wot.alliance.externalText{/lang}
								</a>
							</li>
							<li>
								<a href="javascript:alliance.changeText('internalText')" class="inactive" id="internalTextLink">
									{lang}wot.alliance.internalText{/lang}
								</a>
							</li>
							<li>
								<a href="javascript:alliance.changeText('applicationTemplate')" class="inactive" id="applicationTemplateLink">
									{lang}wot.alliance.applicationTemplate{/lang}
								</a>
							</li>
						</ul>
					</div>
				
					<div class="allianceAdministrationTextsExteralText" id="externalText">
						<textarea cols="40" rows="20" name="externalText">{$alliance->ally_description}</textarea>
					</div>
					<div class="allianceAdministrationTextsInteralText" id="internalText">
						<textarea cols="40" rows="20" name="internalText">{$alliance->ally_text}</textarea>
					</div>
					<div class="allianceAdministrationTextsApplicationTemplate" id="applicationTemplate">
						<textarea cols="40" rows="20" name="applicationTemplate">{$alliance->ally_request}</textarea>
					</div>
				</div>
				
				<div class="allianceAdministrationSettings lwcontainer-{cycle values='1,2'}">
					{if $alliance->nameLastChanged < TIME_NOW - 60 * 60 * 24 * 7}
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="name">{lang}wot.alliance.name{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" id="name" name="name" value="{$alliance->ally_name}" />															
							</div>
						</div>
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="tag">{lang}wot.alliance.tag{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" id="tag" name="tag" value="{$alliance->ally_tag}" />															
							</div>
						</div>
					{/if}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="homepage">{lang}wot.alliance.administration.homepage{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" id="homepage" name="homepage" value="{$alliance->ally_web}" />															
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="image">{lang}wot.alliance.administration.image{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" id="image" name="image" value="{$alliance->ally_image}" />															
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="founder">{lang}wot.alliance.administration.founder{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" id="founder" name="founder" value="{$alliance->ally_owner_range}" />															
						</div>
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