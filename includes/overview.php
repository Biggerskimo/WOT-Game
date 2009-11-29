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
 //overview.php   by DxPpLmOs

define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
include('ban.php');

if(!check_user()){ header("Location: login.$phpEx"); }

includeLang('overview');
includeLang('tech');
/*
  Checkear el tema de la lista de flotas
*/
include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);//Esta funcion permite cambiar el planeta actual.

$planetrow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_planet']}",'planets',true);
$lunarow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_luna']}",'lunas',true);
$galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet={$planetrow['id']}",'galaxy',true);
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

check_field_current($planetrow);
check_field_current($lunarow);


switch ($mode)
{
case 'renameplanet':{//Abandonar o renombrar planetas

	if($_POST['action'] == $lang['namer']){
		
		$newname = trim($_POST['newname']);
		
		if(!preg_match("/[^A-z0-9\ _\-]/", $newname) == 1 && $newname != ""){
			/*
			  Realmente no lo encuentro muy necesario. e incluso es esguro, 
			  porque si o si, se nombra en base al planeta actual
			*/
			$planetrow['name'] = $newname;
			doquery("UPDATE {{table}} SET `name`='$newname' WHERE `id`='{$user['current_planet']}' LIMIT 1","planets");
			
		}
	}
	elseif($_POST['action'] == $lang['colony_abandon']){
	
		$parse = $lang;
		
		$parse['planet_id'] = $planetrow['id'];
		$parse['galaxy_galaxy'] = $galaxyrow['galaxy'];
		$parse['galaxy_system'] = $galaxyrow['system'];
		$parse['galaxy_planet'] = $galaxyrow['planet'];
		$parse['planet_name'] = $planetrow['name'];
		
		$page .= parsetemplate(gettemplate('overview_deleteplanet'), $parse);
		
		display($page,$lang['rename_and_abandon_planet']);
		
	}
	elseif($_POST['action'] == $lang['deleteplanet'] && $_POST['deleteid'] == $user['current_planet']){

		//comprobamos la contraseña y comprobamos que el planeta actual no sea el planeta principal
		if(md5($_POST['pw']) == $user["password"] && $user['id_planet'] != $user['current_planet']){
			//actualizamos el el planeta para que este modo destruido
			
			//tiempo cuando se destruira, y quedara el espacio libre
			$destruyed = time() + 60*60*24;
			doquery("UPDATE {{table}} SET `destruyed` = '$destruyed', `id_owner` = 0 WHERE `id` = '{$user['current_planet']}' LIMIT 1","planets");
			doquery("UPDATE {{table}} SET
				current_planet=id_planet,
				points_points=points_points-{$planetrow['points']}
				WHERE id='{$user['id']}' LIMIT 1","users");
			message($lang['deletemessage_ok'],$lang['colony_abandon'],'overview.php?mode=renameplanet');
		}elseif($user['id_planet'] == $user["current_planet"]){ 
			message($lang['deletemessage_wrong'],$lang['colony_abandon'],'overview.php?mode=renameplanet');
		}else{message($lang['deletemessage_fail'],$lang['colony_abandon'],'overview.php?mode=renameplanet');}

	}

	$parse = $lang;

	$parse['planet_id'] = $planetrow['id'];
	$parse['galaxy_galaxy'] = $galaxyrow['galaxy'];
	$parse['galaxy_system'] = $galaxyrow['system'];
	$parse['galaxy_planet'] = $galaxyrow['planet'];
	$parse['planet_name'] = $planetrow['name'];
//	$parse['luna_name'] = $lunarow['name'];
            


	$page .= parsetemplate(gettemplate('overview_renameplanet'), $parse);

	display($page,$lang['rename_and_abandon_planet']);

}

default:{//-----------------------------------------------------------------

	//Agrega un link que lleva a la seccion de mensajes
	if($user['new_message'] == 1){
		$Have_new_message .= "<tr><th colspan=4><a href=messages.$phpEx>{$lang['Have_new_message']}</a></th></tr>";
	}elseif($user['new_message'] > 1){
		$Have_new_message .= "<tr><th colspan=4><a href=messages.$phpEx>";
		$m = pretty_number($user['new_message']);
		$Have_new_message .= str_replace('%m',$m,$lang['Have_new_messages']);
		$Have_new_message .= "</a></th></tr>";
	}
	/*
	  Lista de flotas actuales
	*/
	$missiontype = array(
		1 => 'Atakuj',
		3 => 'Transportuj',
		4 => 'Stacjonuj',
		5 => 'Destruir',
		6 => 'Szpieguj',
		7 => 'Stacjonuj?',
		8 => 'Zbieraj',
		9 => 'Kolonizuj',
		);
	/*
Aqui se debe de mostrar los movimientos de flotas, propios del jugador
*/
$fq = doquery("SELECT * FROM {{table}} WHERE fleet_owner={$user['id']}",'fleets');
$i=0;
while($f = mysql_fetch_array($fq)){
$i++;
$timerek = $f['fleet_start_time'];
$timerekend = $f['fleet_end_time'];
$czasek = $timerek - time();
$czasekend = $timerekend - time();
$fpage .="<script type=\"text/javascript\">
  function tfe$i(){
                v=new Date();
                var bxxfe$i=document.getElementById('bxxfe$i');
                n=new Date();
                ssfe$i=ppfe$i;
                ssfe$i=ssfe$i-Math.round((n.getTime()-v.getTime())/1000.);
                mfe$i=0;hfe$i=0;
                if(ssfe$i<0){
				  //ps como session :P
                  bxxfe$i.innerHTML=\"-\"
                }else{
                  if(ssfe$i>59){
                    mfe$i=Math.floor(ssfe$i/60);
                    ssfe$i=ssfe$i-mfe$i*60
                  }
                  if(mfe$i>59){
                    hfe$i=Math.floor(mfe$i/60);
                    mfe$i=mfe$i-hfe$i*60
                  }
                  if(ssfe$i<10){
                    ssfe$i=\"0\"+ssfe$i
                  }
                  if(mfe$i<10){
                    mfe$i=\"0\"+mfe$i
                  }
                  bxxfe$i.innerHTML=hfe$i+\":\"+mfe$i+\":\"+ssfe$i
                }
                ppfe$i=ppfe$i-1;
                window.setTimeout(\"tfe$i();\",999);

              }

</script>
<script type=\"text/javascript\">
  function tfs$i(){
                v=new Date();
                var bxxfs$i=document.getElementById('bxxfs$i');
                n=new Date();
                ssfs$i=ppfs$i;
                ssfs$i=ssfs$i-Math.round((n.getTime()-v.getTime())/1000.);
                mse$i=0;hse$i=0;
                if(ssfs$i<0){
				  //ps como session :P
                  bxxfs$i.innerHTML=\"-\"
                }else{
                  if(ssfs$i>59){
                    mse$i=Math.floor(ssfs$i/60);
                    ssfs$i=ssfs$i-mse$i*60
                  }
                  if(mse$i>59){
                    hse$i=Math.floor(mse$i/60);
                    mse$i=mse$i-hse$i*60
                  }
                  if(ssfs$i<10){
                    ssfs$i=\"0\"+ssfs$i
                  }
                  if(mse$i<10){
                    mse$i=\"0\"+mse$i
                  }
                  bxxfs$i.innerHTML=hse$i+\":\"+mse$i+\":\"+ssfs$i
                }
                ppfs$i=ppfs$i-1;
                window.setTimeout(\"tfs$i();\",999);

              }

</script>";
if($f['fleet_mission'] == 1)
   {
      $kolormisjido = lime;
   }
   if($f['fleet_mission'] == 2)
   {
      $kolormisjido = lime;
   }
    if($f['fleet_mission'] == 3)
   {
      $kolormisjido = lime;
   }
    if($f['fleet_mission'] == 4)
   {
      $kolormisjido = lime;
   }
    if($f['fleet_mission'] == 5)
   {
      $kolormisjido = lime;
   }
    if($f['fleet_mission'] == 6)
   {
      $kolormisjido = orange;
   }
    if($f['fleet_mission'] == 7)
   {
      $kolormisjido = lime;
   }
    if($f['fleet_mission'] == 8)
   {
      $kolormisjido = lime;
   }
    if($f['fleet_mission'] == 9)
   {
      $kolormisjido = lime;
   }
    if($f['fleet_mission'] == 1)
   {
      $kolormisjiz = green;
   }
    if($f['fleet_mission'] == 2)
   {
      $kolormisjiz = green;
   }
    if($f['fleet_mission'] == 3)
   {
      $kolormisjiz = green;
   }
    if($f['fleet_mission'] == 4)
   {
      $kolormisjiz = green;
   }
    if($f['fleet_mission'] == 5)
   {
      $kolormisjiz = green;
   }
    if($f['fleet_mission'] == 6)
   {
      $kolormisjiz = B45D00;
   }
    if($f['fleet_mission'] == 7)
   {
      $kolormisjiz = green;
   }
    if($f['fleet_mission'] == 8)
   {
      $kolormisjiz = green;
   }
    if($f['fleet_mission'] == 9)
   {
      $kolormisjiz = green;
   }
   $pierwszaplaneta =doquery("SELECT * FROM {{table}} WHERE galaxy={$f['fleet_start_galaxy']} AND system={$f['fleet_start_system']} AND planet={$f['fleet_start_planet']}",'planets',true);
   $drugaplaneta =doquery("SELECT * FROM {{table}} WHERE galaxy={$f['fleet_end_galaxy']} AND system={$f['fleet_end_system']} AND planet={$f['fleet_end_planet']}",'planets',true);
$fpage .= "<tr><th><div id=\"bxxfs$i\" class=\"z\"></div><font color=\"lime\">".gmdate("H:i:s",$f['fleet_start_time']+2*60*60)."</font> </th><th colspan=\"3\"><font color=\"$kolormisjido\">Jedna z twoich </font>";
$fpage .= '(<a title="';

/*
Se debe hacer una lista de las tropas
*/
$fleet = explode("\r\n",$f['fleet_array']);
$e=0;
foreach($fleet as $a =>$b){
if($b != ''){
$e++;
$a = explode(",",$b);
$fpage .= "{$lang['tech']{$a[0]}}: {$a[1]}\n";
if($e>1){$fpage .= "\t";}
}
}
$fpage .= "\">flot</a>)";
$fpage .= "<font color=\"$kolormisjido\">z planety {$pierwszaplaneta['name']}[{$f[fleet_start_galaxy]}:{$f[fleet_start_system]}:{$f[fleet_start_planet]}] osi±gnie planetê {$drugaplaneta['name']}</font>";
$fpage .= "<font color=\"$kolormisjido\">[{$f[fleet_end_galaxy]}:{$f[fleet_end_system]}:{$f[fleet_end_planet]}]. Misja: {$missiontype[$f[fleet_mission]]}</font></tr></th></div>";
$fpage .= "<tr><th><div id=\"bxxfe$i\" class=\"z\"></div><font color=\"lime\">".gmdate("H:i:s",$f['fleet_end_time']+2*60*60)."</font><br></th><th colspan=\"3\"><font color=\"$kolormisjiz\">Jedna z twoich </font>";
$fpage .= '(<a title="';

/*
Se debe hacer una lista de las tropas
*/
$fleet = explode("\r\n",$f['fleet_array']);
$e=0;
foreach($fleet as $a =>$b){
if($b != ''){
$e++;
$a = explode(",",$b);
$fpage .= "{$lang['tech']{$a[0]}}: {$a[1]}\n";
if($e>1){$fpage .= "\t";}
}
}
$fpage .= "\">flot</a>)";
$fpage .= "<font color=\"$kolormisjiz\">z planety {$drugaplaneta['name']}[{$f[fleet_start_galaxy]}:{$f[fleet_start_system]}:{$f[fleet_start_planet]}] powróci na planetê {$pierwszaplaneta['name']}</font>";
$fpage .= "<font color=\"$kolormisjiz\">[{$f[fleet_end_galaxy]}:{$f[fleet_end_system]}:{$f[fleet_end_planet]}]. Misja: {$missiontype[$f[fleet_mission]]}</font>";
$fpage .= "


<SCRIPT language=\"JavaScript\">
   ppfs$i=".$czasek.";
   tfs$i();
</script>";
$fpage .= "
<SCRIPT language=\"JavaScript\">
   ppfe$i=".$czasekend.";
   tfe$i();
</script>";




$fpage .= "</th>";



}

	/*
	  Cuando un jugador tiene mas de un planeta, se muestra una lista de ellos a la derecha.
	*/
	
	$planets_query = doquery("SELECT * FROM {{table}} WHERE id_owner='{$user['id']}'","planets");
	$c = 1;
	while($p = mysql_fetch_array($planets_query)){
		
		if($p["id"] != $user["current_planet"]){
			$ap .= "<th>{$p['name']}<br>
			<a href=\"?cp={$p['id']}&re=0\" title=\"{$p['name']}\"><img src=\"{$dpath}planeten/small/s_{$p['image']}.jpg\" height=\"50\" width=\"50\"></a><br>
			<center>";
			/*
			  Gracias al 'b_building_id' y al 'b_building' podemos mostrar en el overview
			  si se esta construyendo algo en algun planeta.
			*/
			if($p['b_building_id'] != 0){
				if(check_building_progress($p)){
					$ap .= $lang['tech'][$p['b_building_id']];
					$time = pretty_time($p['b_building'] - time());
					$ap .= "<br><font color=\"#7f7f7f\">({$time})</font>";
				}
				else{$ap .= $lang['Free'];}
			}else{$ap .= $lang['Free'];}
			
			$ap .= "<center></center></center></th>";
			//Para ajustar a dos columnas
			if($c <= 1){$c++;}else{$ap .= "</tr><tr>";$c = 1;	}
		}
	}


                $parse['FLOTA_TEST'] = ($user['authlevel'] == 1||$user['authlevel'] == 3)?'<tr><td><div align="center"><font color="#FFFFFF"><a href="buildings.php?mode=fleet" accesskey="u" target="{mf}">KLIKNIJ TUTAJ NA TEST FLOTY</a></font></div></td></tr>
':'';

	$parse = $lang;
#Mooon

/***$lunas_query = doquery("SELECT * FROM {{table}} WHERE id_owner='{$user['id']}'","lunas");
	$c1 = 1;
	while($p1 = mysql_fetch_array($lunas_query)){
		
		if($p1["id"] != $user["current_luna"]){
			$ap1 .= "<th>{$p1['name']}<br>
			<a href=\"?cp={$p1['id']}&re=0\" title=\"{$p1['name']}\"><img src=\"{$dpath}planeten/small/s_{$p1['image']}.jpg\" height=\"50\" width=\"50\"></a><br>
			<center>";
			/*
			  Gracias al 'b_building_id' y al 'b_building' podemos mostrar en el overview
			  si se esta construyendo algo en algun planeta.
			*/
/*
			if($p1['b_building_id'] != 0){
				if(check_building_progress($p)){
					$ap1 .= $lang['tech'][$p1['b_building_id']];
					$time = pretty_time($p1['b_building'] - time());
					$ap1 .= "<br><font color=\"#7f7f7f\">({$time})</font>";
				}
				else{$ap1 .= $lang['Free'];}
			}else{$ap1 .= $lang['Free'];}
			
			$ap1 .= "<center></center></center></th>";
			//Para ajustar a dos columnas
			if($c1 <= 1){$c1++;}else{$ap1 .= "</tr><tr>";$c1 = 1;	}
		}
}***/	
if ($user['current_luna'] == $lunarow['id_luna'] && $planetrow['galaxy'] == $lunarow['galaxy'] && $planetrow['system'] == $lunarow['system'] && $planetrow['planet'] == $lunarow['lunapos'])
{
	$parse['moon_img'] = "<img src=\"{dpath}planeten/{$lunarow['image']}.jpg\" height=\"50\" width=\"50\">";		
	$parse['moon'] = $lunarow['name'];
	}
	else
	{
	$parse['moon_img'] ="";
	$parse['moon'] = "";
	}
#Moon END
	$parse['planet_name'] = $planetrow['name'];
	$parse['planet_diameter'] = $planetrow['diameter'];
	$parse['planet_field_current'] = $planetrow['field_current'];
	$parse['planet_field_max'] = get_max_field($planetrow);
	$parse['planet_temp_min'] = $planetrow['temp_min'];
	$parse['planet_temp_max'] = $planetrow['temp_max'];
	$parse['galaxy_galaxy'] = $galaxyrow['galaxy'];
	$parse['galaxy_planet'] = $galaxyrow['planet'];
	$parse['galaxy_system'] = $galaxyrow['system'];
	$parse['user_points'] = pretty_number($user['points_points']/1000);
	$rank = doquery("SELECT COUNT(DISTINCT(id)) FROM {{table}} WHERE points_points>={$user['points_points']}","users",true);
	$parse['user_rank'] = $rank[0];
	$parse['u_user_rank'] = $rank[0];
	$parse['user_username'] = $user['username'];
	$parse['fleet_list'] = $fpage;
	$parse['energy_used'] = $planetrow["energy_max"]-$planetrow["energy_used"];

	$parse['Have_new_message'] = $Have_new_message;
	$parse['time'] = date("D M d H:i:s",time());

	$parse['dpath'] = $dpath;

	$parse['planet_image'] = $planetrow['image'];
	$parse['anothers_planets'] = $ap;
	$parse['max_users'] = $game_config['users_amount'];
	//Muestra los escombros en la posicion del planeta  * Agregado en v0.1 r46 *
	$parse['metal_debris'] = $galaxyrow['metal'];
	$parse['crystal_debris'] = $galaxyrow['crystal'];
	//El link
	if(($galaxyrow['metal']!=0||$galaxyrow['crystal']!=0)&&$planetrow[$resource[209]]!=0){
		$parse['get_link'] = " (<a href=\"quickfleet.php?mode=harvest&g={$galaxyrow['system']}&s={$galaxyrow['system']}&p={$galaxyrow['planet']}\">{$lang['Harvest']}</a>)";
	}else{$parse['get_link'] = '';}
	//
	//Muestra la actual contruccion en el planeta
	//Y un contador, gracias NaNiN por la sugerencia
	if($planetrow['b_building_id']!=0&&$planetrow['b_building']>time()){
		$parse['building'] = $lang['tech'][$planetrow['b_building_id']].
		'<br><div id="bxx" class="z">'.pretty_time($planetrow['b_building'] - time()).'</div><SCRIPT language=JavaScript>
		pp="'.($planetrow['b_building'] - time()).'";
		pk="'.$planetrow["b_building_id"].'";
		pl="'.$planetrow["id"].'";
		ps="buildings.php";
		t();
	</script>';
		//$time =  pretty_time();
		//$a['building'] = "<br><font color=\"#7f7f7f\">({$time})</font>";
		// = parsetemplate(gettemplate('overview_body'), $parse);
	}else{
		$parse['building'] = $lang['Free'];
	}
{//Vista normal
                $query = doquery('SELECT username FROM {{table}} ORDER BY register_time DESC','users',true);
	$parse['last_user'] = $query['username'];
	$query = doquery("SELECT COUNT(DISTINCT(id)) FROM {{table}} WHERE onlinetime>".(time()-900),'users',true);
	$parse['online_users'] = $query[0];
	//$count = doquery(","users",true);
	$parse['users_amount'] = $game_config['users_amount'];


}

//include"moon.php";
	$page = parsetemplate(gettemplate('overview_body'), $parse);

 
	display($page,$lang['Overview']);

}

}
// Created by Perberos. All rights reversed (C) 2006
?>
