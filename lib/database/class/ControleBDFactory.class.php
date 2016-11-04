<?php

# ------------------------------------------------
require_once "ControleBDMySQL.class.php";
# ------------------------------------------------

class ControleBDFactory {
	
	// ---------------------------
	
	static function getControlador($driver) {
		
		switch (strtoupper($driver)) {
			case "MSSQL": return null;
			case "MYSQL": return ControleBDMySQL::getInstancia();
			case "ODBC": return null;
			case "POSTGRESQL": return null;
			case "SQLITE": return null;
			default: return null;
		}
	}
	
	// ---------------------------
}

# ------------------------------------------------

?>