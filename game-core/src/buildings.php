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
 //building.php
/***
Potrzebna zmienna - 2500 predkosc Ogame.pl
INSERT INTO `ugml_config` ( `config_name` , `config_value` )
VALUES (
'game_speed', '2500'
);

Kolejka Budowania
//ALTER TABLE `ugml_planets` ADD `b_building_queue` TEXT NULL ;
***/

define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);

if(!check_user()){ header("Location: login.php"); die();}
$speed = 1.0 ;
//
// Esta funcion permite cambiar el planeta actual.
//
include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);

$planetrow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_planet']}",'planets',true);
$galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet={$planetrow['id']}",'galaxy',true);
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
check_field_current($planetrow);

includeLang('tech');
includeLang('buildings');

$features = unserialize($user['diliziumFeatures']);

require_once(LW_DIR.'lib/data/ovent/BuildingOvent.class.php');

//Funciones
function echo_buildinglist(){
	/*
	  Se imprime una lista de naves y defensa en contruccion
	*/
	global $lang,$user,$planetrow,$pricelist;

	$parse = $lang;

	//Array del b_hangar_id
	$b_hangar_id = explode(';', substr($planetrow['b_hangar_id'], 0, -1));

	$a=$b=$c="";
	foreach($b_hangar_id as $n => $array){
		if($array!=''){
			$array = explode(',',$array);
			//calculamos el tiempo
			$time = get_building_time($user,$planetrow,$array[0]);
			$totaltime += $time * $array[1];
			$c .= "$time,";
			$b .= "'".html_entity_decode($lang['tech'][$array[0]])."',";
			$a .= "{$array[1]},";
		}
	}

	$parse['shipTypeArrayText'] = '';
	$i = 0;
	foreach($b_hangar_id as $array) {
		$array = explode(',',$array);

		$shipTypeID = $array[0];
		$count = $array[1];

		$time = get_building_time($user,$planetrow,$shipTypeID);
		$name = html_entity_decode($lang['tech'][$shipTypeID]);

		$parse['shipTypeArrayText'] .= 'shipTypeArray['.++$i.'] = new Object();';
		$parse['shipTypeArrayText'] .= 'shipTypeArray['.$i.']["count"] = '.$count.';';
		$parse['shipTypeArrayText'] .= 'shipTypeArray['.$i.']["time"] = '.round($time).';';
		$parse['shipTypeArrayText'] .= 'shipTypeArray['.$i.']["name"] = "'.$name.'";';
	}
	$parse['a'] = $a;
	$parse['b'] = $b;
	$parse['c'] = $c;
	$parse['b_hangar_id_plus'] = $planetrow['b_hangar'];

	$parse['pretty_time_b_hangar'] = pretty_time($totaltime-$planetrow['b_hangar']);// //$planetrow['last_update']

	$text .= parsetemplate(gettemplate('buildings_script'), $parse);

	return $text;
}
function echo_buildingqueue(){
	/*
	  Se imprime una lista de naves y defensa en contruccion
	*/
	global $lang,$user,$planetrow,$pricelist;

	//Array del b_hangar_id
	$b_building_id = explode(';',$planetrow['b_building_queue']);

	$a=$b=$c="";
	foreach($b_hangar_id as $n => $array){
		if($array!=''){
			$array = explode(',',$array);
			//calculamos el tiempo
			$time = get_building_time($user,$planetrow,$array[0]);
			$totaltime += $time * $array[1];
			$c .= "$time,";
			$b .= "'".htmlspecialchars_decode($lang['tech'][$array[0]])."',";
			$a .= "{$array[1]},";
		}
	}

	$parse = $lang;
	$parse['a'] = $a;
	$parse['b'] = $b;
	$parse['c'] = $c;
	$parse['b_hangar_id_plus'] = $planetrow['b_hangar'];

	$parse['pretty_time_b_hangar'] = pretty_time($totaltime-$planetrow['b_hangar']);// //$planetrow['last_update']

	$text .= parsetemplate(gettemplate('buildings_script'), $parse);

	return $text;
}


