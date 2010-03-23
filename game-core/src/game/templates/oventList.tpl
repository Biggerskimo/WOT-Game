<table class="ovents"{if $id|isset} id="{@$id}"{/if}>
	<thead>
		<tr>
			<th>
				{lang}wot.ovent.time{/lang}
			</th>
			<th>
				{lang}wot.ovent.ovent{/lang}
			</th>
			<th>
				{lang}wot.ovent.type{/lang}
			</th>
		</tr>
	</thead>
	<tbody>
		{counter assign='c' print=false}
		{foreach from=$ovents key='oventID' item='ovent'}
			{if $ovent->time >= TIME_NOW}
				{counter print=false}
				<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}" id="ovent{@$oventID}">
					<td>
						<div>
							<div id="relativeTime{@$c}" class="relativeTime">&nbsp;</div>
							<div id="absoluteTime{@$c}" class="absoluteTime">&nbsp;</div>
						</div>
						<script type="text/javascript">
							new NTime(document.getElementById("relativeTime{@$c}").childNodes[0], new Date({$ovent->time - TIME_NOW} * 1000), -1, -1);
							new NTime(document.getElementById("absoluteTime{@$c}").childNodes[0], new Date({$ovent->time - TIME_NOW} * 1000), 0, -2);
						</script>
					</td>
					<td class="{$ovent->getTemplateName()}">
						{include file=$ovent->getTemplateName()}
					</td>
					<td>
						{lang}wot.ovent.type.{@$ovent->getTemplateName()}{/lang}
						
						<div class="extra">
							{if $ovent->checked}
								<a href="javascript:overview.restoreOvent({@$oventID})">
									<img src="{$dpath}pic/key.gif" alt="{lang}wot.overview.ovent.restore{/lang}">
								</a>
							{else}
								<a href="javascript:overview.hideOvent({@$oventID})">
									<img src="{$dpath}pic/abort.gif" alt="{lang}wot.overview.ovent.hide{/lang}" />
								</a>
							{/if}
						</div>
						<script type="text/javascript">
							overview.registerOvent({@$oventID});
						</script>
					</td>
				</tr>
			{/if}
		{/foreach}
	</tbody>
</table>