<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/
  //functions.php


function add_points($resources,$userid){

	return false;
}

function remove_points($resources,$userid){

	return false;
}
//
// Comprueba si un usario existe
//
function check_user(){
	global $game_config;
	//obtenemos las cookies y o userdata
	$row = checkcookies();

	if($row != false){
		global $user;
		$user = $row;

		if($user['banned']) {
			$bans = doquery("SELECT * FROM {{table}} WHERE who = '".$user['username']."'", 'banned');
			$b = mysql_fetch_assoc($bans);
			message('Du bist bis '.date("d.m.Y G:i:s",$b['longer']).' von <a href="mailto:'.$b['email'].'?subject=banned:'.$b['who'].'">'.$b['author'].'</a> gesperrt. Grund:<br><br>'.$b['theme'],'Gebannt');
		}
		return true;
	}
	return false;

}

//
// Obtiene una array de los datos de un jugador.
//
function get_userdata(){
 echo "pendiente";

}

//
// Comprueba si es una direccion de email valida
//
function is_email($email){

	return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i",$email));

}

//
// Sirve para leer las cookies.
//
function checkcookies(){
	global $lang,$game_config,$ugamela_root_path,$phpEx, $user;
	//mas adelante esta variable formara parte de la $game_config
	includeLang('cookies');
	include($ugamela_root_path.'config.'.$phpEx);
	$row = false;

	if (isset($_COOKIE[$game_config['COOKIE_NAME']]))
	{
		// Formato de la cookie:
		// {ID} {USERNAME} {PASSWORDHASH} {REMEMBERME}
		$theuser = explode(" ",$_COOKIE[$game_config['COOKIE_NAME']]);
		$query = doquery("SELECT * FROM {{table}} LEFT JOIN ugml_stat ON id = userID WHERE id='".WCF::getUser()->userID."'", "users");
		//$sql = "SELECT activityPoints FROM wcf".WCF_N."_user WHERE userID = '".WCF::getUser()->userID."'";
		//$boardQry = WCF::getDB()->getFirstRow($sql);
		if (mysql_num_rows($query) != 1)
		{
			message($lang['cookies']['Error1']);
		}

		$row = mysql_fetch_array($query);
		/*if ($row["id"] != $theuser[0])
		{
			message($lang['cookies']['Error2']);
		}

		if (md5($row["password"]."--".$dbsettings["secretword"]) !== $theuser[1])
		{
			message($lang['cookies']['Error3']);
		}*/
		// Si llegamos hasta aca... quiere decir que la cookie es valida,
		// entonces escribimos una nueva.
		$newcookie = implode(" ",$theuser);
		if($theuser[2] == 1){ $expiretime = time()+31536000;}else{ $expiretime = 0;}
		setcookie ($game_config['COOKIE_NAME'], $newcookie, $expiretime, "/", "", 0);
		//doquery("UPDATE {{table}} SET onlinetime=".time().", user_lastip='{$_SERVER['REMOTE_ADDR']}' WHERE id='{$theuser[0]}' LIMIT 1", "users");
	}
	unset($dbsettings);
	if(!$row) $row = $user;
	// correct rank & points
	$row['rank'] = $row['rankPoints'];
	$row['points_points'] = $row['points'] * 1000;
	$row['dilizium'] += WCF::getUser()->additionalDilizium;
	$row['dilizium'] -= $row['lostDilizium'];
	return $row;
}

//
//	Funcion de parce
//
function parsetemplate($template, $array){

	foreach($array as $a => $b) {
		$template = str_replace("{{$a}}", $b, $template);
	}
	return $template;
}
//
//
//
function gettemplate($templatename){ //OpenGame .. $skinname = 'FinalWars'
	global $ugamela_root_path;

	$filename =  $ugamela_root_path . TEMPLATE_DIR . TEMPLATE_NAME . '/' . $templatename . ".tpl";
	return ReadFromFile($filename);

}

// to get the language texts
function includeLang($filename,$ext='.mo'){
	global $ugamela_root_path,$lang;

	include($ugamela_root_path."language/".DEFAULT_LANG.'/'.$filename.$ext);

}

//
// Leer y Guardar archivos
//
function ReadFromFile($filename){

	$f = @fopen($filename,"r");
	$content = @fread($f,filesize($filename));
	@fclose($f);
	return $content;

}

function SaveToFile($filename,$content){

	$f = fopen($filename,"w");
	fputs($f,"$content");
	fclose($f);

}