if($user["b_tech_planet"] != 0){//technology...
	/*
	  Hacemos el query para mantenerlo porque se va a utilizar mas adelante para dar la referencia
	  pero en vano es el query, si el planeta es el mismo que el actual :P
	*/
	if($user["b_tech_planet"] != $planetrow["id"]){
		$tech_planetrow = doquery("SELECT * FROM {{table}} WHERE id = '{$user['b_tech_planet']}'","planets",true);
	}
	if($tech_planetrow){$planet = $tech_planetrow;}else{$planet = $planetrow;}

	if($planet["b_tech"] <= time() && $planet["b_tech_id"] != 0){

		$user[$resource[$planet["b_tech_id"]]]++;
		doquery("UPDATE {{table}} SET
			b_tech_id=0
			WHERE id='{$planet["id"]}'","planets");
		doquery("UPDATE {{table}} SET
			`{$resource[$planet["b_tech_id"]]}`='{$user[$resource[$planet["b_tech_id"]]]}',
			points_tech2=points_tech2+1,
			b_tech_planet=0
			WHERE `id`=".$user["id"].";","users");
		$planet["b_tech_id"] = 0;

		if(isset($tech_planetrow)){$tech_planetrow = $planet;}else{$planetrow = $planet;}

	}elseif($planet["b_tech_id"] == 0){
		/*
		  Esto es para corregir algunos fallos o un posible problema al cancelar una investigacion
		*/
		doquery("UPDATE {{table}} SET b_tech_planet=0  WHERE id='{$user['id']}'","users");
	}else{ $teching = true;}

}
$_GET['bau'] = intval($_GET['bau']);
switch ($_GET["mode"]){

case 'fleet':{//------------------------------------------------------------
	//modo POST
	if(isset($_POST['fmenge']) && $user['urlaubs_modus'] == 0){
		$totalMetal = $totalCrystal = $totalDeuterium = 0;
		
		$ally_points=0;
		foreach($_POST['fmenge'] as $a => $b){
			$b = intval($b);
			/*
			Lo que se hara a continuacion es totalmente insano y muy loco...

			Bueno, se procede a crear una array con la produccion de los
			elementos elegidos, y se comprobara si se tiene el suficiente
			recurso para poder comprarlo
			*/
			if($b != 0){
				//se comprueba que este disponible, para evitar hacks
				if(is_tech_available($user,$planetrow,$a)){
					//Se procede a comprobar cuantos recursos requiere esa cantidad de
					//elementos
					//version 1.0
					unset($builds);
					while($b != 0){

						$is_buyeable = true;
						$costMetal=0;
						$costCrystal=0;
						$costDeuterium=0;
						$costEnergy=0;

						if($pricelist[$a]['metal'] != 0){
							$costMetal = $pricelist[$a]['metal'];
							if($costMetal > $planetrow["metal"]){ $is_buyeable = false;}
						}
						if($pricelist[$a]['crystal'] != 0){
							$costCrystal = $pricelist[$a]['crystal'];
							if($costCrystal > $planetrow["crystal"] && $is_buyeable){ $is_buyeable = false;}
						}
						if($pricelist[$a]['deuterium'] != 0){
							$costDeuterium = $pricelist[$a]['deuterium'];
							if($costDeuterium > $planetrow["deuterium"] && $is_buyeable){$is_buyeable = false;}
						}
						if($pricelist[$a]['energy'] != 0){
							$costEnergy = $pricelist[$a]['energy'];
							if($costEnergy > $planetrow["energy_max"] && $is_buyeable){$is_buyeable = false;}
						}
						if($is_buyeable){
							//Se agrega a una array donde se contiene todo lo que se pudo
							//comprar
							$builds[$a]++;
							$user['points_fleet2']++;
							$ally['ally_points_fleet2']++;
							$planetrow["metal"] -= $costMetal;
							$planetrow["crystal"] -= $costCrystal;
							$planetrow["deuterium"] -= $costDeuterium;
													           
				            $totalMetal += $costMetal;
				            $totalCrystal += $costCrystal;
				            $totalDeuterium += $costDeuterium;

							LWCore::getPlanet()->metal = $planetrow['metal'];
							LWCore::getPlanet()->crystal = $planetrow['crystal'];
							LWCore::getPlanet()->deuterium = $planetrow['deuterium'];

							$points_points = $costMetal+$costCrystal+$costDeuterium;
							$user["points_fleet_old"] += $points_points;
							$ally_fleet_old += $points_points;
							$b--;//un contador menos...
						}else{
							$b=0;//para romper el loop
						}
					}
					//ahora que ya quitamos los recursos, que se actualizan solos ademas!
					//se procede a crear la array de produccion
					if (isset($builds)){
						foreach($builds as $a => $b){
							$planetrow['b_hangar_id'] .= "$a,$b;";
						}
					}
				}
			}

		}
		//agregamos los puntos
		doquery("UPDATE {{table}} SET
		points_fleet_old='{$user['points_fleet_old']}',
		points_fleet2={$user['points_fleet2']}
		WHERE id={$user['id']}","users");
		//para alianza
		if($user['ally_id']!=0){
			//agregamos los puntos
			doquery("UPDATE {{table}} SET
			ally_points_fleet_old=ally_points_fleet_old+'{$ally_fleet_old}',
			ally_points_fleet2=ally_points_fleet2+{$user['points_fleet2']}
			WHERE id={$user['ally_id']}","alliance");
		}
		
		
		$sql = "UPDATE ugml_planets
	    		SET metal = metal - ".$totalMetal.",
	   				crystal = crystal - ".$totalCrystal.",
	   				deuterium = deuterium - ".$totalDeuterium."
	   			WHERE id = ".LWCore::getPlanet()->planetID;
	    WCF::getDB()->sendQuery($sql);
	}
	

	if($planetrow[$resource[21]] == 0){
		message($lang['need_hangar'],$lang["Hangar"]);
	}
	
	LWCore::getPlanet()->b_hangar = $planetrow['b_hangar'];
    LWCore::getPlanet()->b_hangar_id = $planetrow['b_hangar_id'];
    //echo 'Setting!';

	//luego del post
	//
	//--[Comienza normalmente]----------------------------------------------
	//
	$tabindex = 0;

	foreach($lang['tech'] as $i => $n){//investigacion

		if($i > 201&&$i <= 399){

			if(!is_tech_available($user,$planetrow,$i)){
				$buildlist .= "</tr>\n";
			}else{
				//Funciona ^_^
				$buildlist .= "<tr><td class=l><a href=infos.{$phpEx}?gid=$i><img border=0 src=\"{$dpath}gebaeude/$i.gif\" align=top width=120 height=120></a></td>";
				//obtenemos el nivel del edificio
				$building_level = $planetrow[$resource[$i]];
				//Muestra el nivel actual de la mina
				//die($user[$resource[$i]]);
				$nivel = ($building_level == 0) ? "" : " ($building_level vorhanden)";
				//Descripcion
				$buildlist .= "<td class=l><a href=infos.{$phpEx}?gid=$i>$n</a>$nivel<br>{$lang['res']['descriptions'][$i]}<br>\n";

				$is_buyeable = is_buyeable($user,$planetrow,$i,false);
				$buildlist .= price($user,$planetrow,$i,false);

				/*
				Calculo del tiempo de produccion
				[(Cris+Met)/2500]*[1/(Nivel f.robots+1)]* 0,5^NivelFabrica Nanos.
				*/
				$time = get_building_time($user,$planetrow,$i);
				//metodo temporal para mostrar el formato tiempo...
				$buildlist .= building_time($time);

				$buildlist .= "<td class=k>";

				//Muestra la opcion a elegir para construir o ampliar un edificio
				if($is_buyeable){
					$tabindex++;
					$buildlist .= "<input type=text name=fmenge[$i] alt='{$lang['tech'][$i]}' size=\"6\" maxlength=6 value=0 tabindex=$tabindex>";
				}
			}
		}
	}

	if($planetrow['b_hangar_id']!='') $buildinglist .= echo_buildinglist();

	$parse = $lang;
	$parse['buildlist'] = $buildlist;
	$parse['buildinglist'] = $buildinglist;
	//fragmento de template
	$page .= parsetemplate(gettemplate('buildings_fleet'), $parse);

	display($page,$lang['Research']);

}

case 'research':{//---------------------------------------------------------
	/*
	  Investigacion
	  Este codigo es similar en todo este php
	*/
	if(isset($_GET["bau"]) && in_array($_GET["bau"],$reslist['tech']) && $user['urlaubs_modus'] == 0){
		//if(is_buyable($user,$planetrow,$bau)) error("No se puede investigar esa tecnologia.","Investigar");
		//nueva configuracion :D
		if($planetrow["b_building_id"] == 31 &&
			$game_config['allow_invetigate_while_lab_is_update']!=1)
		{
			message($lang['cant_invetigate_while_lab_is_update'],"Urlaubsmodus");
		}



		if(is_tech_available($user,$planetrow,$_GET["bau"]) &&
			$user["b_tech_planet"]==0 &&
			is_buyable($user,$planetrow,$_GET["bau"]))
		{
		//establecemos que se investiga.
		$planetrow["b_tech_id"] = $_GET["bau"];
		//indicamos el tiempo de investigacion.y establecemos el tiempo de
		//especulacion de cuando termine la investigacion.
		$planetrow["b_tech"] = time()+get_building_time($user,$planetrow,$_GET["bau"]);
		//actualizamos e indicamos donde se esta haciendo la investigacion.
		$user["b_tech_planet"] = $planetrow["id"];
		//ahora se restan los recursos
		$costs = get_building_price($user,$planetrow,$_GET["bau"]);
		//descontamos, solo en vista
		$planetrow['metal']-=$costs['metal'];
		$planetrow['crystal']-=$costs['crystal'];
		$planetrow['deuterium']-=$costs['deuterium'];
		LWCore::getPlanet()->metal = $planetrow['metal'];
		LWCore::getPlanet()->crystal = $planetrow['crystal'];
		LWCore::getPlanet()->deuterium = $planetrow['deuterium'];
		$points_points=$costs['metal']+$costs['crystal']+$costs['deuterium'];
		$planetrow['points']+=$points_points;
		//queries
		doquery("UPDATE {{table}} SET b_tech_id={$planetrow['b_tech_id']},
			b_tech={$planetrow['b_tech']},
			metal={$planetrow['metal']},
			crystal={$planetrow['crystal']},
			deuterium={$planetrow['deuterium']},
			points={$planetrow['points']}
			WHERE id={$planetrow['id']}","planets");
		doquery("UPDATE {{table}} SET
			b_tech_planet={$user['b_tech_planet']},
			points_tech_old=points_tech_old+{$points_points}
			WHERE id={$user['id']}","users");
		if($user['ally_id']!=0){
			//agregamos los puntos
			doquery("UPDATE {{table}} SET
			ally_points_tech_old=ally_points+{$points_points}
			WHERE id={$user['ally_id']}","alliance");
		}
		//listo
		$planet = $planetrow;
		$teching = true;
		}
	}
	elseif(isset($_GET["unbau"]) && in_array($_GET["unbau"], $reslist['tech'])){

	//checheamos la tecnologia...
    if($user["b_tech_planet"] != 0){// && $planetrow["b_tech_id"] == $unbau

      if($user["b_tech_planet"] != $planetrow["id"]){
        $tech_planetrow = doquery("SELECT * FROM {{table}} WHERE id = '".$user["b_tech_planet"]."'","planets",true);
      }

      if(isset($tech_planetrow)){$planet = $tech_planetrow;}else{$planet = $planetrow;}
      //if($planet["b_tech"] <= time()){

      if($planet["b_tech_id"] == $_GET["unbau"]){

        $planet["b_tech_id"] = 0;
        $user["b_tech_planet"] = 0;

		$costs = get_building_price($user,$planetrow,$_GET["unbau"]);
		//descontamos, solo en vista
		$planet['metal']+=$costs['metal'];
		$planet['crystal']+=$costs['crystal'];
		$planet['deuterium']+=$costs['deuterium'];

		LWCore::getPlanet()->metal = $planet['metal'];
		LWCore::getPlanet()->crystal = $planet['crystal'];
		LWCore::getPlanet()->deuterium = $planet['deuterium'];

		$points_points = $costs['metal']+$costs['crystal']+$costs['deuterium'];
		$planetrow['points']-=$points_points;

        //doquery("UPDATE {{table}} SET b_tech_id=0  WHERE `id`=".$planet["id"].";","planets");
        doquery("UPDATE {{table}} SET
			b_tech_id=0,
			metal=".$planet["metal"].",
			crystal=".$planet["crystal"].",
			deuterium=".$planet["deuterium"].",
			points={$planetrow['points']}
			WHERE `id`=".$planet["id"].";","planets");
        doquery("UPDATE {{table}} SET
			b_tech_planet=0,
			points_tech_old=points_tech_old-{$points_points}
			WHERE `id`=".$user["id"].";","users");
		if($user['ally_id']!=0){
			//agregamos los puntos
			doquery("UPDATE {{table}} SET
			ally_points_tech_old=ally_points-{$points_points}
			WHERE id={$user['ally_id']}","alliance");
		}

        if(isset($tech_planetrow)){$tech_planetrow = $planet;}else{$planetrow = $planet;}
		//listo :)
		$teching = false;



      }
      //error("$cost_metal/".$planetrow["metal"]." - $cost_crystal/".$planetrow["crystal"]." - $cost_deuterium/".$planetrow["deuterium"]."/","");
      //$time = ((($cost_crystal )+($cost_metal)) / 2500) * (1 / ($planetrow[$resource['14']] + 1)) * pow(0.5,$planetrow[$resource['15']]);
      //metodo temporal para mostrar el formato tiempo...
      //$time = ($time *60*60);

    }

  }

	//
	//--[Comienza normalmente]----------------------------------------------
	//
	if($planetrow[$resource[31]] == 0){
		message($lang['need_investigationlab'],"Forschung");
	}

	if($planetrow["b_building_id"] == 31 && $game_config['allow_invetigate_while_lab_is_update']!=1)
	{
		$buildinglist = '<br><br><font color="#ff0000">'.$lang['cant_invetigate_while_lab_is_update'].'</font><br><br>';
	}

	//cargamos la template...
	$template = gettemplate('buildings_research_row');

	foreach($lang['tech'] as $i => $n){//investigacion

		if($i > 105&&$i <= 199){

			if(!is_tech_available($user,$planetrow,$i)){//:)
				$buildinglist .= "</tr>";
			}else{
				//Funciona ^_^
				$parse = $lang;
				$parse['dpath'] = $dpath;
				$parse['i'] = $i;
				//obtenemos el nivel del edificio
				$building_level = $user[$resource[$i]];
				//Muestra el nivel actual de la mina
				$parse['nivel'] = ($building_level == 0) ? "" : "({$lang['level']} {$building_level})";
				//Descripcion
				$parse['n'] = $n;
				$parse['description'] = $lang['res']['descriptions'][$i];
				//$is_buyeable = is_buyable($user,$planetrow,$i,true);
				$is_buyeable = is_buyeable($user,$planetrow,$i);
				$parse['price'] = price($user,$planetrow,$i);
				$parse['rest_price'] = rest_price($user,$planetrow,$i);
				/*
				Calculo del tiempo de produccion
				[(Cris+Met)/2500]*[1/(Nivel f.robots+1)]* 0,5^NivelFabrica Nanos.
				*/
				//$time = (($pricelist[$i]['metal'] + $pricelist[$i]['crystal']) / 1000) * (($planetrow[$resource['31']] + 1 ));
				//metodo temporal para mostrar el formato tiempo...
				$time = get_building_time($user,$planetrow,$i);//$time*60*60);
				$parse['time'] = building_time($time);


				//agregamos el row a la buildinglist
				$buildinglist .= parsetemplate($template, $parse);

				/*if($game_config['allow_invetigate_while_lab_is_update']==1){

					$lang['cant_invetigate_while_lab_is_update'];




				}else*/
				if(!$teching){
					//Muestra la opcion a elegir para construir o ampliar un edificio
					if($user[$resource[$i]] == 0 && $is_buyeable){

						if($planetrow["b_building_id"] == 31 &&
							$game_config['allow_invetigate_while_lab_is_update']!=1)
						{
							$buildinglist .= "<font color=#FF0000>Forschen</font>";
						}else{
							$buildinglist .= "<a href=\"?mode=research&bau=$i\"><font color=#00FF00>Forschen</font></a>";
						}

					}elseif($is_buyeable){

						if($planetrow["b_building_id"] == 31 &&
							$game_config['allow_invetigate_while_lab_is_update']!=1)
						{
							$nplus = $user[$resource[$i]] + 1;
							$buildinglist .= "<font color=#FF0000>Forschen<br> auf Stufe $nplus</font>";
						}else{
							$nplus = $user[$resource[$i]] + 1;
							$buildinglist .= "<a href=\"?mode=research&bau=$i\"><font color=#00FF00>Forschen<br> auf Stufe $nplus</font></a>";
						}

					}elseif($user[$resource[$i]] == 0){
						$buildinglist .= "<font color=#FF0000>Forschen</font>";
					}else{
						$nplus = $user[$resource[$i]] + 1;
						$buildinglist .= "<font color=#FF0000>Forschen<br> auf Stufe $nplus nicht m�glich </font>";
					}
				}
				else{

					if($planet["b_tech_id"] == $i){
						$parse = $lang;
						if(isset($tech_planetrow)){
							$planet = $tech_planetrow;
							$parse['time'] = $tech_planetrow["b_tech"] - time();
							$parse['name'] = $tech_planetrow["name"];
							$parse['idcp'] = $tech_planetrow["id"];
							$parse['unbau'] = $tech_planetrow["b_tech_id"];
						}else{
							$planet = $planetrow;
							$parse['time'] = $planetrow["b_tech"] - time();
							$parse['name'] = "";
							$parse['idcp'] = $planetrow["id"];
							$parse['unbau'] = $planetrow["b_tech_id"];
						}
						// Todo loco, este script permite mostrar el tiempo de investigacion seguido
						$buildinglist .= parsetemplate(gettemplate('buildings_research_script'), $parse);

					}else{$buildinglist .= "<center>-</center>";}

				}

			}

		}

	}

	$parse = $lang;
	$parse['buildinglist'] = $buildinglist;
	//fragmento de template
	$page .= parsetemplate(gettemplate('buildings_research'), $parse);

	display($page,$lang['Research']);
	die();

}

case 'defense':{//----------------------------------------------------------
	//Defensa
	//
	//--[modo POST]-------------------------------------------------------
	//
	
	$totalMetal = $totalCrystal = $totalDeuterium = 0;
	if(isset($_POST['fmenge']) && $user['urlaubs_modus'] == 0){
    $points_points_plus = $updateSiloSlots = 0;
    foreach($_POST['fmenge'] as $a => $b){
    	
			$b = intval($b);
		/*
		  Lo que se hara a continuacion es totalmente insano y muy loco...

		  Bueno, se procede a crear una array con la produccion de los
		  elementos elegidos, y se comprobara si se tiene el suficiente
		  recurso para poder comprarlo
		*/
      if($b != 0){
        //se comprueba que este disponible, para evitar hacks
        if(is_tech_available($user,$planetrow,$a)){
          //Se procede a comprobar cuantos recursos requiere esa cantidad de
          //elementos
          //version 1.0
          unset($builds);
          while($b != 0){


          	// check
          	switch($a) {
          		// small protection shield
          		case 407:
          			if(LWCore::getPlanet()->small_protection_shield > 0) {
          				$b = 0;
          				break 2;
          			}
          			break;
          		// big protection shield
          		case 408:
          			if(LWCore::getPlanet()->big_protection_shield > 0) {
          				$b = 0;
          				break 2;
          			}
          			break;
          		// interceptor missile
          		case 502:
          			if(LWCore::getPlanet()->interceptor_missiles + LWCore::getPlanet()->interplanetary_missiles * 2 + 1 > LWCore::getPlanet()->silo * 10) {
          				$b = 0;
          				break 2;
          			}
          			++LWCore::getPlanet()->siloSlots;
          			++$updateSiloSlots;
          			break;
          		// interplanetary missile
          		case 503:
          			if(LWCore::getPlanet()->interceptor_missiles + LWCore::getPlanet()->interplanetary_missiles * 2 + 2 > LWCore::getPlanet()->silo * 10) {
          				$b = 0;
          				break 2;
          			}
          			LWCore::getPlanet()->siloSlots += 2;
          			$updateSiloSlots += 2;
          			break;
          	}

	        $is_buyeable = true;
            $costMetal=0;
            $costCrystal=0;
            $costDeuterium=0;
            $costEnergy=0;

            if($pricelist[$a]['metal'] != 0){
              $costMetal = $pricelist[$a]['metal'];
              if($costMetal > $planetrow["metal"]){ $is_buyeable = false;}
            }
            if($pricelist[$a]['crystal'] != 0){
              $costCrystal = $pricelist[$a]['crystal'];
              if($costCrystal > $planetrow["crystal"] && $is_buyeable){ $is_buyeable = false;}
            }
            if($pricelist[$a]['deuterium'] != 0){
              $costDeuterium = $pricelist[$a]['deuterium'];
              if($costDeuterium > $planetrow["deuterium"] && $is_buyeable){$is_buyeable = false;}
            }
            if($pricelist[$a]['energy'] != 0){
              $costEnergy = $pricelist[$a]['energy'];
              if($costEnergy > $planetrow["energy_max"] && $is_buyeable){$is_buyeable = false;}
            }
            
            if($is_buyeable){
              //Se agrega a una array donde se contiene todo lo que se pudo
              //comprar
              $builds[$a]++;
              $planetrow["metal"] -= $costMetal;
              $planetrow["crystal"] -= $costCrystal;
              $planetrow["deuterium"] -= $costDeuterium;

              LWCore::getPlanet()->metal = $planetrow['metal'];
			  LWCore::getPlanet()->crystal = $planetrow['crystal'];
			  LWCore::getPlanet()->deuterium = $planetrow['deuterium'];
			  
			  $totalMetal += $costMetal;
			  $totalCrystal += $costCrystal;
			  $totalDeuterium += $costDeuterium;

			  $points_points = $costMetal+$costCrystal+$costDeuterium;
			  $points_points_plus .= $points_points;
              $user['points_builds2']+=$points_points;
              $planetrow['points']+=$points_points;
              $b--;//un contador menos...

              if (($a == 407) || ($a == 408)) {
              	$b=0;
              	break;
              }

            }else{
              $b=0;//para romper el loop
            }

          }
          //ahora que ya quitamos los recursos, que se actualizan solos ademas!
          //se procede a crear la array de produccion
          if (isset($builds)){
	          foreach($builds as $a => $b){
	            $planetrow['b_hangar_id'] .= "$a,$b;";
	          }
          }
        }
      }
    }
    LWCore::getPlanet()->b_hangar = $planetrow['b_hangar'];
    LWCore::getPlanet()->b_hangar_id = $planetrow['b_hangar_id'];
    
    $sql = "UPDATE ugml_planets
    		SET metal = metal - ".$totalMetal.",
   				crystal = crystal - ".$totalCrystal.",
   				deuterium = deuterium - ".$totalDeuterium."
   			WHERE id = ".LWCore::getPlanet()->planetID;
    WCF::getDB()->sendQuery($sql);

		//agregamos los puntos
		doquery("UPDATE {{table}} SET
		points_builds2='{$user['points_builds2']}'
		WHERE id={$user['id']}","users");
		//agregamos los puntos
		doquery("UPDATE {{table}} SET
		points='{$planetrow['points']}'
		WHERE id={$planetrow['id']}","planets");
		if($user['ally_id']!=0){
			//agregamos los puntos
			doquery("UPDATE {{table}} SET
			ally_points_builds=ally_points+{$points_points_plus}
			WHERE id={$user['ally_id']}","alliance");
		}
	}

	//
	//--[Modo normal]-------------------------------------------------------
	//

	if($planetrow[$resource[21]] == 0){
		message($lang['need_hangar']);
	}else{

		$tabindex = 0;

		foreach($lang['tech'] as $i => $n){ //Defensa
			if($i > 400&&$i <= 599){

				if(!is_tech_available($user,$planetrow,$i)){

					$buildinglist .= "</tr>\n";

				}

				else{
					//Funciona ^_^
					$buildinglist .= "<tr><td class=l><a href=infos.php?gid=$i><img border='0' src=\"".$dpath."gebaeude/$i.gif\" align='top' width='120' height='120'></a></td>";

					//obtenemos la cantidad de unidades que hay en el planeta
					$building_level = $planetrow[$resource[$i]];

					//Muestra la cantidad de unidades que se encuentran en el planeta
					//die($planetrow[$resource[$i]]);
					$nivel = ($building_level == 0) ? "" : "(Vorhanden: $building_level)";
					//Descripcion
					$buildinglist .= "<td class=l><a href=infos.php?gid=$i>$n</a> $nivel<br>{$lang['res']['descriptions'][$i]}<br>";

					$is_buyeable = is_buyeable($user,$planetrow,$i,false);
					$buildinglist .= price($user,$planetrow,$i,false);

					/*
					  Calculo del tiempo de produccion
					  [(Cris+Met)/2500]*[1/(Nivel f.robots+1)]* 0,5^NivelFabrica Nanos.
					*/
					$time = get_building_time($user,$planetrow,$i);
					//metodo temporal para mostrar el formato tiempo...
					$buildinglist .= building_time($time);

					$buildinglist .= "<td class=k>";

					//Muestra la opcion a elegir para construir o ampliar un edificio
					if($is_buyeable){
						if (($i==407 && $planetrow['small_protection_shield'] == "1") ||($i==407 && $planetrow['b_hangar_id'] == "407,1;")||(($i==408 && $planetrow['big_protection_shield'] == "1") ||($i==408 && $planetrow['b_hangar_id'] == "408,1;")))
					    	{
						 $tabindex++;
					    	 $is_buyeable = false;
						 $buildinglist .= "Nur eine Schildkuppel m�glich";
					    	}
						else{
						$tabindex++;
						$buildinglist .= "<input type=text name=fmenge[$i] alt='{$tech[$i]}' size=6 maxlength=6 value=0 tabindex=$tabindex>";
					 }
					}
				}

			}

		}

		$buildinglist .= '</td></tr>
		<td class=c colspan=2 align=center><input type=submit value="Auftrag erstellen">
		</td></tr></table></form></td><td valign="top"></td></tr></table>';

		if ($planetrow['b_hangar_id']!='') $buildinglist .= echo_buildinglist();
	}


	$parse = $lang;
	$parse['buildinglist'] = $buildinglist;
	//fragmento de template
	$page .= parsetemplate(gettemplate('buildings_defense'), $parse);

	display($page,$lang['Defense']);
	die();
}

default:{//-----------------------------------------------------------------

/*
  La construccion se controla aqui. Se decide construir, o calcelar la construccion
  tambien se toma y quita los recursos.
*/
$_GET["bau"] = intval($_GET["bau"]);
$_GET["unbau"] = intval($_GET["unbau"]);
if(in_array($_GET["bau"],$reslist['build']) && $user['urlaubs_modus'] == 0){

	check_field_current($planetrow);
	//hay que arreglar este mensaje de advertencia...
	if($user["b_tech_planet"] != 0 && $_GET["bau"] == 31 && $game_config['allow_invetigate_while_lab_is_update'] != 1){
		message($lang['Cant_build_lab_while_invetigate'],$lang['Build_lab']);
	}
	//comprobamos si hay espacio para construir
	if($planetrow["field_current"] < get_max_field($planetrow) && $planetrow["b_building_id"] == 0 && is_tech_available($user,$planetrow,$_GET["bau"]) && is_buyable($user,$planetrow,@$_GET["bau"])){
		/*
		  Especular el tiempo de construccion, se puede establecer una funcion aparte, pero
		  todavia tengo el problema para averiguar el tiempo de construcciones...
		*/
		$planetrow["b_building_id"] = $_GET["bau"];
		//ahora se restan los recursos


		$costs = get_building_price($user,$planetrow,$_GET["bau"]);
		//descontamos, solo en vista
		$planetrow['metal']-=$costs['metal'];
		$planetrow['crystal']-=$costs['crystal'];
		$planetrow['deuterium']-=$costs['deuterium'];

		LWCore::getPlanet()->metal = $planetrow['metal'];
		LWCore::getPlanet()->crystal = $planetrow['crystal'];
		LWCore::getPlanet()->deuterium = $planetrow['deuterium'];

		//error("$cost_metal/".$planetrow["metal"]." - $cost_crystal/".$planetrow["crystal"]." - $cost_deuterium/".$planetrow["deuterium"]."/","");
		$time = ((($costs['crystal']+$costs['metal'])) / $game_config['game_speed']) * (1 / ($planetrow[$resource[14]] + 1)) * pow(0.5,$planetrow[$resource[15]]);
		//Agregamos los puntos.
		$ally_points = $costs['metal']+$costs['crystal']+$costs['deuterium'];
		$points_points = $costs['metal']+$costs['crystal']+$costs['deuterium'];
		$planetrow['points']-=$points_points;
		$planetrow['points'] += $ally_points;
		//$points_points = $costMetal+$costCrystal+$costDeuterium;
		$user["points_builds"] += $points_points;
//		$user["points_points"] += $points_points;
		$ally_points += $points_points;
		//metodo para obtener el formato tiempo...
		$time = ($time *60*60);
//		$builds =

//		foreach($builds as $a => $b){
//			$planetrow['b_building_queue'] .= "$a,$b;";
//			}

		$planetrow["b_building"] = time() + floor($time);
		doquery("UPDATE {{table}} SET
			b_building_id='{$planetrow['b_building_id']}',
			b_building='{$planetrow['b_building']}',
			metal = metal - ".$costs['metal'].",
			crystal = crystal - ".$costs['crystal'].",
			deuterium = deuterium - ".$costs['deuterium'].",
			points='{$planetrow['points']}'
			WHERE id='{$planetrow['id']}'","planets");
		//para alianza
		if($user['ally_id']!=0){
			//agregamos los puntos
		doquery("UPDATE {{table}} SET
			ally_points_builds=ally_points+{$ally_points}
			WHERE id={$user['ally_id']}","alliance");
			}
		doquery("UPDATE {{table}} SET
			points_builds='{$user['points_builds']}'
			WHERE `id`=".$user["id"].";","users");
			
		LWCore::getPlanet()->b_building = $planetrow["b_building"];
		LWCore::getPlanet()->b_building_id = $planetrow["b_building_id"];
		
		BuildingOvent::check(LWCore::getPlanet()->planetID); 

	} else if($planetrow["b_building_id"] && $features['buildList'] > time() && is_tech_available($user,$planetrow,$_GET["bau"])/* && is_buyable($user,$planetrow,@$_GET["bau"])*/) {
		$moreBuildings = unserialize($planetrow['moreBuildings']);

		$costs = get_building_price($user,$planetrow,$_GET["bau"]);
		
		/*$planetrow['metal']-=$costs['metal'];
		$planetrow['crystal']-=$costs['crystal'];
		$planetrow['deuterium']-=$costs['deuterium'];

		LWCore::getPlanet()->metal = $planetrow['metal'];
		LWCore::getPlanet()->crystal = $planetrow['crystal'];
		LWCore::getPlanet()->deuterium = $planetrow['deuterium'];*/
		
		if(count($moreBuildings) < 4) {

			$moreBuildings[] = intval($_GET['bau']);
			$planetrow['moreBuildings'] = serialize($moreBuildings);
			$sql = "UPDATE ugml".LW_N."_planets
					SET moreBuildings = '".$planetrow['moreBuildings']."'
					WHERE id = ".$planetrow['id'];
			WCF::getDB()->sendQuery($sql);
		}
	}

}
elseif(in_array($_GET["unbau"],$reslist['build'])&&$planetrow['b_building_id'] == $_GET["unbau"]){

	// count buildings
	/*
	$moreBuildings = unserialize($planetrow['moreBuildings']);
	if(is_array($moreBuildings)) {
		$buildingsTMP = $planetrow;
		foreach($moreBuildings as $buildingID) $buildingsTMP[$resource[$buildingID]]++;
	}*/
	$costs = get_building_price($user,$planetrow,$_GET["unbau"]);
	/*print_r($buildingsTMP);
	echo '<br />';*/
	//print_r($costs);

	//ahora se restan los recursos
	//$costs = get_building_price($user,$planetrow,$_GET["unbau"]);
	//descontamos, solo en vista
	$planetrow['metal']+=$costs['metal'];
	$planetrow['crystal']+=$costs['crystal'];
	$planetrow['deuterium']+=$costs['deuterium'];

	LWCore::getPlanet()->metal = $planetrow['metal'];
	LWCore::getPlanet()->crystal = $planetrow['crystal'];
	LWCore::getPlanet()->deuterium = $planetrow['deuterium'];


	$planetrow['b_building_id'] = 0;
	$planetrow['b_building'] = time();
	doquery("UPDATE {{table}} SET
		b_building_id='{$planetrow['b_building_id']}',
		metal = metal + '".$costs['metal']."',
		crystal = crystal + '".$costs['crystal']."',
		deuterium = deuterium + '".$costs['deuterium']."',
		points='{$planetrow['points']}'
		WHERE id='{$planetrow['id']}'",'planets');
        
		doquery("UPDATE {{table}} SET
		points_builds='{$user['points_builds']}'
		WHERE `id`=".$user["id"].";","users");
	
	LWCore::getPlanet()->b_building = $planetrow["b_building"];
	LWCore::getPlanet()->b_building_id = $planetrow["b_building_id"];
	
	BuildingOvent::check(LWCore::getPlanet()->planetID); 

	$moreBuildings = unserialize($planetrow['moreBuildings']);
	if(@isset($moreBuildings[0])) {
		if(is_tech_available($user,$planetrow, $moreBuildings[0]) && is_buyable($user,$planetrow,$moreBuildings[0])) {

			$nextBuildingID = $moreBuildings[0];

			$costs = get_building_price($user,$planetrow,$nextBuildingID);

			$planetrow['metal'] -= $costs['metal'];
			$planetrow['crystal'] -= $costs['crystal'];
			$planetrow['deuterium'] -= $costs['deuterium'];

			LWCore::getPlanet()->metal = $planetrow['metal'];
			LWCore::getPlanet()->crystal = $planetrow['crystal'];
			LWCore::getPlanet()->deuterium = $planetrow['deuterium'];


			unset($moreBuildings[0]);

			if($_GET['unbauBL'] == 0) {
				if(isset($moreBuildings[1])) {
					$moreBuildings[0] = $moreBuildings[1];
					unset($moreBuildings[1]);
					if(isset($moreBuildings[2])) {
						$moreBuildings[1] = $moreBuildings[2];
						unset($moreBuildings[2]);
						if(isset($moreBuildings[3])) $moreBuildings[2] = $moreBuildings[3];
					}
				}
			} else if($_GET['unbauBL'] == 1) {
				if(isset($moreBuildings[2])) {
					$moreBuildings[1] = $moreBuildings[2];
					unset($moreBuildings[2]);
					if(isset($moreBuildings[3])) $moreBuildings[2] = $moreBuildings[3];
				}
			} else if($_GET['unbauBL'] == 2 && isset($moreBuildings[3])) $moreBuildings[2] = $moreBuildings[3];

			unset($moreBuildings[3]);
			$costs = get_building_price($user,$planetrow,$nextBuildingID);
			$time = ((($costs['crystal']+$costs['metal'])) / $game_config['game_speed']) * (1 / ($planetrow[$resource[14]] + 1)) * pow(0.5,$planetrow[$resource[15]]);
			$time = ($time * 60 * 60);

			ksort($moreBuildings);

			$planetrow['moreBuildings'] = serialize($moreBuildings);
			//echo '>>'.$planetrow['moreBuildings'].'<<>>'.$time.'<<<br />';
			$planetrow["b_building"] = time() + floor($time);
			$planetrow['b_building_id'] = $nextBuildingID;
			$sql = "UPDATE ugml".LW_N."_planets SET
					metal = metal - ".$costs['metal'].",
					crystal =  crystal - ".$costs['crystal'].",
					deuterium = deuterium - ".$costs['deuterium'].",
					b_building_id = '".$nextBuildingID."',
					b_building = '".$planetrow['b_building']."',
					moreBuildings = '".$planetrow['moreBuildings']."'
					WHERE id = '".$planetrow['id']."'";
			WCF::getDB()->sendQuery($sql);
			$building = true;
			
			LWCore::getPlanet()->b_building = $planetrow["b_building"];
			LWCore::getPlanet()->b_building_id = $planetrow["b_building_id"];
			
			BuildingOvent::check(LWCore::getPlanet()->planetID); 
		} else {
			$planetrow['moreBuildings'] = serialize(array());
			$sql = "UPDATE ugml".LW_N."_planets SET
					moreBuildings = '".$planetrow['moreBuildings']."'
					WHERE id = '".$planetrow['id']."'";
			WCF::getDB()->sendQuery($sql);

			$planetrow["b_building"] = 0;
		}
	}

} elseif(@isset($_GET["unbauBL"])) {
	$moreBuildings = unserialize($planetrow['moreBuildings']);
	if(@isset($moreBuildings[$_GET['unbauBL']])) {

		// count buildings
		/*$buildingsTMP = $planetrow;
		foreach($moreBuildings as $buildingID) $buildingsTMP[$resource[$buildingID]]++;

		$costs = get_building_price($user,$buildingsTMP,$moreBuildings[$_GET['unbauBL']]);
		$planetrow['metal'] += $costs['metal'];
		$planetrow['crystal'] += $costs['crystal'];
		$planetrow['deuterium'] += $costs['deuterium'];*/

		unset($moreBuildings[$_GET['unbauBL']]);

		if($_GET['unbauBL'] == 0) {
			if(isset($moreBuildings[1])) {
				$moreBuildings[0] = $moreBuildings[1];
				unset($moreBuildings[1]);
				if(isset($moreBuildings[2])) {
					$moreBuildings[1] = $moreBuildings[2];
					unset($moreBuildings[2]);
					if(isset($moreBuildings[3])) $moreBuildings[2] = $moreBuildings[3];
				}
			}
		} else if($_GET['unbauBL'] == 1) {
			if(isset($moreBuildings[2])) {
				$moreBuildings[1] = $moreBuildings[2];
				unset($moreBuildings[2]);
				if(isset($moreBuildings[3])) $moreBuildings[2] = $moreBuildings[3];
			}
		} else if($_GET['unbauBL'] == 2 && isset($moreBuildings[3])) $moreBuildings[2] = $moreBuildings[3];

		unset($moreBuildings[3]);

		ksort($moreBuildings);

		$planetrow['moreBuildings'] = serialize($moreBuildings);

		$sql = "UPDATE ugml".LW_N."_planets SET
				"./*metal = '".$planetrow['metal']."',
				crystal = '".$planetrow['crystal']."',
				deuterium = '".$planetrow['deuterium']."',*/"
				moreBuildings = '".$planetrow['moreBuildings']."'
				WHERE id = '".$planetrow['id']."'";
		WCF::getDB()->sendQuery($sql);
	}
}
/*
  Peque&ntilde;a comprobacion en el cual se revisa si se esta construyendo algo en el planeta
  Si el tiempo en el row 'b_building_id' es distinto a cero. Este comprueba el tiempo
  de finalizacion de la construccion.
  Con time(); sacamos el tiempo actual y el tiempo en el que termina para la comprobacion.
  Si el time() es mayor. Se actualiza el edificio, y se reestablece cero el 'b_building_id'
  'b_building' no hace falta actualizarlo, porque solo nos basaremos en 'b_building_id'
*/
if($planetrow["b_building_id"] != 0){

	if($planetrow["b_building"] <= time()){
		$features = unserialize($user['diliziumFeatures']);

		// old
		/*
		  en este lugar se calculan los agregados de cada edificio, por ejemplo.
		  cuanto afecta un edificio a la produccion de recursos, y cuanta energia consume el mismo.
		*/
		$planetrow[$resource[$planetrow["b_building_id"]]]++;
		//$cost_metal = floor($pricelist[$planetrow["b_building_id"]]['metal'] * pow($pricelist[$planetrow["b_building_id"]]['factor'],$planetrow[$resource[$planetrow["b_building_id"]]]+1));
		//$cost_crystal = floor($pricelist[$planetrow["b_building_id"]]['crystal'] * pow($pricelist[$planetrow["b_building_id"]]['factor'],$planetrow[$resource[$planetrow["b_building_id"]]]+1));
		doquery("UPDATE {{table}} SET
			`{$resource[$planetrow["b_building_id"]]}`='{$planetrow[$resource[$planetrow["b_building_id"]]]}',
			b_building_id=0
			WHERE id='{$planetrow["id"]}'",'planets');
			
			
		LWCore::getPlanet()->b_building = $planetrow["b_building"];
		LWCore::getPlanet()->b_building_id = $planetrow["b_building_id"];
	
		BuildingOvent::check(LWCore::getPlanet()->planetID); 

		$moreBuildings = unserialize($planetrow['moreBuildings']);
		
		while(@isset($moreBuildings[0])) {
			if(is_tech_available($user,$planetrow, $moreBuildings[0]) && is_buyable($user,$planetrow,$moreBuildings[0])) {
				if(!check_building_progress($planetrow)) {
					$nextBuildingID = $moreBuildings[0];

					$costs = get_building_price($user,$planetrow,$nextBuildingID);

					$planetrow['metal'] -= $costs['metal'];
					$planetrow['crystal'] -= $costs['crystal'];
					$planetrow['deuterium'] -= $costs['deuterium'];

					LWCore::getPlanet()->metal = $planetrow['metal'];
					LWCore::getPlanet()->crystal = $planetrow['crystal'];
					LWCore::getPlanet()->deuterium = $planetrow['deuterium'];

					unset($moreBuildings[0]);


					if(isset($moreBuildings[1])) {
						$moreBuildings[0] = $moreBuildings[1];
						unset($moreBuildings[1]);
						if(isset($moreBuildings[2])) {
							$moreBuildings[1] = $moreBuildings[2];
							unset($moreBuildings[2]);
							if(isset($moreBuildings[3])) $moreBuildings[2] = $moreBuildings[3];
						}
					}
					unset($moreBuildings[3]);
					//$costs = get_building_price($user,$planetrow,$nextBuildingID);
					$time = (($costs['crystal']+$costs['metal']) / $game_config['game_speed']) * (1 / ($planetrow[$resource[14]] + 1)) * pow(0.5,$planetrow[$resource[15]]);
					$time = ($time * 60 * 60);

					ksort($moreBuildings);

					$planetrow['moreBuildings'] = serialize($moreBuildings);
					//echo '>>'.$planetrow['moreBuildings'].'<<>>'.$time.'<<<br />';
					$planetrow["b_building"] = $planetrow["b_building"] + floor($time);
					$sql = "UPDATE ugml".LW_N."_planets SET
								metal = metal - ".$costs['metal'].",
								crystal = crystal - ".$costs['crystal'].",
								deuterium = deuterium - ".$costs['deuterium'].",
								crystal = '".$planetrow['crystal']."',
								deuterium = '".$planetrow['deuterium']."',
								b_building_id = '".$nextBuildingID."',
								b_building = '".$planetrow['b_building']."',
								moreBuildings = '".$planetrow['moreBuildings']."',
								".$resource[$planetrow['b_building_id']]." = '".$planetrow[$resource[$planetrow['b_building_id']]]."'
							WHERE id = '".$planetrow['id']."'";
					WCF::getDB()->sendQuery($sql);
					if($planetrow['b_building'] < time) {
						$planetrow['b_building'] = 0;
						$planetrow['b_building_id'] = 0;
						//$planetrow[$planetrow['b_building_id']]++;
					} else $planetrow['b_building_id'] = $nextBuildingID;
					$planetrow[$resource[$planetrow["b_building_id"]]]++;

					/*if($building && $planetrow["b_building"] < time()) {
						//$planetrow[$resource[$planetrow["b_building_id"]]]++;
						doquery("UPDATE {{table}} SET
							`{$resource[$planetrow["b_building_id"]]}`='{$planetrow[$resource[$planetrow["b_building_id"]]]}',
							b_building_id=0
							WHERE id='{$planetrow["id"]}'",'planets');
					}*/
					$building = true;
					
					
					LWCore::getPlanet()->b_building = $planetrow["b_building"];
					LWCore::getPlanet()->b_building_id = $planetrow["b_building_id"];
	
					BuildingOvent::check(LWCore::getPlanet()->planetID); 
				}
				else
					break;
			} else {
				$planetrow['moreBuildings'] = serialize(array());
				$sql = "UPDATE ugml".LW_N."_planets SET
						moreBuildings = '".$planetrow['moreBuildings']."'
						WHERE id = '".$planetrow['id']."'";
				WCF::getDB()->sendQuery($sql);
				//$planetrow[$resource[$planetrow["b_building_id"]]]++;

				$planetrow["b_building"] = 0;

				unset($moreBuildings);
			}
		} /*else $planetrow["b_building_id"] = 0;*/
	}else{
		$building = true;
	}

}

$list='';
$row = gettemplate('buildings_builds_row');

$moreBuildings = unserialize($planetrow['moreBuildings']);
$buildingsTMP = $planetrow;
if((@isset($moreBuildings[0]) && $planetrow['b_building_id']) || ($features['buildList'] >= time() && $planetrow['b_building_id'])) {
	// view first building
	$list .= '<tr>';
	$list .= '<td class="l">';
	$list .= '</td>';
	$list .= '<td class="l">';
	$list .= '1.: '.$lang['tech'][$planetrow['b_building_id']];
	$buildingsTMP[$resource[$planetrow['b_building_id']]]++;
	$list .= ' '.$lang['level'].' '.$buildingsTMP[$resource[$planetrow['b_building_id']]];
	$list .= '</td>';
	$list .= '<td class="k">';
	$t['time'] = $planetrow["b_building"] - time();
	$t['building_id'] = $planetrow["b_building_id"];
	$t['id'] = $planetrow["id"];
	$list .= parsetemplate(gettemplate('buildings_builds_script'), $t);
	$list .= '</td>';
	$list .= '</tr>';

	// view other buldings
	if(is_array($moreBuildings)) {
		foreach($moreBuildings as $i => $buildingID) {
			$list .= '<tr>';
			$list .= '<td class="l">';
			$list .= '</td>';
			$list .= '<td class="l">';
			$list .= ($i + 2).'.: '.$lang['tech'][$buildingID];
			$buildingsTMP[$resource[$buildingID]]++;
			$list .= ' '.$lang['level'].' '.$buildingsTMP[$resource[$buildingID]];
			$list .= '</td>';
			$list .= '<td class="k">';
			//$t['time'] = $planetrow["b_building"] - time();
			//$t['building_id'] = $buildingID;
			//$t['id'] = $planetrow["id"];
			//$list .= parsetemplate(gettemplate('buildings_builds_script'), $t);
			$list .= '<a href="buildings.php?unbauBL='.$i.'">Abbrechen</a>';
			$list .= '</td>';
			$list .= '</tr>';
		}
	}
}
$moreBuildings = unserialize($planetrow['moreBuildings']);
$buildableBuildings = LWCore::getPlanet()->getBuildableBuildings();
//foreach($lang['tech'] as $i => $n){
foreach($buildableBuildings as $i) {
	$n = $lang['tech'][$i];
	//if($i > 0&& $i != 40 && $i != 41 && $i != 42 && $i != 43 && $i < 100){

		if(!is_tech_available($user,$planetrow,$i)){//:)
			//$list .= '</tr>';
		}
		else{
			$parse = array();
			$parse['dpath'] = $dpath;
			$parse['i'] = $i;
			//obtenemos el nivel del edificio
			$building_level = $planetrow[$resource[$i]];
			//Muestra el nivel actual de la mina
			$parse['nivel'] = ($building_level == 0) ? "" : " ({$lang['level']} $building_level)";
			$parse['n'] = $n;
			$parse['descriptions'] = $lang['res']['descriptions'][$i];
			/*
			  Calculo del tiempo de produccion
			  [(Cris+Met)/2500]*[1/(Nivel f.robots+1)]* 0,5^NivelFabrica Nanos.
			*/
			$time = get_building_time($user,$planetrow,$i);
			//informacion del precio, etc
			$parse['time'] = building_time($time);
			$parse['price'] = price($user,$planetrow,$i);
			$parse['rest_price'] = rest_price($user,$planetrow,$i);
			//Comprobacion si es posible comprarlo
			$is_buyeable = is_buyeable($user,$planetrow,$i);
			$parse['click'] = '';
			
			if(!$planetrow["b_building_id"] || $features['buildList'] < time()) {
				if(!$building){
					if($user["b_tech_planet"] != 0 && $i == 31 && $game_config['allow_invetigate_while_lab_is_update'] != 1){
					//en caso de que sea el laboratorio y se este investigando algo.
						$parse['click'] = "<font color=#FF0000>{$lang['Teching']}</font>";
					//Muestra la opcion a elegir para construir o ampliar un edificio
					}elseif($planetrow[$resource[$i]] == 0 && /*$planetrow["field_current"] < get_max_field($planetrow)*/LWCore::getPlanet()->checkFields()  && $is_buyeable && $user['urlaubs_modus'] == 0){
						$parse['click'] = "<a href=\"?bau=$i\"><font color=#00FF00>{$lang['Build']}</font></a>";
					}elseif(/*$planetrow["field_current"] < get_max_field($planetrow)*/LWCore::getPlanet()->checkFields() && $is_buyeable && $user['urlaubs_modus'] == 0){
						$nplus = $planetrow[$resource[$i]] + 1;
						$parse['click'] = "<a href=\"?bau=$i\"><font color=#00FF00>".str_replace('%n',$nplus,$lang['Update_to_n'])."</font></a>";
					}elseif(/*$planetrow["field_current"] < get_max_field($planetrow)*/LWCore::getPlanet()->checkFields() && !$is_buyeable || $user['urlaubs_modus'] != 0){
						if($planetrow[$resource[$i]] == 0){
							$parse['click'] = "<font color=#FF0000>{$lang['Build']}</font>";
						}else{
							$nplus = $planetrow[$resource[$i]] + 1;
							$parse['click'] = '<font color=#FF0000>'.str_replace('%n',$nplus,$lang['Update_to_n']).'</font>';
						}
					}else{
						$parse['click'] = "<font color=#FF0000>{$lang['Planet_full']}</font>";
					}

				}elseif($planetrow["b_building_id"] == $i){
					/*
					  no lo puedo creer, esta funcionando T_T
					*/
					$time = $planetrow["b_building"] - time();
					$t = array();
					$t['time'] = $time;
					$t['building_id'] = $planetrow["b_building_id"];
					$t['id'] = $planetrow["id"];
					$parse['click'] = parsetemplate(gettemplate('buildings_builds_script'), $t);
				}
			} else {
				if(count($moreBuildings) < 4) $parse['click'] = '<a href="?bau='.$i.'">In Bauliste</a>';
				else $parse['click'] = '';
			}

			$list .= parsetemplate($row, $parse);

		}

	//}

}

	$parse = $lang;
	$parse['list'] = $list;
	//fragmento de template
	$page .= parsetemplate(gettemplate('buildings_builds'), $parse);

	display($page,$lang['Builds']);

die();}

}

// Created by Perberos. All rights reversed (C) 2006
?>
