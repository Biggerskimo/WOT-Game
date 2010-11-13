{include file="documentHeader"}
	<head>
		<title>{lang}wot.alliance.rankList.title{/lang}</title>
		{include file="headInclude"}
	</head>
	<body>
		{include file="topnav"}
		<div class="main content">
			<form action="index.php?form=AllianceRankList&amp;allianceID={@$alliance->allianceID}" method="post">
				<div class="rankList">
					<table class="tableList">
						<thead>
							<tr class="tableHead">
								<th colspan="2" rowspan="2">
									<div>
										<p>
											{lang}wot.alliance.rank{/lang}
										</p>
									</div>
								</th>
								{section loop=10 start=1 name='i'}
									<th>
										<div>
											{assign var='langVarName' value='wot.alliance.right'|concat:$i}
											<img src="../images/r{@$i}.png" alt="{$this->getLanguage()->get($langVarName)}" title="{$this->getLanguage()->get($langVarName)}" />
										</div>
									</th>
								{/section}
							</tr>
						</thead>
						<tbody>
							{assign var='ranks' value=$alliance->getRank()}
							{foreach from=$ranks key='rankID' item='rank'}
								<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
									{foreach from=$rank key='rightID' item='value'}
										<td class="column">
											{if $rightID == 0}
												{* show name *}
												<input type="text" name="rank{@$rankID}right{@$rightID}" value="{$value}" />
											
												{* show delete button *}
											</td>
											<td class="column">
												<a href="index.php?action=AllianceRankDelete&amp;rankID={@$rankID}&amp;allianceID={@$alliance->allianceID}" onclick="return confirm('{lang}wot.alliance.rank.delete.sure{/lang}');">
													<img src="{$dpath}pic/abort.gif" alt="{lang}wot.alliance.rank.delete{/lang}" />
												</a>
											{else}
												{* show checkbox *}
												{if $alliance->getRank(true, $rightID)}
													<input type="checkbox" name="rank{@$rankID}right{@$rightID}"{if $alliance->getRank($rankID, $rightID)} checked="checked"{/if} />
												{else}
													{if $alliance->getRank($rankID, $rightID)}
														+
													{else}
														-
													{/if}
												{/if}
											{/if}
										</td>
									{/foreach}
								</tr>
							{/foreach}
							<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
								<th class="column" colspan="11">
									<span class="allianceRankListNewRank">
										{lang}wot.alliance.rank.new{/lang}
									</span>
								</th>
							</tr>
							<tr class="lwcontainer-{cycle values='1,2' name='contcyc'}">
								<td class="column">
									<input type="text" name="rankNewright0" />
								</td>
								<td>
								</td>
								{section loop=10 start=1 name='i'}
									<td>
										{if $alliance->getRank(true, $i)}
											<input type="checkbox" name="rankNewright{@$i}" />
										{else}
											-
										{/if}
									</td>
								{/section}
							</tr>
						</tbody>
					</table>
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