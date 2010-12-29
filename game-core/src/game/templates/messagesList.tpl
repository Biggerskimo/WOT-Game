<div class="messages"{if $id|isset} id="{@$id}"{/if}>
	<div class="contentDescriptor ">
		<div>
			{lang}wot.messages.info{/lang}
		</div>
		<div>
			{lang}wot.messages.text{/lang}
		</div>
		<div>
			{lang}wot.messages.extra{/lang}
		</div>
	</div>
	{foreach from=$messages key='messageID' item='message'}
		<div class="lwcontainer-{cycle values='1,2' name='contcyc'} message{if $message->remembered && !$hideRemembered} messageRemembered{/if}" id="message{@$messageID}">
			<div class="messageInfo">
				<ul>
					<li class="messageSubject">
						<span class="tupleFirst">{lang}wot.messages.message.subject{/lang}</span>
						<span class="tupleSecond">{$message->subject}</span>
					</li>
					<li class="messageFrom">
						<span class="tupleFirst">{lang}wot.messages.message.from{/lang}</span>
						<span class="tupleSecond">{@$message->getSender()->getSenderName()}</span>
					</li>
					<li class="messageTime">
						<span class="tupleFirst">{lang}wot.messages.message.time{/lang}</span>
						<span class="tupleSecond">{@$message->time|time}</span>
					</li>
				</ul>
			</div>
			<div class="messageText">
				{if $message->getSender()->escape()}
					{$message->text}
				{else}
					{@$message->text}
				{/if}
			</div>
			<div class="messageExtra">
				<ul>
					<li>
						<a href="javascript:messages.delete({@$messageID});">
							{lang}wot.messages.message.delete{/lang}
						</a>
					</li>
					<li>
						<a href="javascript:messages.remember({@$messageID});">
							{lang}wot.messages.message.remember{/lang}
						</a>
					</li>
				</ul>
				
				<ul>
					{foreach from=$message->getSender()->getActions() item='action'}
					<li>
						<a href="{@$action.1}">
							{lang}{@$action.0}{/lang}
						</a>
					</li>
					{/foreach}
				</ul>
			</div>
		</div>
	{/foreach}
</div>