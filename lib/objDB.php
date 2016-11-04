<?php
require_once "database/include/config_bd.inc.php";
require_once "database/class/ControleBDFactory.class.php";
$obj_controle=ControleBDFactory::getControlador(DB_DRIVER);
?>