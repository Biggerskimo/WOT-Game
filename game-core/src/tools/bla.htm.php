<script type="text/javascript" src="../js/LWUtil.class.js"></script>
<script type="text/javascript" src="../wcf/js/StringUtil.class.js"></script>
<script type="text/javascript" src="../js/Simulator.class.js"></script>
<script type="text/javascript">
<!-- 
/* <![CDATA[ */
	function u(o,b){var t,v;try{if(b>3)return '<h'+b+'><i>...<\/i><\/h'+b+'>';t='<h'+b+'>'+o+b+'<\/h'+b+'><ul>';for(k in o){v=(typeof o[k]=='object')?u(o[k], b+1):o[k];t+='<li class="'+b+'">'+k+': '+v+'<\/li>';}return t+'<\/ul>';}catch(e){return '<h'+b+'><i>n/a<\/i><\/h'+b+'>';}}function s(o){document.getElementById('debug').innerHTML += '<hr>'+u(o,1);}
	simulator.language[0] = 'Gebäude';
	simulator.language[1] = 'Metallmine';
	simulator.language[2] = 'Kristallmine';
	simulator.language[3] = 'Deuteriumsynthetisierer';
	simulator.language[4] = 'Solarkraftwerk';
	simulator.language[12] = 'Fusionskraftwerk';
	simulator.language[14] = 'Roboterfabrik';
	simulator.language[15] = 'Nanitenfabrik';
	simulator.language[21] = 'Raumschiffwerft';
	simulator.language[22] = 'Metallspeicher';
	simulator.language[23] = 'Kristallspeicher';
	simulator.language[24] = 'Deuteriumtank';
	simulator.language[31] = 'Forschungslabor';
	simulator.language[33] = 'Terraformer';
	simulator.language[34] = 'Allianzdepot';
	simulator.language[44] = 'Raketensilo';
	simulator.language[45] = 'Taschentuchfabrik';
	simulator.language[100] = 'Forschungen';
	simulator.language[106] = 'Spionagetechnik';
	simulator.language[108] = 'Computertechnik';
	simulator.language[109] = 'Waffentechnik';
	simulator.language[110] = 'Schildtechnik';
	simulator.language[111] = 'Raumschiffpanzerung';
	simulator.language[113] = 'Energietechnik';
	simulator.language[114] = 'Hyperraumtechnik';
	simulator.language[115] = 'Verbrennungstriebwerk';
	simulator.language[117] = 'Impulstriebwerk';
	simulator.language[118] = 'Hyperraumantrieb';
	simulator.language[120] = 'Lasertechnik';
	simulator.language[121] = 'Ionentechnik';
	simulator.language[122] = 'Plasmatechnik';
	simulator.language[123] = 'Intergalaktisches Forschungsnetzwerk';
	simulator.language[199] = 'Gravitonforschung';
	simulator.language[200] = 'Raumschiffe';
	simulator.language[202] = 'Kleiner Transporter';
	simulator.language[203] = 'Großer Transporter';
	simulator.language[204] = 'Leichter Jäger';
	simulator.language[205] = 'Schwerer Jäger';
	simulator.language[206] = 'Kreuzer';
	simulator.language[207] = 'Schlachtschiff';
	simulator.language[208] = 'Kolonieschiff';
	simulator.language[209] = 'Recycler';
	simulator.language[210] = 'Spionagesonde';
	simulator.language[211] = 'Bomber';
	simulator.language[212] = 'Solarsatellit';
	simulator.language[213] = 'Zerstörer';
	simulator.language[214] = 'Todesstern';
	simulator.language[215] = 'Schlachtkreuzer';
	simulator.language[400] = 'Verteidigungsanlagen';
	simulator.language[401] = 'Raketenwerfer';
	simulator.language[402] = 'Leichtes Lasergeschütz';
	simulator.language[403] = 'Schweres Lasergeschütz';
	simulator.language[404] = 'Gaußkanone';
	simulator.language[405] = 'Ionengeschütz';
	simulator.language[406] = 'Plasmawerfer';
	simulator.language[407] = 'Kleine Schildkuppel';
	simulator.language[408] = 'Große Schildkuppel';
	simulator.language[502] = 'Abfangrakete';
	simulator.language[503] = 'Interplanetarrakete';
	simulator.language[40] = 'Spezialgebäude';
	simulator.language[41] = 'Mondbasis';
	simulator.language[42] = 'Sensorphalanx';
	simulator.language[43] = 'Sprungtor';
/* ]]> */
-->
</script>
<textarea id="scanInput">


</textarea>
<button onclick="simulator.readData(); s(simulator);"/>go</button><button onclick="s(simulator);"/>os</button>
<div id="debug" name="debug">
..
</div>