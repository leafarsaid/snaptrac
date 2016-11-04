<?php
# ------------------------------------------------ 
// BANCO DE DADOS
define("DB_DRIVER", "mysql"); 
$sUserBanco = "";
$sServer = ""; 
if ($_REQUEST["db"] == 2) {       
	$sUserBanco = "chronosat2";  
	$sBanco = "chronosat2";
	$sServer = "mysql03.chronosat.com.br";
	$sPass = "chrono2002";
} elseif ($_REQUEST["db"] == 3){       
	$sUserBanco = "chronosat3";
	$sBanco = "chronosat3";
	$sServer = "mysql04.chronosat.com.br";
	$sPass = "chrono2002";
}  elseif ($_REQUEST["db"] == "local1"){       
	$sUserBanco = "root"; 
	$sBanco = "chronosat1";
	$sServer = "localhost";
	$sPass = "";
}  elseif ($_REQUEST["db"] == "local2"){       
	$sUserBanco = "root"; 
	$sBanco = "chronosat2";
	$sServer = "localhost";
	$sPass = "";
}  elseif ($_REQUEST["db"] == "local3"){       
	$sUserBanco = "root"; 
	$sBanco = "chronosat3";
	$sServer = "localhost";
	$sPass = "";
} 
else {       
	$sUserBanco = "chronosat1";
	$sBanco = "chronosat1";
	$sServer = "mysql02.chronosat.com.br";
	$sPass = "chrono2002";
}

define("DB_HOST", $sServer);
define("DB_USER", $sUserBanco);
define("DB_PASS", $sPass);
define("DB_BANCO", $sBanco); 
define("DB_DML", "^(INSERT|UPDATE|DELETE)"); 

// comandos DML permitidos
define("DB_QUERY", "^(\(?SELECT|CALL|SHOW)"); 
// queries permitidas
define("DB_UNBUFFERED", true); 
// suporta resultsets "unbuffereds"?
define("DB_PAG_LIMITE", 10); 
// resultados por pсgina (paginaчуo de resultados) 
# ------------------------------------------------ 
?>