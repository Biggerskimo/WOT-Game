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
		<div class="{if !$message->viewed}showMessage{else}hideMessage{/if} lwcontainer-{cycle values='1,2' name='contcyc'} message{if $message->checked && !$hideChecked} messageChecked{/if}" id="message{@$messageID}">
			<div class="messageToggle">
				<a href="javascript:messages.toggle({@$messageID})">&nbsp;</a>
			</div>
			<div class="messageCheck">
				<input type="checkbox" id="checkMessage{@$messageID}" name="checkMessage{@$messageID}"
				 {if $message->checked}checked="checked"{/if}
				 onchange="messages.check({@$messageID}, true)"
				 />
			</div>
			<div class="messageInfo" data-messageID="{@$messageID}">
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
			<div class="messageMore">
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
							<a href="javascript:messages.deleteMsg({@$messageID});">
								{lang}wot.messages.message.delete{/lang}
							</a>
						</li>
						<li>
							<a href="javascript:messages.check({@$messageID}, false);">
								{lang}wot.messages.message.check{/lang}
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
		</div>
	{/foreach}
</div>