<?php

# ------------------------------------------------

abstract class ResultsetBD {
	
	// ---------------------------
	
	protected $result;
	
	// ---------------------------
	
	abstract public function getNumLinhas();
	abstract public function getValor();
	abstract public function getLinha($tipo="");
	abstract public function getAll($tipo="");
	
	// ---------------------------
}

# ------------------------------------------------

?>