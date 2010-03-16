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
  //planet_maker.php :: Creador de planetas v0.2

//
//  Genera un planeta, en el lugar especificado.
//  Usos: Colonizador

function make_planet($g,$s,$pos,$user_id,$name=''){

//ahora preguntamos si existe el planeta...
$query = doquery("SELECT id FROM {{table}} WHERE
	galaxy='{$g}' AND
	system='{$s}' AND
	planet='{$pos}' AND planet_type = '1'",'planets',true);
//return false because a planet exist in that position
if($query){return false;}
/*
  En un principio a uno le asignan un planeta, en una ubicación aleatoria,
  dentro de uno de los 499 sistemas posibles de las 9 galaxias que componen cada universo
  (actualmente es posible poblar un solo universo); cada sistema está compuesto por una estrella
  y 15 planetas que giran a su alrededor.
*/
$planet = array();
/*
  Desde el principio uno cuenta con 500 unidades de Metal y 500 unidades de Cristal y
  con una producción por hora de 20 nidades de Metal y 10 de Cristal.
*/
$planet['metal'] = 500;
$planet['crystal'] = 500;
$planet['deuterium'] = 500;
$planet['metal_perhour'] = 20;
$planet['crystal_perhour'] = 10;
$planet['deuterium_perhour'] = 0;
$planet['metal_max'] = 100000;
$planet['crystal_max'] = 100000;
$planet['deuterium_max'] = 100000;
/*
  Pos 1 -3: 80% entre 40 y 70 Campos ( 55+/-15)
  Pos 4 - 6: 80% entre 120 y 310 Campos (215+/-95)
  Pos 7 - 9: 80% entre 105 y 195 Campos (150+/-45)
  Pos 10 - 12: 80% entre 75 y 125 Campos (100+/-25)
  Pos 13 - 15: 80% entre 60 y 190 Campos (125+/-65)
  Por ejemplo, tienes 80% de posibilidad que el planeta tiene entre 60 y 190 campos el valor 60
  puede tener hasta 125 campos mas y el valor 190 puede tener hasta 65 campos menos.
  Los 20% faltantes puede ser cualquier otro tamaño.
*/
$planet['galaxy'] = $g;
$planet['system'] = $s;
$planet['planet'] = $pos;
/*
  los campos pueden variar según su ubicación, esta cantidad de campos
  es aleatoria, pero varia en probabilides:
*/
if($pos==1||$pos==2||$pos==3){
	//array de imagenes posibles...
	$type = array('trocken');//,'gas'
	$class = array('planet');
	$number = array('01','02','03','04','05','06','07','08','09','10');
	//los demas valores
	$planet['field_max'] = rand(140,250);
	$planet['temp_min'] = rand(0,100);
	$planet['temp_max'] = $planet['temp_min'] + 40;
}elseif($pos==4||$pos==5||$pos==6){
	//array de imagenes posibles...
	$type = array('dschjungel');
	$class = array('planet');
	$number = array('01','02','03','04','05','06','07','08','09','10');
	//los demas valores
	$planet['field_max'] = rand(330,670);
	$planet['temp_min'] = rand(-25,75);
	$planet['temp_max'] = $planet['temp_min'] + 40;
}elseif($pos==7||$pos==8||$pos==9){
	//array de imagenes posibles...
	$type = array('normaltemp');
	$class = array('planet');
	$number = array('01','02','03','04','05','06','07');
	//los demas valores
	$planet['field_max'] = rand(260,440);
	$planet['temp_min'] = rand(-50,50);
	$planet['temp_max'] = $planet['temp_min'] + 40;
}elseif($pos==10||$pos==11||$pos==12){
	//array de imagenes posibles...
	$type = array('wasser');
	$class = array('planet');
	$number = array('01','02','03','04','05','06','07','08','09');
	//los demas valores
	$planet['field_max'] = rand(140,250);
	$planet['temp_min'] = rand(-75,25);
	$planet['temp_max'] = $planet['temp_min'] + 40;
}elseif($pos==13||$pos==14||$pos==15){
	//array de imagenes posibles...
	$type = array('eis');
	$class = array('planet');
	$number = array('01','02','03','04','05','06','07','08','09','10');
	//los demas valores
	$planet['field_max'] = rand(200,300);
	$planet['temp_min'] = rand(-100,10);
	$planet['temp_max'] = $planet['temp_min'] + 40;
}else{
	//array de imagenes posibles...
	$type = array('dschjungel','gas','normaltemp','trocken','wasser','wuesten','eis');
	$class = array('planet');
	$number = array('01','02','03','04','05','06','07','08','09','10','00',);
	//los demas valores
	$planet['field_max'] = rand(0,700);
	$planet['temp_min'] = rand(-120,10);
	$planet['temp_max'] = $planet['temp_min'] + 40;
}
//ahora generamos la imagen del planeta
$planet['image'] = $type[rand(0,count($type)-1)].
		$class[rand(0,count($class)-1)].
		$number[rand(0,count($number)-1)];

//El resto de las variables se llenan con los datos mas comunes.
$planet['planet_type'] = 1;
$planet['id_owner'] = $user_id;
$planet['last_update'] = time();
$planet['name'] = ($name=='')?'Kolonia':$name;
//El diametro del planeta depende de los campos maximos
//$planet['diameter'] = ($planet['field_max'] ^ (14 / 1.5)) * 75 ;
$planet['diameter'] = sqrt($planet['field_max']) * 1000;
//agregamos el planeta
doquery("INSERT INTO {{table}} SET
				`name`='{$planet['name']}',
				`id_owner`='{$planet['id_owner']}',
				`galaxy`='{$planet['galaxy']}',
				`system`='{$planet['system']}',
				`planet`='{$planet['planet']}',
				`last_update`='{$planet['last_update']}',
				`image`='{$planet['image']}',
				`diameter`='{$planet['diameter']}',
				`field_max`='{$planet['field_max']}',
				`temp_min`='{$planet['temp_min']}',
				`temp_max`='{$planet['temp_max']}',
				`metal`='{$planet['metal']}',
				`metal_perhour`='{$planet['metal_perhour']}',
				`metal_max`='{$planet['metal_max']}',
				`crystal`='{$planet['crystal']}',
				`crystal_perhour`='{$planet['crystal_perhour']}',
				`crystal_max`='{$planet['crystal_max']}',
				`deuterium`='{$planet['deuterium']}',
				`deuterium_perhour`='{$planet['deuterium_perhour']}',
				`deuterium_max`='{$planet['deuterium_max']}'"
				,'planets');
//pedimos el id del planeta
$query = doquery("SELECT id FROM {{table}} WHERE
				`galaxy`='{$planet['galaxy']}' AND
				`system`='{$planet['system']}' AND
				`planet`='{$planet['planet']}' AND
				`id_owner`='{$planet['id_owner']}'"
				,'planets',true);
//separamos el id
$planet['id'] = $query['id'];
//ahora nos fijamos si ya existe un row en galaxy
$query = doquery("SELECT * FROM {{table}} WHERE
				`galaxy`='{$planet['galaxy']}' AND
				`system`='{$planet['system']}' AND
				`planet`='{$planet['planet']}'"
				,'galaxy',true);
//Cada query correspondiente, no queremos accidentes ;)
if($query){
	//Actualizamos en galaxy
	$query = doquery("UPDATE {{table}} SET
				`id_planet`='{$planet['id']}' WHERE
				`galaxy`='{$planet['galaxy']}' AND
				`system`='{$planet['system']}' AND
				`planet`='{$planet['planet']}'"
				,'galaxy');
}else{
	//Agregamos en galaxy
	$query = doquery("INSERT INTO {{table}} SET
				`galaxy`='{$planet['galaxy']}',
				`system`='{$planet['system']}',
				`planet`='{$planet['planet']}',
				`id_planet`='{$planet['id']}'"
				,'galaxy');
}

return true;

}

?>