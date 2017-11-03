<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

ini_set('memory_limit', '128000M');
set_time_limit (0);

include_once 'php/snaptrac.php';
include_once 'php/functions.php';
//require_once 'lib/objDB.php';

$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini",true));
$snaptrac->process();

/*
$pto1['latitude'] = floatval('-22.715141969869308'); 
$pto1['longitude'] = floatval('-48.570636010808904');
$pto2['latitude'] = floatval('-22.713122952565595');
$pto2['longitude'] = floatval('-48.57213796451693');

echo $snaptrac->functions->distancia($pto1,$pto2) * 1000;
*/

//echo '<pre>';

//$snaptrac->getPoints();
//$snaptrac->tracProcess();
//var_dump($snaptrac->radar);
//var_dump($snaptrac->trac);
//krumo($snaptrac->points);
//var_dump($snaptrac->points);
//var_dump($snaptrac->radar);
//sleep(5000);

//$snaptrac->tracProcess();
//var_dump($snaptrac->trac);

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