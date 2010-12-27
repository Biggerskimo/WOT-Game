<table class="messages"{if $id|isset} id="{@$id}"{/if}>
	<thead>
		<tr>
			<th>
				{lang}wot.messages.info{/lang}
			</th>
			<th>
				{lang}wot.messages.text{/lang}
			</th>
			<th>
				{lang}wot.messagex.extra{/lang}
			</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$messages key='messageID' item='message'}
			<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}" id="message{@$messageID}">
				<td class="messagesInfoColumn">
					<ul>
						<li class="messageSubject">
							<span class="tupleFirst">{lang}wot.messages.message.subject{/lang}</span>
							<span class="tupleSecond">{$message->subject}</span>
						</li>
						<li class="messageFrom">
							<span class="tupleFirst">{lang}wot.messages.message.from{/lang}</span>
							<span class="tupleSecond">{$message->senderID}</span>
						</li>
						<li class="messageTime">
							<span class="tupleFirst">{lang}wot.messages.message.time{/lang}</span>
							<span class="tupleSecond">{$message->time|time}</span>
						</li>
					</ul>
				</td>
				<td class="messagesTextColumn">
					{$message->text}
				</td>
				<td class="messagesExtraColumn">
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
						<li>
							<a href="javascript:messages.answer({@$messageID});">
								{lang}wot.messages.message.answer{/lang}
							</a>
						</li>
						<li>
							<a href="javascript:messages.notify({@$messageID});">
								{lang}wot.messages.message.notify{/lang}
							</a>
						</li>
					</ul>
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>