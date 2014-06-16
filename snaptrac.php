<?php

include_once 'php/snaptrac.php';

$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini",true));

$pontos = $snaptrac->getPoints();

echo '<pre>';
var_dump($pontos);
echo '</pre>';
?>