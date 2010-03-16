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
  //strings.php :: Funciones variadas para dar formato al texto
//
//  Simple coloreo de numeros.
//
function colorNumber($n,$s=''){

	if($n>0){
		if($s!=''){
		$s = colorGreen($s);
		}else{
		$s = colorGreen($n);
		}
	}elseif($n<0){
		if($s!=''){
		$s = colorRed($s);
		}else{
		$s = colorRed($n);
		}
	}else{
		if($s!=''){
		$s = $s;
		}else{
		$s = $n;
		}
	}
	return $s;
}

function colorRed($n){
	return '<font color="#ff0000">'.$n.'</font>';
}

function colorGreen($n){
	return '<font color="#00ff00">'.$n.'</font>';
}

function pretty_number($n,$floor=true){
	if($floor){$n = floor($n);}
	return number_format($n,0,",",".");
	
}



// Created by Perberos. All rights reversed (C) 2006
?>