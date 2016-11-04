<?php

# ------------------------------------------------

abstract class DriverBD {
	
	// ---------------------------
	
	public function metodoExecuta($comando) {
		$metodo = "";
		$var_comando = @gettype($comando);
		
		if ($var_comando == "string") {
			
			if (@eregi(DB_DML, $comando))
				$metodo = "runDML";
			elseif (@eregi(DB_QUERY, $comando))
				$metodo = "runQuery";
			
		} elseif ($var_comando == "array") {
			$metodo = "runTransaction";
			
			foreach ($comando as $sql) {
				
				if (! @eregi(DB_DML, $sql)) {
					$metodo = "";
					break;
				}
			}
		}
		
		return $metodo;
	}
	
	// ---------------------------
	
	abstract public function runTransaction(array $vet_comando);
	abstract public function runDML($comando);
	abstract public function runQuery($comando);
	
	// ---------------------------
}

# ------------------------------------------------

?>