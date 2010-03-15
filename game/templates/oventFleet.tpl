{assign var='ovents' value=$ovent}
{foreach from=$ovents->getPoolData() key='no' item='ovent'}
	{assign var='time' value=$ovent.time}
	<div class="{@$ovent.passage} {@$ovent.cssClass}">
		{* ships tooltip *}
		{assign var='shipCount' value=0}
		{capture assign='shipStr'}
			{if $this->user->spy_tech >= 8 || $this->user->userID == $ovent.userID}
				{assign var='showShipStr' value=1}
				{foreach from=$ovent.spec key=$specID item=$count}
					<li>{lang}wot.spec.spec{@$specID}{/lang}: {#$count}</li>
					{assign var='shipCount' value=$shipCount+$count}
				{/foreach}
			{else}
			{if $this->user->spy_tech >= 4}			
				{assign var='showShipStr' value=1}
				{foreach from=$ovent.spec key=$specID item=$count}
					<li>{lang}wot.spec.spec{@$specID}{/lang}</li>
					{assign var='shipCount' value=$shipCount+$count}
				{/foreach}
			{else}			
			{if $this->user->spy_tech >= 2}			
				{assign var='showShipStr' value=1}
				{foreach from=$ovent.spec key=$specID item=$count}
					{assign var='shipCount' value=$shipCount+$count}
				{/foreach}
			{/if}
			{/if}
			{/if}
		{/capture}
		{if $showShipStr}
			<ul class="tooltip shipStrList" id="shipStrList{@$c}">
				<li>{lang}wot.ovent.fleet.shipCount{/lang}: {#$shipCount}</li>
				{@$shipStr}
			</ul>
		{/if}
		
		{* resources tooltip*}
		{assign var='showResources' value=0}
		{if $this->user->userID == $ovent.userID}
			<ul class="tooltip resourcesList" id="resourcesList{@$c}">
				{if $ovent.resources.metal > 0}{assign var='showResources' value=1}<li>{lang}wot.global.metal{/lang}: {#$ovent.resources.metal}</li>{/if}
				{if $ovent.resources.crystal > 0}{assign var='showResources' value=1}<li>{lang}wot.global.crystal{/lang}: {#$ovent.resources.crystal}</li>{/if}
				{if $ovent.resources.deuterium > 0}{assign var='showResources' value=1}<li>{lang}wot.global.deuterium{/lang}: {#$ovent.resources.deuterium}</li>{/if}
			</ul>
		{/if}
		
		{* planet links *}
		{if $ovent.ownerID == $this->user->userID}
			{assign var='startOwn' value=true}
		{else}
			{assign var='startOwn' value=false}
		{/if}
		
		{if $ovent.ofiaraID == $this->user->userID}
			{assign var='targetOwn' value=true}
		{else}
			{assign var='targetOwn' value=false}
		{/if}
		{assign var='startCoords' value=$ovent.startCoords}
		{assign var='targetCoords' value=$ovent.targetCoords}
		{include file="planetLink" assign="startPlanet" own=$startOwn id=$ovent.startPlanetID name=$ovent.startPlanetName g=$startCoords.0 s=$startCoords.1 p=$startCoords.2 k=$startCoords.3}
		{include file="planetLink" assign="targetPlanet" own=$targetOwn id=$ovent.targetPlanetID name=$ovent.targetPlanetName g=$targetCoords.0 s=$targetCoords.1 p=$targetCoords.2 k=$targetCoords.3}
		
		{* ... *}
		{lang}wot.ovent.fleet{/lang}
		
		<script type="text/javascript">
			{if $showShipStr}new Tooltip(document.getElementById("shipStrList{@$c}"), document.getElementById("shipStr{@$c}"));{/if}
			{if $showResources}new Tooltip(document.getElementById("resourcesList{@$c}"), document.getElementById("resources{@$c}"));{/if}
		</script>
	</div>
{/foreach}