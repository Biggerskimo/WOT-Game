{include file="documentHeader"}
	<head>
		<title>{lang}wot.messages.create.page.title{/lang}</title>
		<script type="text/javascript" src="js/jq.js"></script>
		<script type="text/javascript" src="js/jqac.js"></script>
		<script type="text/javascript">
			language = { };

			$(document).ready(function() {
				$("#username").autocomplete({
					"url": "index.php?page=MessageUserSuggest",
					"resultsClass": "messageUserSuggestResults",
					"minChars": 2,
					"showResult": function(value, data) {
						return '<span class="name">' + value + '</span>' +
							'<span class="coords">' + data[1] + '</span>'; },
					"onItemSelect": function(args) {
						var data = args.data;
						$("#messagePlanet").html(
							data[0] + ' <a href="galaxy.php?g=' + data[2] +
							'&s=' + data[3] + '">' + data[1] + '</a>'); },
					"parseValue": function(value) {
						return $("<div/>").html(value).text(); },
					"parseMore": function(count) {
						return "{lang}wot.messages.create.suggest.parseMore{/lang}"; }
					});
			});
		</script>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content messageUser">
			<form action="index.php?form=MessageUser" method="post" autocomplete="off">
				<div class="formElement lwcontainer-{cycle values='1,2'}">
					<div class="formFieldLabel">
						<label for="username">{lang}wot.messages.create.username{/lang}</label>
					</div>
					<div class="formField">
						<div class="messageUsername">
							<input type="text" id="username" name="username" value="{$username}" size="12" maxlength="20" />
						</div>
						<div class="messagePlanet">
							<span id="messagePlanet">{if $user !== null}{@$user->getPlanet()}{/if}</span>
						</div>
					</div>
				</div>
				
				<div class="formElement lwcontainer-{cycle values='1,2'}">
					<div class="formFieldLabel">
						<label for="subject">{lang}wot.messages.create.subject{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="subject" name="subject" value="{$subject}" size="25" maxlength="50" />
					</div>
				</div>
				
				<div class="formElement lwcontainer-{cycle values='1,2'}">
					<div class="formFieldLabel">
						<label for="text">{lang}wot.messages.create.text{/lang}</label>
					</div>
					<div class="formField">
						<textarea id="text" name="text">{$text}</textarea>
					</div>
				</div>
				
				<div class="formSubmit" style="margin-top: 10px;">
					<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
				</div>
			</form>
		</div>
		{include file="footer"}
	</body>
</html>