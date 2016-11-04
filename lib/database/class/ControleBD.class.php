<?php

# ------------------------------------------------

abstract class ControleBD {
	
	// ---------------------------

	protected static $instancia;
	protected $obj_driverbd;
	
	// ---------------------------
	
	abstract public static function getInstancia();
	
	// ---------------------------
	
	public function executa($comando, $unbuffered=false) {
		$metodo = $this->obj_driverbd->metodoExecuta($comando);
		
		if ($metodo == "")
			return false;
		elseif ($metodo == "runQuery") {
			if (DB_UNBUFFERED) return $this->obj_driverbd->$metodo($comando, $unbuffered);
			else return $this->obj_driverbd->$metodo($comando);
		}
		else
			return $this->obj_driverbd->$metodo($comando);
	}
	
	// ---------------------------
	
	abstract public function paginacao($comando, $pagina);
	
	// ---------------------------
	
	public function populaBanco($comando, $valor) {
		$obj_resultset = $this->executa($comando, true);
		
		while ($vet_linha = $obj_resultset->getLinha()) {
			if ($valor == $vet_linha[0])
				echo "<option value=\"{$vet_linha[0]}\" selected=\"selected\">{$vet_linha[1]}</option>\n";
			else
				echo "<option value=\"{$vet_linha[0]}\">{$vet_linha[1]}</option>\n";
		}
	}
	
	// ---------------------------
	
	public static function popula($vet_opcoes, $valor) {
		foreach ($vet_opcoes as $indice => $texto) {
			if ($valor == $indice)
				echo "<option value=\"$indice\" selected=\"selected\">$texto</option>\n";
			else
				echo "<option value=\"$indice\">$texto</option>\n";
		}
	}
	
	// ---------------------------
	
	public static function populaNum($inicio, $fim, $incremento, $valor) {
		for ($i = $inicio; ($incremento < 0) ? $i >= $fim : $i <= $fim; $i += $incremento) {
			if ($valor == $i)
				echo "<option value=\"$i\" selected=\"selected\">$i</option>\n";
			else
				echo "<option value=\"$i\">$i</option>\n";
		}
	}
	
	// ---------------------------
	
	public static function populaRadio($opcao, $valor) {
		return ($valor == $opcao) ? ' checked="checked"' : "";
	}
	
	// ---------------------------
	
	public static function aspas2HTML($string) {
		$string = str_replace('"', "&quot;", $string);
		return $string = str_replace("'", "&#39;", $string);
	}
	
	// ---------------------------
}

# ------------------------------------------------

?>