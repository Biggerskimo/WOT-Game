{assign var='ovents' value=$ovent->getPoolData()}
{foreach from=$ovents key='no' item='ovent'}
	{counter print=false}
	{assign var='time' value=$ovent.time}
	<div class="{@$ovent.passage} {@$ovent.cssClass} {if $this->user->userID != $ovent.ofiaraID || $this->user->userID == $ovent.ownerID}own{else}foreign{/if}">
		{* ships tooltip *}
		{assign var='shipCount' value=0}
		{capture assign='shipStr'}
			{if $this->user->spy_tech >= 8 || $this->user->userID == $ovent.ownerID}
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
		
		{* resources tooltip *}
		{assign var='showResources' value=0}
		{assign var='resources' value=$ovent.resources}
		{if $this->user->userID == $ovent.ownerID || $ovent.missionID == 3}
			<ul class="tooltip resourcesList" id="resourcesList{@$c}">
				{if $resources.metal > 0}{assign var='showResources' value=1}<li>{lang}wot.global.metal{/lang}: {#$resources.metal}</li>{/if}
				{if $resources.crystal > 0}{assign var='showResources' value=1}<li>{lang}wot.global.crystal{/lang}: {#$resources.crystal}</li>{/if}
				{if $resources.deuterium > 0}{assign var='showResources' value=1}<li>{lang}wot.global.deuterium{/lang}: {#$resources.deuterium}</li>{/if}
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