//**************************************************************************
//
//	FUNCIONES PARA REVISAR!!!!!!!!!!
//
//**************************************************************************

function message($mes,$title='Error',$dest = "",$time = "3"){

	$parse['color'] = $color;
	$parse['title'] = $title;
	$parse['mes'] = $mes;

	$page .= parsetemplate(gettemplate('message_body'), $parse);

	display($page,$title,false,(($dest!='')?"<meta http-equiv=\"refresh\" content=\"$time;URL=javascript:self.location='$dest';\">":''));

}

function display($page,$title = '',$topnav = true,$metatags=''){
	global $link,$game_config,$debug,$user;

	//die('---~|~---');

	echo_head($title,$metatags);

	if($topnav){ echo_topnav();}
	echo '<div id="content">';
	echo "<center>\n$page\n</center>\n";
	//Muestra los datos del debuger.
	if($user['authlevel']==1||$user['authlevel']==3){
		if($game_config['debug']==1) $debug->echo_log();
	}

	echo echo_foot();
	if(isset($link)) mysql_close();
	die();
}

function echo_foot(){
	global $game_config,$lang;
	//$parse['copyright'] = $game_config['copyright'];
	$parse['TranslationBy'] = $lang['TranslationBy'];
	echo parsetemplate(gettemplate('overall_footer'), $parse);
}

function CheckUserExist($user){
  global $lang,$link;

	if(!$user){
		if(isset($link)) mysql_close();
		error($lang['Please_Login'],$lang['Error']);
	}
}

function pretty_time($seconds){
	//Divisiones, y resto. Gracias Prody
	$day = floor($seconds / (24*3600));
	$hs = floor($seconds / 3600 % 24);
	$min = floor($seconds  / 60 % 60);
	$seg = floor($seconds / 1 % 60);

	$time = '';//la entrada del $time
	if($day != 0){ $time .= $day.'d ';}
	if($hs != 0){ $time .= $hs.'h ';}
	if($min != 0){ $time .= $min.'m ';}
	$time .= $seg.'s';

	return $time;//regresa algo como "[[[0d] 0h] 0m] 0s"
}

function pretty_time_hour($seconds){
	//Divisiones, y resto. Gracias Prody
	$min = floor($seconds  / 60 % 60);

	$time = '';//la entrada del $time

	if($min != 0){ $time .= $min.'min ';}
	return $time;//regresa algo como "[[[0d] 0h] 0m] 0s"
}

