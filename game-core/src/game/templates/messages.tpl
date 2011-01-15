{include file="documentHeader"}
	<head>
		<title>{lang}wot.messages.page.title{/lang}</title>		
		<script type="text/javascript" src="js/jq.js"></script>
		<script type="text/javascript" src="js/Messages.class.js"></script>
		{include file="headInclude"}
		<link href="../css/thickbox.css" type="text/css" rel="stylesheet">
		<script type="text/javascript">
			{assign var='checkedCount' value=$this->user->getSetting('checkedMessages')}
			
			language = { };
			language['message.notify.sure'] = "{lang}wot.messages.message.notify.sure{/lang}";
			language['message.notify.done'] = "{lang}wot.messages.message.notify.done{/lang}";
			language['message.ignore.sure'] = "{lang}wot.messages.message.ignore.sure{/lang}";
			language['message.ignore.done'] = "{lang}wot.messages.message.ignore.done{/lang}";

			messages.dblClickHref = "index.php?page=Messages{if $checked === null}&checked=1{/if}";
			messages.checkedCount = {@$checkedCount}; 
		</script>
	</head>
	<body>
		{capture append='additionalTopnavContent'}
			<div class="checkedMessages" id="checkedMessages">
				<span class="checkedMessagesTitle" id="checkedMessagesTitleNone"{if $checkedCount} style="display: none;"{/if}>
					{lang}wot.messages.message.check.none{/lang}
				</span>
				<span class="checkedMessagesTitle" id="checkedMessagesTitleOne"{if $checkedCount != 1} style="display: none;"{/if}>
					{lang}wot.messages.message.check.one{/lang}
				</span>
				<span class="checkedMessagesTitle" id="checkedMessagesTitleMore"{if $checkedCount <= 1} style="display: none;"{/if}>
					{lang}wot.messages.message.check.more{/lang}
				</span>
				<ul class="checkedMessagesActions" id="checkedMessagesActionsSome">
					<li class="uncheckChecked">
						<a href="index.php?action=MessageManipulation&command=uncheckChecked">
							{lang}wot.messages.message.check.uncheck{/lang}
						</a>
					</li>
					<li class="checkAll">
						<a href="index.php?action=MessageManipulation&command=checkAll">
							{lang}wot.messages.message.check.all{/lang}
						</a>
					</li>
					{if $active !== null && $active|count}
						<li class="checkVisible">
							<a href="index.php?action=MessageManipulation&command=checkVisible&amp;folderIDs={@','|implode:$active}">
								{lang}wot.messages.message.check.visible{/lang}
							</a>
						</li>
						<li class="uncheckVisible">
							<a href="index.php?action=MessageManipulation&command=uncheckVisible&amp;folderIDs={@','|implode:$active}">
								{lang}wot.messages.message.check.uncheckVisible{/lang}
							</a>
						</li>
					{/if}
					<li class="deleteChecked">
						<a href="index.php?action=MessageManipulation&command=deleteChecked">
							{lang}wot.messages.message.check.delete{/lang}
						</a>
					</li>
					<li class="deleteUnchecked">
						<a href="index.php?action=MessageManipulation&command=deleteUnchecked">
							{lang}wot.messages.message.check.deleteOthers{/lang}
						</a>
					</li>
					<li class="deleteAll">
						<a href="index.php?action=MessageManipulation&command=deleteAll">
							{lang}wot.messages.message.check.deleteAll{/lang}
						</a>
					</li>
				</ul>
				<ul class="checkedMessagesActions" id="checkedMessagesActionsNone">
					<li class="checkAll">
						<a href="index.php?action=MessageManipulation&command=checkAll">
							{lang}wot.messages.message.check.all{/lang}
						</a>
					</li>
					{if $active !== null && $active|count}
						<li class="checkVisible">
							<a href="index.php?action=MessageManipulation&command=checkVisible&amp;folderIDs={@','|implode:$active}">
								{lang}wot.messages.message.check.visible{/lang}
							</a>
						</li>
					{/if}
					<li class="deleteAll">
						<a href="index.php?action=MessageManipulation&command=deleteAll">
							{lang}wot.messages.message.check.deleteAll{/lang}
						</a>
					</li>
				</ul>
			</div>
		{/capture}
		{include file="topnav"}
		<div class="main content messages">
			{* folders *}
			{if $checked === null && $folders|count}
				<div class="messageFolders">
					<div class="contentDescriptor">
						<div class="check">
							<span class="checkDescription">
								{lang}wot.messages.folders.check{/lang}
							</span>
							<span class="checkAll">
								{assign var='folderKeys' value=$folders|array_keys}
								{if $folders|count != $active|count}
									<a href="index.php?page=Messages&amp;active={@','|implode:$folderKeys}">
										{lang}wot.messages.folders.checkAll{/lang}
									</a>
								{else}
									<a href="index.php?page=Messages">
										{lang}wot.messages.folders.uncheckAll{/lang}
									</a>
								{/if}
							</span>
						</div>
						<div class="name">
							{lang}wot.messages.folders.name{/lang}
						</div>
						<div class="unviewed">
							{lang}wot.messages.folders.unviewed{/lang}
						</div>
						<div class="all">
							{lang}wot.messages.folders.all{/lang}
						</div>
					</div>
					{foreach from=$folders key='folderID' item='folder'}
						<div class="messageFolder {if $folderID|in_array:$active}active{else}inactive{/if}" id="messageFolder{@$folderID}">
							{if $active !== null && $folderID|in_array:$active}
								{if $active|count == 1}
									{assign var='link' value='index.php?page=Messages'}
								{else}
									{assign var='active2' value=$active|array_flip}
									{assign var='activeKey' value=$active2.$folderID}
									{assign var='active2' value=$active}
									{$active2|array_splice:$activeKey:1}
									{assign var='active2' value=','|implode:$active2}
									{assign var='link' value='index.php?page=Messages&amp;active='|concat:$active2}
								{/if}
							{else}
								{if $active === null || !$active|count}
									{assign var='link' value='index.php?page=Messages&amp;active='|concat:$folderID}
								{else}
									{assign var='active2' value=','|implode:$active}
									{assign var='link' value='index.php?page=Messages&amp;active='|concat:$active2:',':$folderID}
								{/if}
							{/if}
							<div class="check">
								<span><a href="{@$link}">&nbsp;</a></span>
							</div>
							<div class="name">
								{if $active !== null && $folderID|in_array:$active && $active|count == 1}
									<a href="index.php?page=Messages">{lang}{$folder->name}{/lang}</a>
								{else}
									<a href="index.php?page=Messages&amp;active={@$folderID}">{lang}{$folder->name}{/lang}</a>
								{/if}
							</div>
							<div class="unviewed">
								{#$folder->unviewedCount}
							</div>
							<div class="all">
								{#$folder->messageCount}
							</div>
						</div>
					{/foreach}
				</div>
			{/if}
			
			{if $nextPage || $pageNo > 1}
				<div class="messageNavigation">
					{if $nextPage}
						<a class="olderMessages" href="index.php?page=Messages&amp;active={@','|implode:$active}&amp;pageNo={@$pageNo+1}">
							{lang}wot.messages.older{/lang}
						</a>
					{/if}
					{if $pageNo > 1}
						<a class="newerMessages" href="index.php?page=Messages&amp;active={@','|implode:$active}&amp;pageNo={@$pageNo-1}">
							{lang}wot.messages.newer{/lang}
						</a>
					{/if}
				</div>
			{/if}
			
			{* messages *}
			{if $messages|count}
				<a name="unread"></a>
				{include file='messagesList' id='messages' messages=$messages}
			{/if}
			
			{if $nextPage || $pageNo > 1}
				<div class="messageNavigation">
					{if $nextPage}
						<a class="olderMessages" href="index.php?page=Messages&amp;active={@','|implode:$active}&amp;pageNo={@$pageNo+1}">
							{lang}wot.messages.older{/lang}
						</a>
					{/if}
					{if $pageNo > 1}
						<a class="newerMessages" href="index.php?page=Messages&amp;active={@','|implode:$active}&amp;pageNo={@$pageNo-1}">
							{lang}wot.messages.newer{/lang}
						</a>
					{/if}
				</div>
			{/if}
		</div>
		{include file='footer'}
	</body>
</html>