{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/fleetViewL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wot.acp.fleet.view{/lang}</h2>
	</div>
</div>
<div class="border borderMarginRemove">

	{foreach from=$fleetData key='revisionNo' item='revision'}
		<fieldset class="content fleetRevisionList">
			<legend>
				{lang}wot.acp.fleet.view.revision{/lang}
			</legend>
			<div class="messageInner">
				<div class="messageBody">
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.revision.timestamp{/lang}</label>
						</div>
						<div class="formField">
							<span>{@$revision.time|time} ({@$revision.time})</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.mission{/lang}</label>
						</div>
						<div class="formField">
							<span>{lang}wot.fleet.mission{@$revision.data.missionID}{/lang} (#{@$revision.data.missionID})</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.revision.event.impact{/lang}</label>
						</div>
						<div class="formField">
							<span>{@$revision.data.impactEventID}</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.revision.event.impact.time{/lang}</label>
						</div>
						<div class="formField">
							<span>{@$revision.data.impactTime|time} ({@$revision.data.impactTime})</span>
						</div>
					</div>
					{if $revision.data.wakeUpEventID}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}wot.fleet.revision.event.wakeUp{/lang}</label>
							</div>
							<div class="formField">
								<span>{@$revision.data.wakeUpEventID}</span>
							</div>
						</div>
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}wot.fleet.revision.event.wakeUp.time{/lang}</label>
							</div>
							<div class="formField">
								<span>{@$revision.data.wakeUpTime|time} ({@$revision.data.wakeUpTime})</span>
							</div>
						</div>
					{/if}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.revision.event.return{/lang}</label>
						</div>
						<div class="formField">
							<span>{@$revision.data.returnEventID}</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.revision.event.return.time{/lang}</label>
						</div>
						<div class="formField">
							<span>{@$revision.data.returnTime|time} ({@$revision.data.returnTime})</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.owner{/lang}</label>
						</div>
						<div class="formField">
							<span>{$revision.data.ownerName} ({@$revision.data.ownerID})</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.ofiara{/lang}</label>
						</div>
						<div class="formField">
							<span>{$revision.data.ofiaraName} ({@$revision.data.ofiaraID})</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.global.metal{/lang}</label>
						</div>
						<div class="formField">
							<span>{#$revision.data.metal}</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.global.crystal{/lang}</label>
						</div>
						<div class="formField">
							<span>{#$revision.data.crystal}</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.global.deuterium{/lang}</label>
						</div>
						<div class="formField">
							<span>{#$revision.data.deuterium}</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.startPlanet{/lang}</label>
						</div>
						<div class="formField">
							<span>{#$revision.data.startPlanetCoords} ({@$revision.data.startPlanetID})</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.targetPlanet{/lang}</label>
						</div>
						<div class="formField">
							<span>{#$revision.data.targetPlanetCoords} ({@$revision.data.targetPlanetID})</span>
						</div>
					</div>
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wot.fleet.ships{/lang}</label>
						</div>
						<div class="formField">
						{assign var='separatorViewed' value=0}
						{* no whitespaces in next line allowed! *}
<span>{foreach from=$revision.fleet key='specID' item='count'}{if $separatorViewed|empty}{assign var='separatorViewed' value=1}{else}, {/if}{lang}wot.spec.spec{@$specID}{/lang}({@$specID}): {#$count}{/foreach}</span>
						</div>
					</div>
					{if $revision.data.ownerIP|isset}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}wot.fleet.revision.ownerIP{/lang}</label>
							</div>
							<div class="formField">
								<span><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url=http://www.db.ripe.net/whois%3Fsearchtext={$revision.data.ownerIP}" class="externalURL">{$revision.data.ownerIP}</a> @ {@$revision.data.ownerIpTime|time} ({@$revision.data.ownerIpTime})</span>
							</div>
						</div>
					{/if}
					{if !$revision.data.ofiaraID|empty}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}wot.fleet.revision.ofiaraIP{/lang}</label>
							</div>
							<div class="formField">
								<span><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url=http://www.db.ripe.net/whois%3Fsearchtext={$revision.data.ofiaraIP}" class="externalURL">{$revision.data.ofiaraIP}</a> @ {@$revision.data.ofiaraIpTime|time} ({@$revision.data.ofiaraIpTime})</span>
							</div>
						</div>
					{/if}
					{if !$revision.data.primaryDestination|empty}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}wot.fleet.primaryDestination{/lang}</label>
							</div>
							<div class="formField">
								<span>{lang}wot.spec.spec{@$revision.data.primaryDestination}{/lang} ({@$revision.data.primaryDestination})</span>
							</div>
						</div>
					{/if}
				</div>
			</div>
		</fieldset>
	{/foreach}
</div>

{include file='footer'}