function echo_topnav(){

	global $user, $planetrow, $galaxyrow,$mode,$messageziel,$gid,$lang;


	if(!$user){return;}
	//if(!$planetrow){ $planetrow = doquery("SELECT * FROM {{table}} WHERE id ={$user['current_planet']}","planets",true);}
	//if(!$planetrow) $planetrow = (array)LWCore::getPlanet();
	
	//if($planetrow['galaxy'] == 3 && $planetrow['system'] == 139) echo print_r($planetrow, true).'<br /><br />';
	calculate_resources_planet($planetrow);//Actualizacion de rutina
	//if(!$galaxyrow){ $galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet = '".$planetrow["id"]."'","galaxy",true);}
	$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

	//-[Arrays]------------------------------------------------
	$parse = $lang;
	$parse['dpath'] = $dpath;
	$parse['image'] = $planetrow['image'];
	/*
	  pequeño loop para agregar todos los planetas disponibles del mismo jugador...
	*/
	$parse['planetlist'] = '';
	//pedimos todos los planetas que coincidan con el id del due�.
	$planets_list = doquery("SELECT id,name,galaxy,system,planet FROM {{table}} WHERE id_owner='{$user['id']}' ORDER BY sortID ASC","planets");
	while($p = mysql_fetch_array($planets_list)){
		/*
		  Cuando alguien selecciona destruir planeta, hay un tiempo en el que se vacia el slot
		  del planeta, es mas que nada para dar tiempo a posible problema de hackeo o robo de cuenta.
		*/
		if($p["destruyed"] == 0){
			//$pos_galaxy = doquery("SELECT * FROM {{table}} WHERE id_planet = {$p[id]}","galaxy",true);
			$parse['planetlist'] .= "<option ";
			if($p["id"] == $user["current_planet"]) $parse['planetlist'] .= 'selected="selected" ';//Se selecciona el planeta actual
			$parse['planetlist'] .= 'value="?cp='.$p['id'];
			if(isset($_GET['mode'])) $parse['planetlist'] .= '&amp;mode='.$_GET['mode'];
			if(isset($_GET['gid'])) $parse['planetlist'] .= '&amp;gid='.$_GET['gid'];
			if(isset($_GET['messageziel'])) $parse['planetlist'] .= '&amp;messageziel='.$_GET['messageziel'];
			$parse['planetlist'] .= '&amp;re=0">';
			//Nombre [galaxy:system:planet]
			$parse['planetlist'] .= "{$p['name']} [{$p['galaxy']}:{$p['system']}:{$p['planet']}]</option>";
		}
	}
	/*
	  Muestra los recursos, e indica si estos sobrepasan la capacidad de los almacenes
	*/
	$resourceProducer = LWCore::getPlanet()->getProductionHandler()->getProductorObject('resource');
	$energyProd = $resourceProducer->getProduction('energy');
	$energy = pretty_number((($energyProd[1] - $energyProd[0]) * (-3600)))."/".pretty_number(($energyProd[0] * 3600));
	//energy
	if($energyProd[1] > $energyProd[0]){
		$parse['energy'] = colorRed($energy);
	}else{$parse['energy'] = $energy;}
	//metal
	$metal = pretty_number(LWCore::getPlanet()->metal);
	$red = false;
	if($resourceProducer->getProduction('metal') < 0) {
		if(LWCore::getPlanet()->metal < $resourceProducer->getSignificantLimit('metal')) {
			$red = true;
		}
	} else {
		if(LWCore::getPlanet()->metal > $resourceProducer->getSignificantLimit('metal')) {
			$red = true;
		}		
	}
	if($red){
		$parse['metal'] = colorRed($metal);
	}else{$parse['metal'] = $metal;}
	//cristal
	$crystal = pretty_number(LWCore::getPlanet()->crystal);
	$red = false;
	if($resourceProducer->getProduction('crystal') < 0) {
		if(LWCore::getPlanet()->crystal < $resourceProducer->getSignificantLimit('crystal')) {
			$red = true;
		}
	} else {
		if(LWCore::getPlanet()->crystal > $resourceProducer->getSignificantLimit('crystal')) {
			$red = true;
		}		
	}
	if($red){
		$parse['crystal'] = colorRed($crystal);
	}else{$parse['crystal'] = $crystal;}
	//deuterium
	$deuterium = pretty_number(LWCore::getPlanet()->deuterium);
	$red = false;
	if($resourceProducer->getProduction('deuterium') < 0) {
		if(LWCore::getPlanet()->deuterium < $resourceProducer->getSignificantLimit('deuterium')) {
			$red = true;
		}
	} else {
		if(LWCore::getPlanet()->deuterium > $resourceProducer->getSignificantLimit('deuterium')) {
			$red = true;
		}		
	}
	if($red){
		$parse['deuterium'] = colorNumber($deuterium);
	}else{$parse['deuterium'] = $deuterium;}

	//esto es un hecho!
	echo parsetemplate(gettemplate('topnav'),$parse);

}


function echo_head($title = '',$metatags=''){

	global $user,$lang;

	$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

	$parse = $lang;
	$parse['dpath'] = $dpath;
	$parse['title'] = $title;
	$parse['META_TAG'] = ($metatags)?$metatags:'';
	$parse['floten'] = "";
	
	$parse['META_TAG'] .= '
		<script type="text/javascript">
			var serverTime = '.TIME_NOW.';
		</script>';
	
	echo parsetemplate(gettemplate('simple_header'), $parse);

}

function calculate_resources_planet(&$planet, $timeArg = null){
	global $resource, $game_config;

	$time = time();
	
	LWCore::getPlanet()->calculateResources($time);
	
	return;
}

function check_field_current(&$planet){
	/*
	  Esta funcion solo permite actualizar la cantidad de campos en un planeta.
	*/
	global $resource;
	//sumatoria de todos los edificios disponibles
	$cfc = $planet[$resource[1]]+$planet[$resource[2]]+$planet[$resource[3]];
	$cfc += $planet[$resource[4]]+$planet[$resource[12]]+$planet[$resource[14]];
	$cfc += $planet[$resource[15]]+$planet[$resource[21]]+$planet[$resource[22]];
	$cfc += $planet[$resource[23]]+$planet[$resource[24]]+$planet[$resource[31]];
	$cfc += $planet[$resource[33]]+$planet[$resource[34]]+$planet[$resource[44]];

	//Esto ayuda a ahorrar una query...
	if($planet['field_current'] != $cfc){
		$planet['field_current'] = $cfc;
		doquery("UPDATE {{table}} SET field_current=$cfc WHERE id={$planet['id']}",'planets');
	}
}

