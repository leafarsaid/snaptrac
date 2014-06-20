<?php

include_once 'php/snaptrac.php';

$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini",true));

$snaptrac->getPoints();
$snaptrac->retornaTrac();

echo '<pre>';
var_dump($snaptrac->trac);
echo '</pre>';

$vlt = 0;
foreach ($snaptrac->trac AS $volta){
	foreach ($volta AS $ponto){
		if ($vlt_ant != $vlt) {
			$vlt = $ponto['volta'];
			echo '<br />Volta: '.$vlt;
			echo '<br />Latitude: '.$ponto['latitude'];
			echo '<br />Longitude: '.$ponto['longitude'];
			echo '<br />';
		}		
		$vlt_ant = $ponto['volta'];
	}
}


?>