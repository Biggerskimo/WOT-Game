{assign var='coords' value=$ovent->coords}
{include file="planetLink" assign="planet" own=1 id=$ovent->planetID name=$ovent->planetName g=$coords.0 s=$coords.1 p=$coords.2 k=$coords.3}
<div class="buildingCompletion">
	{lang}wot.ovent.building{/lang}
</div>