function check_abandon_planet(&$planet){

	if($planet['destruyed'] <= time()){
		//Borrando el planeta...
		doquery("DELETE FROM {{table}} WHERE id={$planet['id']}",'planets');
		//Borrando referencias en la galaxia...
		doquery("UPDATE {{table}} SET id_planet=0 WHERE id_planet={$planet['id']}",'galaxy');

	}
}

function check_building_progress($planet){
	/*
	  Esta funcion es utilizada en el Overview.
	  Indica si se esta construyendo algo en el planeta
	*/
	if($planet['b_building'] > time()) return true;

}

function is_tech_available($user,$planet,$i){//comprueba si la tecnologia esta disponible

	global $requeriments,$resource;

	if(isset($requeriments[$i])){ //se comprueba si se tienen los requerimientos necesarios

		$enabled = true;
		foreach($requeriments[$i] as $r => $l){

			if(@$user[$resource[$r]] && $user[$resource[$r]] >= $l){
			// break;
			}elseif($planet[$resource[$r]] && $planet[$resource[$r]] >= $l){
				$enabled = true;
			}else{
				return false;
			}
		}
		return $enabled;
	}else{
		return true;
	}
}

function is_buyable($user,$planet,$i,$userfactor=true){//No usado por el momento...

	global $pricelist,$resource,$lang;

	$level = (isset($planet[$resource[$i]])) ? $planet[$resource[$i]] : $user[$resource[$i]];
  $is_buyeable = true;
	//array
  $array = array('metal'=>$lang["Metal"],'crystal'=>$lang["Crystal"],'deuterium'=>$lang["Deuterium"],'energy_max'=>$lang["Energy"]);
  //loop
  foreach($array as $a => $b){

    if(@$pricelist[$i][$a] != 0){
      //echo "$b: ";
      if($userfactor)
        $cost = floor($pricelist[$i][$a] * pow($pricelist[$i]['factor'],$level));
      else
        $cost = floor($pricelist[$i][$a]);

      if($cost > $planet[$a]){
        $is_buyeable = false;

      }

    }

  }
	return $is_buyeable;
}

function echo_price($user,$planet,$i,$userfactor=true){//Usado
	global $pricelist,$resource,$lang;

	if($userfactor)
	$level = ($planet[$resource[$i]]) ? $planet[$resource[$i]] : $user[$resource[$i]];

	$is_buyeable = true;

	$array = array('metal'=>$lang["Metal"],'crystal'=>$lang["Crystal"],'deuterium'=>$lang["Deuterium"],'energy'=>$lang["Energy"]);
	echo "{$lang['Requires']}: ";
	foreach($array as $a => $b){

	if($pricelist[$i][$a] != 0){
	echo "$b: ";
	if($userfactor)
	$cost = floor($pricelist[$i][$a] * pow($pricelist[$i]['factor'],$level));
	else
	$cost = floor($pricelist[$i][$a]);

	if($cost > $planet[$a]){
		echo '<b style="color:red;"> <t title="-'.pretty_number($cost-$planet[$a]).'"><span class="noresources">'.pretty_number($cost)."</span></t></b> ";
		$is_buyeable = false;
	}else{
		echo '<b style="color:lime;"> <t title="+'.-pretty_number($cost-$planet[$a]).'"><span class="noresources">'.pretty_number($cost)."</span></t></b> ";
	}
	}
	}

	return $is_buyeable;

}

function rest_price($user,$planet,$i,$userfactor=true){//Usado
  global $pricelist,$resource,$lang;

  if($userfactor)
    $level = ($planet[$resource[$i]]) ? $planet[$resource[$i]] : $user[$resource[$i]];

  $array = array('metal'=>$lang["Metal"],'crystal'=>$lang["Crystal"],'deuterium'=>$lang["Deuterium"],'energy_max'=>$lang["Energy"]);

  $str .= '<br><font color="#7f7f7f">Reszta: ';
  foreach($array as $a => $b){

    if($pricelist[$i][$a] != 0){
      $str .= "$b: ";
      if($userfactor)
        $cost = floor($pricelist[$i][$a] * pow($pricelist[$i]['factor'],$level));
      else
        $cost = floor($pricelist[$i][$a]);

      if($cost < $planet[$a]){
        $str .= '<b style="color: rgb(95, 127, 108);">'.pretty_number($planet[$a]-$cost)."</b> ";
      }else{
        $str .= '<b style="color: rgb(127, 95, 96);">'.pretty_number($planet[$a]-$cost)."</b> ";
      }
    }
  }
  echo '</font>';
  return $str;

}

