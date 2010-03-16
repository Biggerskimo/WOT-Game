<div id="page">
	<a id="top"></a>
	<div id="userPanel" class="userPanel">
		{if SHOW_CLOCK}
			<p id="date">
				<img src="{@RELATIVE_WCF_DIR}icon/dateS.png" alt="" /> <span>{*@TIME_NOW|fulldate} UTC{if $timezone > 0}+{@$timezone}{else if $timezone < 0}{@$timezone}{/if*}({@TIME_NOW})</span>
			</p>
		{/if}

		<p id="userNote">
			{if $this->user->userID != 0}{lang}wot.header.userNote.user{/lang}{else}{lang}wot.header.userNote.guest{/lang}{/if}
		</p>

		<div id="userMenu">
			<ul>
				{if $this->user->userID != 0}
					<li><a href="index.php?action=UserLogout&amp;u={@$this->user->userID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/logoutS.png" alt="" /> <span>{lang}wot.header.userMenu.logout{/lang}</span></a></li>
					<li><a href="index.php?form=UserProfileEdit{@SID_ARG_2ND}"><img src="../wbb/icon/profileS.png" alt="" /> <span>{lang}wot.header.userMenu.profile{/lang}</span></a></li>
					{if $this->user->getPermission('user.pm.canUsePm')}
						<li {if $this->user->pmUnreadCount} class="new"{/if}><a href="index.php?page=PMList{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/pm{if $this->user->pmUnreadCount}Full{else}Empty{/if}S.png" alt="" /> <span>{lang}wot.header.userMenu.pm{/lang}</span>{if $this->user->pmUnreadCount} ({#$this->user->pmUnreadCount}){/if}</a>{if $this->user->pmTotalCount >= $this->user->getPermission('user.pm.maxPm')} <span class="pmBoxFull">{lang}wcf.pm.userMenu.mailboxIsFull{/lang}</span>{/if}</li>
					{/if}
					{if $this->user->getPermission('admin.general.canUseAcp')}
						<li><a href="acp/index.php?packageID={@PACKAGE_ID}"><img src="../wbb/icon/acpS.png" alt="" /> <span>{lang}wot.header.userMenu.acp{/lang}</span></a></li>
					{/if}

				{else}
					<li><a href="index.php?form=UserLogin{@SID_ARG_2ND}" id="loginButton"><img src="{@RELATIVE_WCF_DIR}icon/loginS.png" alt="" /> <span>{lang}wot.header.userMenu.login{/lang}</span></a>

					{if !LOGIN_USE_CAPTCHA}
						<div class="hidden" id="loginBox">
							<form method="post" action="index.php?form=UserLogin" class="container-1">
								<div>
									<div>
										<input tabindex="1" type="text" class="inputText" id="loginUsername" name="loginUsername" value="{lang}wcf.user.username{/lang}" />
										<input tabindex="2" type="password" class="inputText" name="loginPassword" value="" />
										{if $this->session->requestMethod == 'GET'}<input type="hidden" name="url" value="{$this->session->requestURI}" />{/if}
										{@SID_INPUT_TAG}
										<input tabindex="4" type="image" class="inputImage" src="{@RELATIVE_WCF_DIR}icon/submitS.png" />
									</div>
									<label><input tabindex="3" type="checkbox" id="useCookies" name="useCookies" value="1" /> {lang}wot.header.login.useCookies{/lang}</label>
								</div>
							</form>
						</div>

						<script type="text/javascript">
							//<![CDATA[
							var loginFormVisible = false;
							function showLoginForm() {
								var loginBox = document.getElementById("loginBox");

								if (loginBox) {
									if (!loginFormVisible) {
										loginBox.className = "border loginPopup";
										loginFormVisible = true;
									}
									else {
										loginBox.className = "hidden";
										loginFormVisible = false;
									}
								}

								return false;
							}

							document.getElementById('loginButton').onclick = function() { return showLoginForm(); };
							document.getElementById('loginButton').ondblclick = function() { document.location.href = fixURL('index.php?form=UserLogin{@SID_ARG_2ND_NOT_ENCODED}'); };
							document.getElementById('loginUsername').onfocus = function() { if (this.value == '{lang}wcf.user.username{/lang}') this.value=''; };
							document.getElementById('loginUsername').onblur = function() { if (this.value == '') this.value = '{lang}wcf.user.username{/lang}'; };
							//]]>
						</script>
					{/if}

					</li>
					{if !REGISTER_DISABLED}<li><a href="index.php?page=Register{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/registerS.png" alt="" /> <span>{lang}wot.header.userMenu.register{/lang}</span></a></li>{/if}

					{if $this->language->countAvailableLanguages() > 1}
						<li><a id="changeLanguage" class="hidden"><img src="{@RELATIVE_WCF_DIR}icon/language{@$this->language->getLanguageCode()|ucfirst}S.png" alt="" /> <span>{lang}wot.header.userMenu.changeLanguage{/lang}</span></a>
							<div class="hidden" id="changeLanguageMenu">
								<ul>
									{foreach from=$this->language->getAvailableLanguageCodes() item=guestLanguageCode key=guestLanguageID}
										<li{if $guestLanguageID == $this->language->getLanguageID()} class="active"{/if}><a href="index.php?l={$guestLanguageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/language{@$guestLanguageCode|ucfirst}S.png" alt="" /> <span>{lang}wcf.global.language.{@$guestLanguageCode}{/lang}</span></a></li>
									{/foreach}
								</ul>
							</div>
							<script type="text/javascript">
								//<![CDATA[
								onloadEvents.push(function() { document.getElementById('changeLanguage').className=''; });
								popupMenuList.register('changeLanguage');
								//]]>
							</script>
							<noscript>
								<form method="get" action="index.php">
									<div>
										<label><img src="{@RELATIVE_WCF_DIR}icon/language{@$this->language->getLanguageCode()|ucfirst}S.png" alt="" />
											<select name="l" onchange="this.form.submit()">
												{htmloptions options=$this->language->getLanguages() selected=$this->language->getLanguageID()}
											</select>
										</label>
										{@SID_INPUT_TAG}
										<input type="image" class="inputImage" src="{@RELATIVE_WCF_DIR}icon/submitS.png" />
									</div>
								</form>
							</noscript>
						</li>
					{/if}
				{/if}
			</ul>
		</div>
	</div>

	{* --- quick search controls ---
	 * $searchScript=search script; default=index.php?form=search
	 * $searchFieldName=name of the search input field; default=q
	 * $searchFieldValue=default value of the search input field; default=content of $query
	 * $searchFieldTitle=title of search input field; default=language variable wbb.header.search.query
	 * $searchFieldOptions=special search options for popup menu; default=empty
	 * $searchExtendedLink=link to extended search form; default=index.php?form=search{@SID_ARG_2ND}
	 * $searchHiddenFields=optional hidden fields; default=empty
	 * $searchShowExtendedLink=set to false to disable extended search link; default=true
	 *}

	{if !$searchScript|isset}{assign var='searchScript' value='index.php?form=search'}{/if}
	{if !$searchFieldName|isset}{assign var='searchFieldName' value='q'}{/if}
	{if !$searchFieldValue|isset && $query|isset}{assign var='searchFieldValue' value=$query}{/if}
	{if !$searchFieldTitle|isset}{assign var='searchFieldTitle' value='{lang}wot.header.search.query{/lang}'}{/if}
	{if !$searchFieldOptions|isset}
		{capture assign=searchFieldOptions}
			<li><a href="index.php?form=search&amp;action=unread{@SID_ARG_2ND}">{lang}wot.search.unreadPosts{/lang}</a></li>
			<li><a href="index.php?form=search&amp;action=unreplied{@SID_ARG_2ND}">{lang}wot.search.unrepliedThreads{/lang}</a></li>
			<li><a href="index.php?form=search&amp;action=24h{@SID_ARG_2ND}">{lang}wot.search.threadsOfTheLast24Hours{/lang}</a></li>
		{/capture}
	{/if}
	{if !$searchExtendedLink|isset}{assign var='searchExtendedLink' value='index.php?form=search'|concat:SID_ARG_2ND}{/if}
	{if !$searchShowExtendedLink|isset}{assign var='searchShowExtendedLink' value=true}{/if}

	<div id="header" class="border">
		{include file=ressources}
		<div id="logo">
			<h1 class="pageTitle"><a href="index.php?page=Index{@SID_ARG_2ND}">{PAGE_TITLE}</a></h1>
			{if $this->getStyle()->getVariable('page.logo.image')}
				<a href="index.php?page=Index{@SID_ARG_2ND}" class="pageLogo">
					<img src="{$this->getStyle()->getVariable('page.logo.image')}" title="{PAGE_TITLE}" alt="" />
				</a>
			{/if}
		</div>

		{include file=headerMenu}
	</div>

{* user messages system*}
{capture append=userMessages}
	{if $this->user->userID}

		{if $this->user->activationCode && REGISTER_ACTIVATION_METHOD == 1}<p class="warning">{lang}wcf.user.register.needsActivation{/lang}</p>{/if}

		{if $this->session->isNew}<p class="info">{lang}wot.header.welcomeBack{/lang}</p>{/if}

		{if $this->user->showPmPopup && $this->user->pmOutstandingNotifications && $this->user->getOutstandingNotifications()|count > 0}
			<div class="info" id="pmOutstandingNotifications">
				<a href="index.php?page=PM&amp;action=disableNotifications{@SID_ARG_2ND}" onclick="return (((new AjaxRequest()).openGet(this.href + '&ajax=1') && (document.getElementById('pmOutstandingNotifications').style.display = 'none')) ? false : false)" class="close"><img src="{@RELATIVE_WCF_DIR}icon/pmCancelS.png" alt="" title="{lang}wcf.pm.notification.cancel{/lang}" /></a>
				<p>{lang}wcf.pm.notification.report{/lang}</p>
				<ul>
					{foreach from=$this->user->getOutstandingNotifications() item=outstandingNotification}
						<li>
							<a href="index.php?page=PMView&amp;pmID={@$outstandingNotification->pmID}{@SID_ARG_2ND}#pm{@$outstandingNotification->pmID}">{$outstandingNotification->subject}</a> von <a href="index.php?page=User&amp;userID={@$outstandingNotification->userID}{@SID_ARG_2ND}">{$outstandingNotification->username}</a>
						</li>
					{/foreach}
				</ul>
			</div>
		{/if}

	{elseif !$this->session->spiderID}

		{if $this->session->isNew}<p class="info">{lang}wcf.user.register.welcome{/lang}</p>{/if}

	{/if}
{/capture}