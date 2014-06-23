<?php

include_once 'php/snaptrac.php';
include_once 'php/functions.php';

$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini",true));

$snaptrac->getPoints();
$snaptrac->retornaTrac();

//echo '<pre>';
//var_dump($snaptrac->trac);
//echo '</pre>';

$vlt = 0;
/*
foreach ($snaptrac->trac AS $key => $ponto){
	echo '<br />Key: '.$key;
	echo '<br />Latitude: '.$ponto['latitude'];
	echo '<br />Longitude: '.$ponto['longitude'];
	echo '<br />Hora: '.$ponto['horaPura'];
	echo '<br />Linhas: '.$snaptrac->linhas;
	echo '<br />Dist I01: '.$ponto['distancia']['I01'];
	echo '<br />Dist I02: '.$ponto['distancia']['I02'];
	echo '<br />Dist F01: '.$ponto['distancia']['F01'];
	echo '<br />Dist F02: '.$ponto['distancia']['F02'];
	echo '<br />';
}
*/


foreach ($snaptrac->coordenadas AS $key => $coord){
	echo '<br />Coordenada: '.$key;
	echo '<br />Latitude: '.$coord['latitude'];
	echo '<br />Longitude: '.$coord['longitude'];
	echo '<br />Snap: ';
	echo '<br />Latitude: '.$coord['snap']['latitude'];
	echo '<br />Longitude: '.$coord['snap']['longitude'];
	echo '<br />distancia: '.$coord['distancia'];
	echo '<br />hora: '.$coord['snap']['hora'];
	echo '<br />hora: '.date("H:i:s", $coord['snap']['hora']); 
	echo '<br />';
}


/*
$pt1 = array(
	'latitude'	=>	-27.46146,
	'longitude'	=>	-52.11738
);

$pt2 = array(
	'latitude'	=>	-27.429861,
	'longitude'	=>	-52.11454
);

$functions = new functions();
echo $functions->distancia($pt2,$pt1);
*/

?>