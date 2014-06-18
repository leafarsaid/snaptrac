<?php

include_once 'php/snaptrac.php';

$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini",true));

$snaptrac->getPoints();
$snaptrac->retornaTrac();

echo '<pre>';
var_dump($snaptrac->trac);
echo '</pre>';
?>