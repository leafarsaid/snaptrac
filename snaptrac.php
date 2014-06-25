<?php

include_once 'php/snaptrac.php';
include_once 'php/functions.php';

$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini",true));

$snaptrac->tracProcess();

//echo '<pre>';
//var_dump($snaptrac->trac);
//echo '</pre>';

$vlt = 0;

echo '<pre>';
var_dump($snaptrac->$_GET['tipo']);

?>