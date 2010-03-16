{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
{*<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/InlineListEdit.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/UserListEdit.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	// data array
	var userData = new Array();

	// language
	var language = new Object();
	language['wcf.global.button.mark']		= '{lang}wcf.global.button.mark{/lang}';
	language['wcf.global.button.unmark']		= '{lang}wcf.global.button.unmark{/lang}';
	language['wcf.global.button.delete']		= '{lang}wcf.global.button.delete{/lang}';
	language['wcf.acp.user.button.sendMail']	= '{lang}wcf.acp.user.button.sendMail{/lang}';
	language['wcf.acp.user.button.exportMail']	= '{lang}wcf.acp.user.button.exportMail{/lang}';
	language['wcf.acp.user.button.assignGroup']	= '{lang}wcf.acp.user.button.assignGroup{/lang}';
	language['wcf.acp.user.deleteMarked.sure']	= '{lang}wcf.acp.user.deleteMarked.sure{/lang}';
	language['wcf.acp.user.markedUsers']		= '{lang}wcf.acp.user.markedUsers{/lang}';

	// additional options
	var additionalOptions = new Array();
	{if $additionalMarkedOptions|isset}{@$additionalMarkedOptions}{/if}

	// permissions
	var permissions = new Object();
	permissions['canEditUser'] = {if $this->user->getPermission('admin.user.canEditUser')}1{else}0{/if};
	permissions['canDeleteUser'] = {if $this->user->getPermission('admin.user.canDeleteUser')}1{else}0{/if};
	permissions['canMailUser'] = {if $this->user->getPermission('admin.user.canMailUser')}1{else}0{/if};
	permissions['canEditMailAddress'] = {if $this->user->getPermission('admin.user.canEditMailAddress')}1{else}0{/if};
	permissions['canEditPassword'] = {if $this->user->getPermission('admin.user.canEditPassword')}1{else}0{/if};

	onloadEvents.push(function() { userListEdit = new UserListEdit(userData, {@$markedUsers}, additionalOptions); });
	//]]>
</script>*}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/{if $searchID}fleetSearch{else}users{/if}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wot.acp.fleet.search{/lang}</h2>
	</div>
</div>

{if $fleets|count}
	<div class="contentHeader">
		{pages print=true assign=pagesLinks link="index.php?page=FleetList&pageNo=%d&searchID=$searchID&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	</div>

	<div class="border">
		<div class="containerHead"><h3>{lang}wot.acp.fleet.search.matches{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnFleetID{if $sortField == 'fleetID'} active{/if}"{* colspan="2"*}><div><a href="index.php?page=FleetList&amp;searchID={@$searchID}&amp;pageNo={@$pageNo}&amp;sortField=fleetID&amp;sortOrder={if $sortField == 'fleetID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wot.fleet.fleetID{/lang}{if $sortField == 'fleetID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUsername"><div><a href="javascript:void(0)">{lang}wot.fleet.owner{/lang}</a></div></th>
					<th class="columnUsername"><div><a href="javascript:void(0)">{lang}wot.fleet.ofiara{/lang}</a></div></th>
					<th class="columnCoords"><div><a href="javascript:void(0)">{lang}wot.fleet.startPlanet{/lang}</a></div></th>
					<th class="columnCoords"><div><a href="javascript:void(0)">{lang}wot.fleet.targetPlanet{/lang}</a></div></th>
					<th class="columnTime{if $sortField == 'impactTime'} active{/if}"><div><a href="index.php?page=FleetList&amp;searchID={@$searchID}&amp;pageNo={@$pageNo}&amp;sortField=impactTime&amp;sortOrder={if $sortField == 'impactTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wot.fleet.impactTime{/lang}{if $sortField == 'impactTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTime{if $sortField == 'returnTime'} active{/if}"><div><a href="index.php?page=FleetList&amp;searchID={@$searchID}&amp;pageNo={@$pageNo}&amp;sortField=returnTime&amp;sortOrder={if $sortField == 'returnTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wot.fleet.returnTime{/lang}{if $sortField == 'returnTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnMission{if $sortField == 'missionID'} active{/if}"><div><a href="index.php?page=FleetList&amp;searchID={@$searchID}&amp;pageNo={@$pageNo}&amp;sortField=missionID&amp;sortOrder={if $sortField == 'missionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wot.fleet.mission{/lang}{if $sortField == 'missionID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$fleets item=fleet}
				<tr class="{cycle values="container-1,container-2" advance=false}" id="fleetRow{@$fleet.0.data.fleetID}">
					<td class="columnFleetID">
						<a href="index.php?page=FleetView&fleetID={@$fleet.0.data.fleetID}&packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{@$fleet.0.data.fleetID}
						</a>
					</td>
					<td class="columnUsername">
						{$fleet.0.data.ownerName}
					</td>
					<td class="columnUsername">
						{$fleet.0.data.ofiaraName}
					</td>
					<td class="columnCoords">
						{$fleet.0.data.startPlanetCoords}
					</td>
					<td class="columnCoords">
						{$fleet.0.data.targetPlanetCoords}
					</td>
					<td class="columnTime">
						{@$fleet.0.data.impactTime|time}
					</td>
					<td class="columnTime">
						{@$fleet.0.data.returnTime|time}
					</td>
					<td class="columnMission">
						{lang}wot.mission.mission{$fleet.0.data.missionID}{/lang}
					</td>
					
					{if $fleet.additionalColumns|isset}{@$fleet.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	<div class="contentFooter">
		{@$pagesLinks}
	</div>
{/if}

{include file='footer'}