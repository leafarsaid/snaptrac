<?php

include_once 'php/snaptrac.php';
include_once 'php/functions.php';

$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini",true));

$snaptrac->tracProcess();

//echo '<pre>';
//var_dump($snaptrac->$_GET['tipo']);

/* 
$pt1['latitude'] = floatval('-27.1');
$pt1['longitude'] = floatval('-57.1');
$pt1['distancia'] = floatval('0.005645645');

$pt2['latitude'] = floatval('-27.2');
$pt2['longitude'] = floatval('-57.2');

$pt3['latitude'] = floatval('-27.3');
$pt3['longitude'] = floatval('-57.3');

$pt['latitude'] = floatval('-27.22');
$pt['longitude'] = floatval('-57.22');

$arrpts = array($pt1,$pt2,$pt3);

$arrpts_group = $snaptrac->group($arrpts, 300);

echo '<pre>';
var_dump($arrpts_group);
var_dump($snaptrac->nearest($pt, $arrpts));
 */
?>