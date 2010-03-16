<?xml version="1.0" encoding="iso-8859-1" ?>
<statistics>	
	{foreach from=$rows key=$rank item=$entry}
		{include file=$statEntryTemplate}
	{/foreach}
</statistics>