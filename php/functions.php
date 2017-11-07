<?php

class functions{
	
	public function toTime($secs,$fuso=0){
		/*$hh = $secs/360000;
		if($hh < 1){
			$hh = '00';
		}
		else if($hh < 10){
			$hh = '0' + $hh;
		}
		$tmp = (($secs/360000) - $hh)*3600;
		 
		$mm = round($tmp/60);
		if($mm < 1){
			$mm = '00';
		}
		else if($mm < 10){
			$mm = '0' + $mm;
		}
		$tmp = round($secs);
		 
		$ss = substr($tmp,5,2);
		$hora = $hh + $fuso;
		 
		return $hora+":"+$mm+":"+$ss;*/
		
	}
	
	public function distancia($pt1,$pt2) {	
	
		$arco1 = 90 - $pt1['latitude'];
		$arco2 = 90 - $pt2['latitude'];
		$diff = abs(abs($pt2['longitude'])-abs($pt1['longitude']));
		$pre_dista = cos($this->toGrad($arco1))*cos($this->toGrad($arco2));
		$pre_distb = sin($this->toGrad($arco1))*sin($this->toGrad($arco2))*cos($this->toGrad($diff));
		$pre_dist = $pre_dista + $pre_distb;
		$pre_dist2 = $this->toRad(acos($pre_dist));
		$dist = (40030 * $pre_dist2)/360;
	
		return floatval($dist);
	}
	
	public function toGrad($num) {
		$grads = $num * (M_PI / 180);
		return $grads;
	}
	
	public function toRad($num) {
		$rads = $num * (180 / M_PI);
		return $rads;
	}
	
	public function SortIt($arr,$col){		
		return $arr;
	}

	public function toSec($num) {
		$arrsec = explode(":",$num);
		$tosec = ($arrsec[0]*3600+$arrsec[1]*60+$arrsec[2]);
		
		return $tosec;
	}

	public function numeral($folder){
		return intval(substr($folder, 0, 4));
	}

	public function strReport($type, $folder, $desc, $ss, $tempo_valor='', $velocidade='', $tempo=''){
		$report = "";
		$tipos = array('L'=>'largada', 'C'=>'chegada', 'I1'=>'inter1', 'I2'=>'inter2', 'I3'=>'inter3', 'I4'=>'inter4');

		foreach($tipos AS $keytipo => $tipo){
			if ($type == $tipo.'_ss'){
				$report = "SS$ss - Competidor $folder passou às $tempo_valor pelo ponto de $tipo $desc a uma velocidade de $velocidade km/h.\r\n";
			}

			if ($type == $tipo.'_ss_sql'){
				$report = "INSERT INTO t01_tempos (c01_valor, c01_tipo, c01_status, c03_codigo, c02_codigo, c01_obs, c01_sigla) VALUES (TIME_TO_SEC('$tempo_valor'), '$keytipo', getTempoStatus($folder, $ss, '$keytipo'), $folder, $ss, 'Passagem pelo ponto de $tipo $desc','SNAPTRAC');\r\n";
			}
		}

		if ($type == 'perda_wp_ss'){
			$report = "* Competidor $folder não passou pelo waypoint $desc na especial $ss.\r\n";
		}

		if ($type == 'perda_wp_ss_sql'){
			$report = "INSERT INTO t01_tempos (c01_valor, c01_tipo, c01_status, c03_codigo, c02_codigo, c01_obs, c01_sigla) VALUES (TIME_TO_SEC('$tempo_valor'), 'P', getTempoStatus($folder, $ss, 'P'), $folder, $ss, 'Perda do waypoint $desc','SNAPTRAC');\r\n";
		}

		if ($type == 'perda_cb_ss'){
			$report = "* Competidor $folder não passou pelo carimbo $desc na especial $ss.\r\n";
		}

		if ($type == 'perda_cb_ss_sql'){
			$report = "INSERT INTO t01_tempos (c01_valor, c01_tipo, c01_status, c03_codigo, c02_codigo, c01_obs, c01_sigla) VALUES (TIME_TO_SEC('$tempo_valor'), 'P', getTempoStatus($folder, $ss, 'P'), $folder, $ss, 'Perda do carimbo $desc','SNAPTRAC');\r\n";
		}

		if ($type == 'penalidade_por_radar'){
			$report = "* Competidor $folder ultrapassou a velocidade máxima na ZVC $desc na especial $ss. Velocidade de $velocidade km/h.\r\n";
		}

		if ($type == 'penalidade_por_radar_sql'){
			$report = "INSERT INTO t01_tempos (c01_valor, c01_tipo, c01_status, c03_codigo, c02_codigo, c01_obs, c01_sigla) VALUES (TIME_TO_SEC('$tempo_valor'), 'P', getTempoStatus($folder, $ss, 'P'), $folder, $ss, 'Radar na ZVC $desc','SNAPTRAC');\r\n";
		}

		if ($type == 'penalidade_por_tempo'){
			$report = "* Competidor $folder fez um tempo muito curto na ZVC $desc na especial $ss. O tempo foi de $tempo.\r\n";
		}

		if ($type == 'penalidade_por_tempo_sql'){
			$report = "INSERT INTO t01_tempos (c01_valor, c01_tipo, c01_status, c03_codigo, c02_codigo, c01_obs, c01_sigla) VALUES (TIME_TO_SEC('$tempo_valor'), 'P', getTempoStatus($folder, $ss, 'P'), $folder, $ss, 'Tempo curto na ZVC $desc','SNAPTRAC');\r\n";
		}


		return $report;
	}
}