function is_buyeable($user,$planet,$i,$userfactor=true){//Usado
  global $pricelist,$resource,$lang;

  if($userfactor)
    $level = ($planet[$resource[$i]]) ? $planet[$resource[$i]] : $user[$resource[$i]];
  $is_buyeable = true;
  $array = array('metal','crystal','deuterium','energy_max');
  foreach($array as $a){

    if($pricelist[$i][$a] != 0){
      if($userfactor)
        $cost = floor($pricelist[$i][$a] * pow($pricelist[$i]['factor'],$level));
      else
        $cost = floor($pricelist[$i][$a]);
      if($cost > $planet[$a]){
        $is_buyeable = false;
      }
    }
  }
  return $is_buyeable;

}

function price($user,$planet,$i,$userfactor=true){//Usado
	global $pricelist,$resource,$lang;

	if($userfactor)
	$level = ($planet[$resource[$i]]) ? $planet[$resource[$i]] : $user[$resource[$i]];

	$is_buyeable = true;

	$array = array('metal'=>$lang["Metal"],'crystal'=>$lang["Crystal"],'deuterium'=>$lang["Deuterium"],'energy_max'=>$lang["Energy"]);
	$text = "{$lang['Requires']}: ";
	foreach($array as $a => $b){

		if($pricelist[$i][$a] != 0){
			$text .= "$b: ";

			if($userfactor){
				$cost = floor($pricelist[$i][$a] * pow($pricelist[$i]['factor'],$level));
			}else{
				$cost = floor($pricelist[$i][$a]);
			}
			if($cost > $planet[$a]){
				$text .= '<b style="color:red;"> <t title="-'.pretty_number($cost-$planet[$a]).'"><span class="noresources">'.pretty_number($cost)."</span></t></b> ";
				$is_buyeable = false;//style="cursor: pointer;"
			}else{
				$text .= '<b style="color:lime;"> <span class="noresources">'.pretty_number($cost).'</span></b> ';
			}
		}
	}
	return $text;

}

function building_time($time){
  global $lang;

  return "<br>{$lang['ConstructionTime']}: ".pretty_time($time);

  //a futuro...
  //echo "La investigacion puede ser iniciada en: 14d 23h 12m 2s";
}

