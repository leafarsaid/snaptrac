<?php

class functions{
	
	public function toTime($secs,$fuso=0){
		$hh = $secs/360000;
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
		 
		return $hora+":"+$mm+":"+$ss;
	}
	
	public function distancia($pt1_la,$pt1_lo,$pt2_la,$pt2_lo) {
		$arco1 = 90 - $pt1_la;
		$arco2 = 90 - $pt2_la;
		$diff = abs(abs($pt2_lo)-abs($pt1_lo));
		$pre_dista = cos($this->toGrad($arco1))*cos($this->toGrad($arco2));
		$pre_distb = sin($this->toGrad($arco1))*sin($this->toGrad($arco2))*cos($this->toGrad($diff));
		$pre_dist = $pre_dista + $pre_distb;
		$pre_dist2 = $this->toRad(acos($pre_dist));
		$dist = (40030 * $pre_dist2)/360;
	
		return $dist;
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
}