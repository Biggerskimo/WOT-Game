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



if(!defined('INSIDE')){ die("attemp hacking");}



{$pricelist = array(

1 => array('metal'=>40,'crystal'=>10,'deuterium'=>0,'energy'=>0,'factor'=>3/2,"description"=>"Las minas de metal proveen los recursos básicos de un imperio emergente, y permiten la construcción de edificios y naves."),
2 => array('metal'=>30,'crystal'=>15,'deuterium'=>0,'energy'=>0,'factor'=>1.6,"description"=>"Los cristales son el recurso principal usado para construir circuitos electrónicos y ciertas aleaciones."),
3 => array('metal'=>150,'crystal'=>50,'deuterium'=>0,'energy'=>0,'factor'=>3/2,"description"=>"El deuterio se usa como combustible para naves, y se recolecta en el mar profundo. Es una sustancia muy escasa, y por ello, relativamente cara."),
4 => array('metal'=>50,'crystal'=>20,'deuterium'=>0,'energy'=>0,'factor'=>3/2,"description"=>"Las plantas de energía solar convierten energía fotónica en energía eléctrica, para su uso en casi todos los edificios y estructuras."),
12 => array('metal'=>500,'crystal'=>200,'deuterium'=>100,'energy'=>0,'factor'=>1.8,"description"=>"Un reactor de fusión nuclear que produce un átomo de helio a partir de dos átomos de deuterio usando una presión extremadamente alta y una elevadísima temperatura."),
14 => array('metal'=>200,'crystal'=>60,'deuterium'=>100,'energy'=>0,'factor'=>2,"description"=>"Las fábricas de robots proporcionan unidades baratas y de fácil construcción que pueden ser usadas para mejorar o construir cualquier estructura planetaria. Cada nivel de mejora de la fábrica aumenta la eficiencia y el numero de unidades robóticas que ayudan en la construcción."),
15 => array('metal'=>500000,'crystal'=>250000,'deuterium'=>50000,'energy'=>0,'factor'=>2,"description"=>"La fábrica de nanobots es la última evolución de la robótica. Cada mejora proporciona nanobots más y más eficientes que incrementan la velocidad de construcción."),
21 => array('metal'=>200,'crystal'=>100,'deuterium'=>50,'energy'=>0,'factor'=>2,"description"=>"El hangar es el lugar donde se construyen naves y estructuras de defensa planetaria."),
22 => array('metal'=>1000,'crystal'=>0,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"Almacén de metal sin procesar."),
23 => array('metal'=>1000,'crystal'=>500,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"Almacén de cristal sin procesar."),
24 => array('metal'=>1000,'crystal'=>1000,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"Contenedores enormes para almacenar deuterio."),
31 => array('metal'=>100,'crystal'=>200,'deuterium'=>100,'energy'=>0,'factor'=>2,"description"=>"Se necesita un laboratorio de investigación para conducir la investigación en nuevas tecnologías."),
33 => array('metal'=>0,'crystal'=>25000,'deuterium'=>5000,'energy'=>500,'factor'=>2,"description"=>"El Terraformer es necesario para habilitar áreas inaccesibles de tu planeta para edificar infraestructuras."),
34 => array('metal'=>10000,'crystal'=>20000,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"El depósito de la alianza ofrece la posibilidad de repostar a las flotas aliadas que estén estacionadas en la órbita ayudando a defender."),
44 => array('metal'=>10000,'crystal'=>10000,'deuterium'=>500,'energy'=>0,'factor'=>2,"description"=>"El silo es un lugar de almacenamiento y lanzamiento de misiles planetarios."),
//Tecnologias
106 => array('metal'=>200,'crystal'=>1000,'deuterium'=>200,'energy'=>0,'factor'=>2,"description"=>"Usando esta tecnología, puede obtenerse información sobre otros planetas."),
108 => array('metal'=>0,'crystal'=>400,'deuterium'=>600,'energy'=>0,'factor'=>2,"description"=>"Cuanto más elevado sea el nivel de tecnología de computación, más flotas podrás controlar simultaneamente. Cada nivel adicional de esta tecnologia, aumenta el numero de flotas en 1."),
109 => array('metal'=>800,'crystal'=>200,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"Este tipo de tecnología incrementa la eficiencia de tus sistemas de armamento. Cada mejora de la tecnología militar añade un 10% de potencia a la base de daño de cualquier arma disponible."),
110 => array('metal'=>200,'crystal'=>600,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"La tecnología de defensa se usa para generar un escudo de partículas protectoras alrededor de tus estructuras. Cada nivel de esta tecnología aumenta el escudo efectivo en un 10% (basado en el nivel de una estructura dada)."),
111 => array('metal'=>1000,'crystal'=>0,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"Las aleaciones altamente sofisticadas ayudan a incrementar el blindaje de una nave añadiendo el 10% de su fuerza en cada nivel a la fuerza base."),
113 => array('metal'=>0,'crystal'=>800,'deuterium'=>400,'energy'=>0,'factor'=>2,"description"=>"Entendiendo la tecnología de diferentes tipos de energía, muchas investigaciones nuevas y avanzadas pueden ser adaptadas. La tecnología de energía es de gran importancia para un laboratorio de investigación moderno."),
114 => array('metal'=>0,'crystal'=>4000,'deuterium'=>2000,'energy'=>0,'factor'=>2,"description"=>"Incorporando la cuarta y quinta dimensión en la tecnología de propulsión, se puede disponer de un nuevo tipo de motor; que es más eficiente y usa menos combustible que los convencionales."),
115 => array('metal'=>400,'crystal'=>0,'deuterium'=>600,'energy'=>0,'factor'=>2,"description"=>"Ejecutar investigaciones en esta tecnología proporciona motores de combustión siempre más rapido, aunque cada nivel aumenta solamente la velocidad en un 10% de la velocidad base de una nave dada."),
117 => array('metal'=>2000,'crystal'=>4000,'deuterium'=>6000,'energy'=>0,'factor'=>2,"description"=>"El sistema del motor de impulso se basa en el principio de la repulsión de partículas. La materia repelida es basura generada por el reactor de fusión usado para proporcionar la energía necesaria para este tipo de motor de propulsión."),
118 => array('metal'=>10000,'crystal'=>20000,'deuterium'=>6000,'energy'=>0,'factor'=>2,"description"=>"Los motores de hiperespacio permiten entrar al mismo a través de una ventana hiperespacial para reducir drásticamente el tiempo de viaje. El hiperespacio es un espacio alternativo con más de 3 dimensiones."),
120 => array('metal'=>200,'crystal'=>100,'deuterium'=>0,'energy'=>0,'factor'=>2,"description"=>"La tecnología láser es un importante conocimiento; conduce a la luz monocromática firmemente enfocada sobre un objetivo. El daño puede ser ligero o moderado dependiendo de la potencia del rayo..."),
121 => array('metal'=>1000,'crystal'=>300,'deuterium'=>100,'energy'=>0,'factor'=>2,"description"=>"La tecnología iónica enfoca un rayo de iones acelerados en un objetivo, lo que puede provocar un gran daño debido a su naturaleza de electrones cargados de energía."),
122 => array('metal'=>2000,'crystal'=>4000,'deuterium'=>1000,'energy'=>0,'factor'=>2,"description"=>"Las armas de plasma son incluso más peligrosas que cualquier otro sistema de armamento conocido, debido a la naturaleza agresiva del plasma"),
123 => array('metal'=>240000,'crystal'=>400000,'deuterium'=>160000,'energy'=>0,'factor'=>2,"description"=>"Los científicos de tus planetas pueden comunicarse entre ellos a través de esta red."),
199 => array('metal'=>0,'crystal'=>0,'deuterium'=>0,'energy'=>300000,'factor'=>3,"description"=>"A través del disparo de partículas concentradas de gravitón se genera un campo gravitacional artificial con suficiente potencia y poder de atracción para destruir no solo naves, sino lunas enteras."),
//Naves espaciales
202 => array('metal'=>2000,'crystal'=>2000,'deuterium'=>0,'energy'=>0,'consumption'=>20,'speed'=>28000,'capacity'=>5000,'name'=>"Nave pequeña de carga",'description'=>"Las naves pequeñas de carga son naves muy ágiles usadas para transportar recursos desde un planeta a otro."),
203 => array('metal'=>6000,'crystal'=>6000,'deuterium'=>0,'energy'=>0,'consumption'=>50,'speed'=>17250,'capacity'=>25000,'name'=>"Nave grande de carga",'description'=>"La nave grande de carga es una versión avanzada de las naves pequeñas de carga, permitiendo así una mayor capacidad de almacenamiento y velocidades más altas gracias a un mejor sistema de propulsión."),
204 => array('metal'=>3000,'crystal'=>1000,'deuterium'=>0,'energy'=>0,'consumption'=>20,'speed'=>28750,'capacity'=>50,'name'=>"Cazador ligero",'description'=>"El cazador ligero es una nave maniobrable que puedes encontrar en casi cualquier planeta. El coste no es particularmente alto, pero asimismo el escudo y la capacidad de carga son muy bajas."),
205 => array('metal'=>6000,'crystal'=>4000,'deuterium'=>0,'energy'=>0,'consumption'=>75,'speed'=>28000,'capacity'=>100,'name'=>"Cazador pesado",'description'=>"El cazador pesado es la evolución logica del ligero, ofreciendo escudos reforzados y una mayor potencia de ataque."),
206 => array('metal'=>20000,'crystal'=>7000,'deuterium'=>2000,'energy'=>0,'consumption'=>300,'speed'=>42000,'capacity'=>800,'name'=>"Crucero",'description'=>"Los cruceros de combate tienen un escudo casi tres veces más fuerte que el de los cazadores pesados y más del doble de potencia de ataque. Su velocidad de desplazamiento está también entre las más rápidas jamás vista."),
207 => array('metal'=>40000,'crystal'=>20000,'deuterium'=>0,'energy'=>0,'consumption'=>500,'speed'=>31000,'capacity'=>1500,'name'=>"Nave de batalla",'description'=>"Las naves de batalla son la espina dorsal de cualquier flota militar. Blindaje pesado, potentes sistemas de armamento y una alta velocidad de viaje, así como una gran capacidad de carga hace de esta nave un duro rival contra el que luchar."),
208 => array('metal'=>10000,'crystal'=>20000,'deuterium'=>10000,'energy'=>0,'name'=>"Colonizador",'description'=>"Esta nave proporciona lo necesario para ir a donde ningún hombre ha llegado antes y colonizar nuevos mundos."),
209 => array('metal'=>10000,'crystal'=>6000,'deuterium'=>2000,'energy'=>0,'consumption'=>300,'speed'=>4600,'capacity'=>20000,'name'=>"Reciclador",'description'=>"Los recicladores se usan para recolectar escombros flotando en el espacio para reciclarlos en recursos útiles."),
210 => array('metal'=>0,'crystal'=>1000,'deuterium'=>0,'energy'=>0,'consumption'=>1,'speed'=>230000000,'capacity'=>5,'name'=>"Sonda de espionaje",'description'=>"Las sondas de espionaje son pequeños droides no tripulados con un sistema de propulsión excepcionalmente rápido usado para espiar en planetas enemigos."),
211 => array('metal'=>50000,'crystal'=>25000,'deuterium'=>15000,'energy'=>0,'consumption'=>1000,'speed'=>11200,'capacity'=>500,'name'=>"Bombardero",'description'=>"El Bombardero es una nave de propósito especial, desarrollado para atravesar las defensas planetarias más pesadas."),
212 => array('metal'=>0,'crystal'=>2000,'deuterium'=>500,'energy'=>0,'name'=>"Satélite solar",'description'=>"Los satélites solares son simples satélites en órbita equipados con células fotovoltaicas y transmisores para llevar la energía al planeta. Se transmite por este medio a la tierra usando un rayo láser especial."),
213 => array('metal'=>60000,'crystal'=>50000,'deuterium'=>15000,'energy'=>0,'consumption'=>1000,'speed'=>15500,'capacity'=>2000,'name'=>"Destructor",'description'=>"El destructor es la nave más pesada jamás vista y posee un potencial de ataque sin precedentes."),
214 => array('metal'=>5000000,'crystal'=>4000000,'deuterium'=>1000000,'energy'=>0,'name'=>"Estrella de la muerte",'description'=>"No hay nada tan grande y peligroso como una estrella de la muerte aproximándose."),
//Sistemas de defensa
401 => array('metal'=>2000,'crystal'=>0,'deuterium'=>0,'energy'=>0,"description"=>"El lanzamisiles es un sistema de defensa sencillo, pero barato."),
402 => array('metal'=>1500,'crystal'=>500,'deuterium'=>0,'energy'=>0,"description"=>"Por medio de un rayo láser concentrado, se puede provocar más daño que con las armas balísticas normales."),
403 => array('metal'=>6000,'crystal'=>2000,'deuterium'=>0,'energy'=>0,"description"=>"Los lásers grandes posee una mejor salida de energía y una mayor integridad estructural que los lásers pequeños."),
404 => array('metal'=>20000,'crystal'=>15000,'deuterium'=>2000,'energy'=>0,"description"=>"Usando una inmensa aceleración electromagnética, los cañones gauss aceleran proyectiles pesados."),
405 => array('metal'=>2000,'crystal'=>6000,'deuterium'=>0,'energy'=>0,"description"=>"Los cañones iónicos disparan rayos de iones altamente energéticos contra su objetivo, desestabilizando los escudos y destruyendo los componentes electrónicos."),
406 => array('metal'=>50000,'crystal'=>50000,'deuterium'=>30000,'energy'=>0,"description"=>"Los cañones de plasma liberan la energía de una pequeña erupción solar en una bala de plasma. La energía destructiva es incluso superior a la del Destructor."),
407 => array('metal'=>10000,'crystal'=>10000,'deuterium'=>0,'energy'=>0,"description"=>"La cúpula pequeña de protección cubre el planeta con un delgado campo protector que puede absorber inmensas cantidades de energía."),
408 => array('metal'=>50000,'crystal'=>50000,'deuterium'=>0,'energy'=>0,"description"=>"La cúpula grande de protección proviene de una tecnología de defensa mejorada que absorbe incluso más energía antes de colapsarse."),
502 => array('metal'=>8000,'crystal'=>2000,'deuterium'=>0,'energy'=>0,"description"=>"Los misiles de intercepción destruyen los misiles interplanetarios."),
503 => array('metal'=>12500,'crystal'=>2500,'deuterium'=>10000,'energy'=>0,"description"=>"Los misiles interplanetarios destruyen los sistemas de defensa del enemigo."),
//Construcciones especiales
41 => array('metal'=>10000,'crystal'=>20000,'deuterium'=>10000,'energy'=>0,"description"=>"Dado que la luna no tiene atmósfera, se necesita una base lunar para generar espacio habitable."),
42 => array('metal'=>10000,'crystal'=>20000,'deuterium'=>10000,'energy'=>0,"description"=>"Usando el sensor phalanx, las flotas de otros imperios pueden ser descubiertas y observadas. Cuanto mayor sea la cadena de sensores phalanx, mayor el rango que pueda escanear."),
43 => array('metal'=>1000000,'crystal'=>2000000,'deuterium'=>1000000,'energy'=>0,"description"=>"El salto cuántico usa portales transmisores-receptores capaces de enviar incluso la mayor flota instantaneamente a un portal lejano.")

);}

$lang['Multiverse'] = 'DROGA MLECZNA';
$lang['description'] = 'Opis';
$lang['Version'] = 'Wersja';
$lang['Descriptión'] = 'Opis';
$lang['Error'] = 'B��d';
$lang['notpossiblethisway'] = 'Teraz Niemo�liwe';



$lang['LEFT'] = 'lewa';
$lang['RIGHT'] = 'prawa';
$lang['DATE_FORMAT'] =  'd M Y'; // Esto se debería cambiar al formato predeterminado para tu idioma, formato como php date()



$lang['Username'] = 'Benutzername';
$lang['Password'] = 'Passwort';
$lang['Email'] = 'Email';


//notes.php
{
$lang['Back'] = 'Zur&uuml;ck';


}
/*
  Corresponde a la parte de la funcion de error();
*/
$lang['ErrorPage'] = 'Página de errores';
$lang['Query'] = 'Consulta';
$lang['Queries'] = 'Consultas';
$lang['Table'] = 'Tabla';
$lang['universe0'] = 'Universum 0';

//Misc
$lang['Time'] = 'Time';//*
$lang['Download'] = 'Descargar';//*

// Created by Perberos. All rights reversed (C) 2006 
?>