function get_building_time($user,$planet,$i){//solo funciona con los edificios y talvez con las investigaciones
	global $pricelist,$resource,$reslist,$game_config;
  /*
    Formula sencilla para mostrar los costos de construccion.


    Mina de Metal: 60*1,5^(nivel-1) Metal y 15*1,5^(nivel-1) Cristal
    Mina de Cristal: 48*1,6^(nivel-1) Metal y 24*1,6^(nivel-1) Cristal
    Sintetizador de Deuterio: 225*1,5^(nivel-1) Metal y 75*1,5^(Nivel-1) Cristal
    Planta energ} Solar: 75*1,5^(nivel-1) Metal y 30*1,5^(Nivel-1) cristal
    Planta Fusion: 900*1,8^(nivel-1) Metal y 360*1,8^(Nivel-1) cristal y 180*1,8^(Nivel-1) Deuterio
    tecnolog} Gravit�: *3 por Nivel.

    Todas las dem� investigaciones y edificios *2^Nivel

  */
	$level = ($planet[$resource[$i]]) ? $planet[$resource[$i]] : $user[$resource[$i]];

	if(in_array($i,$reslist['build'])){		$cost_metal = 	floor($pricelist[$i]['metal'] * pow($pricelist[$i]['factor'],$level));
		$cost_crystal = floor($pricelist[$i]['crystal'] * pow($pricelist[$i]['factor'],$level));
		$time = ((($cost_crystal )+($cost_metal)) / $game_config['game_speed']) * (1 / ($planet[$resource['14']] + 1)) * pow(0.5,$planet[$resource['15']]);
		$time = floor($time * 60 * 60);
		return $time;
	}elseif(in_array($i,$reslist['tech'])){
		$cost_metal = 	floor($pricelist[$i]['metal'] * pow($pricelist[$i]['factor'],$level));
		$cost_crystal = floor($pricelist[$i]['crystal'] * pow($pricelist[$i]['factor'],$level));
		$msbnlvl = $user['intergalactic_tech'];
		if ($msbnlvl < "1"){
			$laborka = $planet[$resource['31']];
		}elseif ($msbnlvl >= "1"){
			$laborka = $planet['laboratory'];
			$sql = "SELECT laboratory
					FROM ugml_planets
					WHERE id_owner = '".WCF::getUser()->userID."'
						AND id != '".LWCore::getPlanet()->planetID."'
					ORDER BY laboratory DESC
					LIMIT ".WCF::getUser()->intergalactic_tech;
			$laboratories = WCF::getDB()->getResultList($sql);

			foreach($laboratories as $laboratory) {
				$laborka += $laboratory['laboratory'];
			}

			/*$msbnzapytanie = doquery("SELECT * FROM {{table}} WHERE id_owner='{$user[id]}'", "planets");
			$i = 0;
			$laborka_wyn = 0;
			while ($msbnrow = mysql_fetch_array($msbnzapytanie)){
				$laborka[$i] = $msbnrow['laboratory'];
				$tescik[$i] = $msbnrow['name'];
				$i++;
			}
			if ($msbnlvl >= "1"){
				$laborka_temp =0;
				for ($j = 1; $j<=$msbnlvl;$j++){
					asort($laborka);
					$laborka_temp = $laborka_temp + $laborka[$j-1];
				}
			}
			$laborka = $laborka_temp;*/
//		foreach ($laborka as $key => $val) {
//		    echo "; lab[" . $key . "] = " . $val . "<BR>";
//		    }
//		echo "$laborka_wyn";
//		$laborka = $laborka_temp + $laborka_temp1 + $laborka_temp2 + $laborka_temp3 + $laborka_temp4 + $laborka_temp5 + $laborka_temp6 + $laborka_temp7 + $laborka_temp8;
//		echo"$laborka";
		}
		$time = (($cost_metal + $cost_crystal) / $game_config['game_speed']) / ( ($laborka + 1 )*2);
		//metodo temporal para mostrar el formato tiempo...
		$time = floor($time*60*60);
		return $time;
		//return 30;
	}
	elseif(in_array($i,$reslist['defense'])||in_array($i,$reslist['fleet']))
	{//flota y defensa
		$time = (($pricelist[$i]['metal'] + $pricelist[$i]['crystal']) / $game_config['game_speed']) * (1 / ($planet[$resource['21']] + 1 )) * pow(1/2,$planet[$resource['15']]);
		//metodo temporal para mostrar el formato tiempo...
		$time = $time*60*60;
		return $time;
	}

}

function get_building_price($user,$planet,$i,$userfactor=true){
	global $pricelist,$resource;

	if($userfactor){$level = (isset($planet[$resource[$i]])) ? $planet[$resource[$i]] : $user[$resource[$i]];}
	//array
	$array = array('metal','crystal','deuterium');
	//loop
	foreach($array as $a){
		if($userfactor){
			$cost[$a] = floor($pricelist[$i][$a] * pow($pricelist[$i]['factor'],$level));
		}else{
			$cost[$a] = floor($pricelist[$i][$a]);
		}
	}

	return $cost;

}

//
//  Actualiza los datos de un planeta en cuanto a la plota.
//

function touchPlanet(&$planet){
	//global $resource, $phpEx, $ugamela_root_path, $pricelist, $lang;
}

function get_max_field(&$planet){
return $planet["field_max"]+($planet["terraformer"]*5);
}

function rev_time($seconds){
	$days=floor($seconds/86400);
	$hours=(floor(($seconds%86400)/3600));
	$minutes=floor(($seconds%3600)/60);
	$secs=$seconds%60;
	$month_len=array(0,31,28,31,30,31,30,31,31,30,31,30,31);
	$year=1970;
	$done=0;
	$month_id=1;
	while($days>$month_lenght)
	{
		$month_lenght=($month_id==2 ? ($year%4==0 ? 29 : $month_len[$month_id]):$month_len[$month_id]);
		$days-=$month_lenght;
		if ($month_id>12)
		{
			$month_id=1;
			$year++;
		}
		else
			$month_id++;
	}
	$days++;
	$days=($days<10 ? "0".$days : $days);
	$month=($month_id<10 ? "0".$month_id : $month_id);
	$hours=($hours<10 ? "0".$hours : $hours);
	$minutes=($minutes<10 ? "0".$minutes : $minutes);
	$secs=($secs<10 ? "0".$secs : $secs);
	$ret=($seconds>0 ? "$year-$month-$days<br>GMT $hours:$minutes:$secs" : "" );
	return $ret;
}
?>