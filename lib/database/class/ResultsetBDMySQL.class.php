<?php

# ------------------------------------------------
require_once "ResultsetBD.class.php";
# ------------------------------------------------

class ResultsetBDMySQL extends ResultsetBD {
	
	// ---------------------------
	
	public function __construct($result) {
		$this->result = $result;
	}
	
	// ---------------------------
	
	public function getNumLinhas() {
		return @mysql_num_rows($this->result);
	}
	
	// ---------------------------
	
	public function getValor() {
		$vet_linha = @mysql_fetch_row($this->result);
		@mysql_free_result($this->result);
		return $vet_linha[0];
	}
	
	// ---------------------------
	
	public function getLinha($tipo="") {
		$funcao = "mysql_fetch_" . ((@eregi("^ASSOC$", $tipo)) ? "assoc" : "row");
		
		if (! $vet_linha = $funcao($this->result))
			@mysql_free_result($this->result);
		
		return $vet_linha;
	}
	
	// ---------------------------
	
	public function getAll($tipo="") {
		$funcao = "mysql_fetch_" . ((@eregi("^ASSOC$", $tipo)) ? "assoc" : "row");
		
		while ($vet_linha = $funcao($this->result))
			$vet_dados[] = $vet_linha;
		
		@mysql_free_result($this->result);
		return $vet_dados;
	}
	
	// ---------------------------
}

?>