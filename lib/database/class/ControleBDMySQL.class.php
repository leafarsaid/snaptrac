<?php

# ------------------------------------------------
require_once "ControleBD.class.php";
require_once "DriverBDMySQL.class.php";
# ------------------------------------------------

class ControleBDMySQL extends ControleBD {
	
	// ---------------------------
	
	private function __construct() {
		$this->obj_driverbd = new DriverBDMySQL;
	}
	
	// ---------------------------
	
	public static function getInstancia() {
		$classe = __CLASS__;
		
		if ((empty(self::$instancia)))
			return self::$instancia = new $classe;
		else
			return self::$instancia;
	}
	
	// ---------------------------
	
	public function paginacao($comando, $pagina) {
		$inicial = ($pagina - 1) * DB_PAG_LIMITE;
		$comando .= " LIMIT $inicial, " . DB_PAG_LIMITE;
		return $this->executa($comando, true);
	}
	
	// ---------------------------
}

# ------------------------------------------------

?>