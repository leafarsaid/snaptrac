<?php

# ------------------------------------------------
require_once "DriverBD.class.php";
require_once "ResultsetBDMySQL.class.php";
# ------------------------------------------------

class DriverBDMySQL extends DriverBD {
	
	// ---------------------------
	
	public function __construct() {
		@mysql_connect(DB_HOST, DB_USER, DB_PASS);
		@mysql_select_db(DB_BANCO);
	}
	
	// ---------------------------
	
	public function runTransaction(array $vet_comando) {
		@mysql_query("BEGIN");
		
		foreach ($vet_comando as $comando) {
			
			if (! @mysql_query($comando)) {
				@mysql_query("ROLLBACK");
				return false;
			}
		}
		
		@mysql_query("COMMIT");
		return true;
	}
	
	// ---------------------------
	
	public function runDML($comando) {
		return @mysql_query($comando);
	}
	
	// ---------------------------
	
	public function runQuery($comando, $unbuffered=false) {
		$funcao = "mysql_" . ($unbuffered ? "unbuffered_" : "") . "query";
		
		if ($result = @$funcao($comando))
			return new ResultsetBDMySQL($result);
		else
			return false;
	}
	
	// ---------------------------
}

# ------------------------------------------------

?>