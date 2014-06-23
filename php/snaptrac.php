<?php

require_once 'functions.php';
require_once 'lib/PHPExcel/Classes/PHPExcel.php';

class snaptrac{
	
	/**
	 * Velocidade m�xima
	 * @var integer
	 */
	public $velmax;
	
	/**
	 * Fuso
	 * @var integer
	 */
	public $fuso;
	
	/**
	 * Gate
	 * @var integer
	 */
	public $gate;
	
	/**
	 * Caminho para arquivo da planilha de pontos
	 * @var string
	 */
	public $arq_pontos;
	
	/**
	 * Caminho para arquivo do relat�rio GPS
	 * @var string
	 */
	public $arq_trac;
	
	/**
	 * Matriz de coordenadas da planinha de pontos
	 * @var unknown_type
	 */
	public $coordenadas;
	
	/**
	 * Relat�rio do GPS
	 * @var array
	 */
	public $trac;
		
	/**
	 * Fun��es
	 * @var object
	 */
	public $functions;
	
	public $distancia;
	public $distanciaAnterior;
	public $linhas;
	
	/**
	 * 
	 * M�todo construtor
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 18/06/2014
	 * @param array $st
	 */
	public function __construct($st){
		
		$this->velmax = $st['Parametros']['velmax'];
		$this->fuso = $st['Parametros']['fuso'];
		$this->gate = $st['Parametros']['gate'];
		$this->arq_pontos = $st['Parametros']['pontos'];
		$this->arq_trac = 'Imports/arquivo_teste.txt';
		$this->functions = new functions();
	}

	/**
	 * 
	 * Converte as coordenadas da planinha em uma matriz de pontos
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 18/06/2014
	 */
	public function getPoints(){
				
		$objReader = new PHPExcel_Reader_Excel5();
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($this->arq_pontos);

		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
		
		$array_data = array();
		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			
			$pointer = '';			 
			foreach ($cellIterator as $cell) {
				if('A' == $cell->getColumn()){
					$pointer = $cell->getCalculatedValue();
					$array_data[$pointer] = '';
				} else if('B' == $cell->getColumn()){
					$coords = $cell->getCalculatedValue();
					$coords = str_replace('S', '-', $coords);
					$coords = str_replace('N', '+', $coords);
					$coords = str_replace('W', '-', $coords);
					$coords = str_replace('E', '+', $coords);
					$exp = explode(' ',$coords);
					$array_data[$pointer]['latitude'] = $exp[0]*1;
					$array_data[$pointer]['longitude'] = $exp[1]*1;
					$array_data[$pointer]['id'] = $pointer;
				}
			}
		}
		
		$this->coordenadas = $array_data;
	}
	
	public function retornaTrac(){
		
		$temp_trac = array();
		
		$handle = fopen($this->arq_trac, "r");
		if ($handle){
			$this->linhas = 0;
		    while (!feof($handle)){
		        $buffer = fgets($handle, 4096);
		        $arr_buffer = explode(',',$buffer);
		        if ($buffer[0]=='t'){
		        	$hora = $this->functions->toSec($arr_buffer[5]);
		        	
		        	$arr_trac['latitude'] = $arr_buffer[2]*1;
		        	$arr_trac['longitude'] = $arr_buffer[3]*1;
		        	$arr_trac['data'] = $arr_buffer[4];
		        	$arr_trac['hora'] = $hora;
		        	$arr_trac['horaPura'] = $arr_buffer[5];
		        	//foreach ($this->coordenadas AS $key => $coord){
					//	$arr_trac['distancia'][$key] = $this->functions->distancia($arr_trac,$coord);
					//}
		        	$arr_trac['altitude'] = floatval($arr_buffer[6]);
					$this->trac[$hora] = $arr_trac; 
					$this->linhas++;
		        }
		    }
		    fclose($handle);			
						
			foreach ($this->coordenadas AS $key => $coord){
				$menorDistancia = 99999999999999999999;
				foreach ($this->trac AS $key2 => $point){
					$distancia = $this->functions->distancia($point,$coord);
					if ($distancia <= $menorDistancia){
						$this->coordenadas[$key]['snap'] = $point;
						$this->coordenadas[$key]['distancia'] = $distancia;
						$this->coordenadas[$key]['hora'] = $point['hora'];
						$menorDistancia = $distancia;
					}
				}
			}
		}
	}
	
	public function retornaVolta($trac,$num_ponto){
		$distancias = array();
		$horarios = array();
	
		for ($i=0;$i<count($trac);$i++) {
			$dist_array = array();
			$dist_array = $trac[$i][5];
			array_push($distancias,$dist_array[$num_ponto]);
			array_push($horarios,$trac[$i][2]);
		}
		$volta = array();
		$temp1 = 99999999;
		$temp2 = 0;
		for ($i=0;$i<count($horarios);$i++) {
			//se a atual linha estiver no gate
			if (!is_nan($distancias[$i]) && $distancias[$i]!=99999999) {
				if ($distancias[$i]<$temp1) {
					$temp1=$distancias[$i];
					$temp2=$horarios[$i];
				}
				//se a proxima linha N�O estiver no gate
				if (is_nan($distancias[$i+1]) || $distancias[$i+1]==99999999) {
					array_push($volta,$temp2);
					if ($num_ponto%2 == 0){
						array_push($radarEntrada,$temp2);
					}
					else{
						array_push($radarSaida,$temp2);
					}
					$temp1 = 99999999;
				}
			}
		}
		
		return $volta;
	}
	
	public function geraRel($content,$pt_nome){
		$trac = array();
		$trac = retornaTrac($content);
	
		$this->functions->SortIt($trac,2);
		$dados_txt = "";
	
		for ($k=0;$k<count($pt_nome);$k++) {
			$dados = array();
			$dados = retornaVolta($trac,$k);
	
			$dados_txt .= "Relat�rio do ponto:";
			$dados_txt .= $pt_nome[$k];
			$dados_txt .= " N�mero de passagens:";
			$dados_txt .= count($dados);
			$dados_txt .= "<br />";
			for ($i=0;$i<count($dados);$i++) {
				$dados_txt .= $this->functions->toTime($dados[$i])."<br />";
			}
			$dados_txt .= "<br /><br />";
		}
	
		return $dados_txt;		
	}
	
	public function geraRadares($content){
		
		$radarEntrada = array();
		$radarSaida = array();
		$trac = array();
		$trac = $this->retornaTrac($content);
		$this->functions->SortIt($trac,2);
		for ($k=0;$k<count($trac);$k++) {
			$dados = array();
			$dados = $this->retornaVolta($trac,$k);
		}
		
		$dados_txt = "";
	
		for ($k=0;$k<count($radarEntrada);$k++) {
			$num_radares = 0;
			$num_excessos = 0;
			$maxima = 0;
	
			for ($i=0;$i<count($trac);$i++) {
				$vel = $trac[$i][4]*1;
				$hora = $trac[$i][2]*1;
				if ($hora>=$radarEntrada[$k] && $hora<=$radarSaida[$k]) {
					$num_radares++;
					if ($vel>$this->velmax) $num_excessos++;
					if ($vel>$maxima) $maxima = $vel;
				}
			}
			
			$dados_txt .= "<b>Trecho com Radar ";
			$dados_txt .= eval($k+1)+":</b><br /><br />Entrada:<b>";
			$dados_txt .= $this->functions->toTime($radarEntrada[$k])+"</b><br />Sa�da:<b>";
			$dados_txt .= $this->functions->toTime($radarSaida[$k])+"</b><br />Radares:<b>";
			$dados_txt .= $num_radares+"</b><br />Excessos:<b>";
			$dados_txt .= $num_excessos+"</b><br />M�xima:<b>";
			$dados_txt .= round($maxima,2)+"</b><br /><br /><br />";
		}
		
		return $dados_txt;
	}
		
	public function geraRadares2($content){
		
		$radarEntrada = array();
		$radarSaida = array();
		$trac = array();
		$trac = retornaTrac($content);
		$this->functions->SortIt($trac,2);	
		for ($k=0;$k<count($trac);$k++){
			$dados = array();
			$dados = $this->retornaVolta($trac,$k);
		}		
		
		$tadentro = false;	
		$dados_txt = "Version,212<br /><br />WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0<br />USER GRID,0,0,0,0,0<br /><br />";
		for ($i=0;$i<count($trac);$i++){
			if ($trac[$i][4]>=0){
				if ($trac[$i][4]>=$this->velmax){
					$tadentro = false;
					for ($k=0;$k<count($radarEntrada);$k++){
						if ($trac[$i][2]>=$radarEntrada[$k] && $trac[$i][2]<=$radarSaida[$k]){
							$tadentro = true;
						}
					}
					if ($tadentro){
						$dados_txt .= "w,dms,";
						$dados_txt .= round($trac[$i][4],2);
						$dados_txt .= ",";
						$dados_txt .= $trac[$i][0].",".$trac[$i][1].",".$trac[$i][3]." ".$this->functions->toTimeGreenwitch($trac[$i][2]).",".$trac[$i][5].",2,133,0,13<br />";
					}
				}
			}
		}
		$dados_txt += "<br />";
		for ($i=0;$i<count($trac);$i++) {
			if (!is_nan($trac[$i][0]) && !is_nan($trac[$i][2])) {
				$tadentro = false;
				for ($k=0;$k<count($radarEntrada);$k++) {
					if ($trac[$i][2]>=$radarEntrada[$k] && $trac[$i][2]<=$radarSaida[$k]){
						$tadentro = true;
					}
				}
				if ($trac[$i][4]>=$this->velmax && ($tadentro == true)){
					$dados_txt += "<font color=red>";
				}
				$dados_txt .= "t,dms,";
				$dados_txt .= $trac[$i][0].",".$trac[$i][1].",".$trac[$i][3].",".$this->functions->toTime($trac[$i][2],$this->fuso).",".$trac[$i][5];
				if ($i==0){
					$dados_txt .= ",1<br />";
				}
				else{
					$dados_txt .= ",0<br />";
				}
				if ($trac[$i][4] >= $this->velmax && ($tadentro == true)){
					$dados_txt .= "</font>";
				}
			}
		}			
		
		return $dados_txt;
	}
	
	public function geraRadaresTot($content){
		
		$tracla = array();
		$traclo = array();
		$trachor = array();
		$tracvel = array();
		$trac = array();
		
		$quebra = explode('\n',$content);
		//alert("\n--0-->"+quebra[0]+"\n--1-->"+quebra[1]+"\n--2-->"+quebra[2]+"\n--3-->"+quebra[3]+"\n--4-->"+quebra[4]+"\n--5-->"+quebra[5]+"\n--6-->"+quebra[6]);	
		
		for ($i=5;$i<count($quebra);$i++) {
			$quebra2 = explode(',',$quebra[$i]);	
			$lat = $quebra2[2];
			$lon = $quebra2[3];
			
			if ($lat[6]=="'") {
				$lat2 = ($lat[0]+(($lat[1]+$lat[2]) + ($lat[4]+$lat[5])/60 + ($lat[8]+$lat[9]+$lat[10]+$lat[11]+$lat[12]+$lat[13]+$lat[14]+$lat[15])/3600));
				$lon2 = ($lon[0]+(($lon[2]+$lon[3]) + ($lon[5]+$lon[6])/60 + ($lon[9]+$lon[10]+$lon[11]+$lon[12]+$lon[13]+$lon[14]+$lon[15]+$lon[16])/3600));
				$lat2 = round($lat2,6);
				$lon2 = round($lon2,6);
			} else {
				$lat2 = $lat;
				$lon2 = $lon;
			}
			array_push($tracla,$lat2);
			array_push($traclo,$lon2);
			array_push($trachor,toSec($quebra2[5]));
			$vel = $this->functions->distancia($tracla[$i-1],$traclo[$i-1],$tracla[$i],$traclo[$i])/($trachor[$i]-$trachor[$i-1]);
		}
		for ($i=0;$i<count($tracla);$i++) {
			$trac[$i] = array();
			$trac[$i][0]=$tracla[$i];
			$trac[$i][1]=$traclo[$i];
			$trac[$i][2]=$trachor[$i];
			$trac[$i][3]=$this->functions->distancia($tracla[$i-1],$traclo[$i-1],$tracla[$i],$traclo[$i])/(($trachor[$i]-$trachor[$i-1])/3600);
			
		}
			
		$dados_txt = "";
		$num_radares = 0;
		$num_excessos = 0;
		$maxima = 0;
		$entrada = 0;
		$maxhora = 0;
		for ($i=0;$i<count($trac);$i++) {
			$vel = $trac[$i][3];
			$hora = $trac[$i][2];
			if ($hora>$maxhora) {
				$maxhora = $hora;
			}
			if ($hora>0) {
				$num_radares++;
				if ($entrada==0){
					$entrada = $this->functions->toTime($hora);
				}
			}
			if ($vel>$velmax) {
				$num_excessos++; 
			}
			if ($vel>$maxima) $maxima = $vel;	
		}	
		
		$dados_txt = "Entrada:<b>".$entrada."</b><br />Sa�da:<b>".$this->functions->toTime($maxhora)."</b><br />Radares:<b>".$num_radares."</b><br />Excessos:<b>".$num_excessos."</b><br />M�xima:<b>".round($maxima,2)."</b>";
			
		return $dados_txt;
	}

	public function geraRadaresTot2($content){
		
		$radarEntrada = array();
		$radarSaida = array();
		$trac = array();
		$trac = $this->retornaTrac($content);
		$this->functions->SortIt($trac,2);	
		for ($k=0;$k<count($trac);$k++) {
			$dados = array();
			$dados = $this->retornaVolta($trac,$k);
		}	
		
		$tadentro = false;	
		$dados_txt = "Version,212<br /><br />WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0<br />USER GRID,0,0,0,0,0<br /><br />";
		for ($i=0;$i<count($trac);$i++) {
			if ($trac[$i][4]>=0) {
				if ($trac[$i][4]>=$this->velmax) {
					$dados_txt .= "w,dms,";
					$dados_txt .= $trac[$i][4].toFixed(2);
					$dados_txt .= ",";
					$dados_txt .= $trac[$i][0].",".$trac[$i][1].",".$trac[$i][3]." ".$this->functions->toTime($trac[$i][2],$this->fuso).",".$trac[$i][5].",2,133,0,13<br />";
				}
			}
		}
		$dados_txt .= "<br />";
		for ($i=0;$i<count($this->$trac);$i++) {
			if (!is_nan($trac[$i][0]) && !is_nan($trac[$i][2])) {
				if ($trac[$i][4]>=$this->velmax){
					$dados_txt .= "<font color=red>";
				}
				$dados_txt .= "t,dms,";
				$dados_txt .= $trac[$i][0].",".$trac[$i][1].",".$trac[$i][3].",".$this->functions->toTime($trac[$i][2],$this->fuso).",".$trac[$i][5];
				if ($i==0){
					$dados_txt .= ",1<br />";
				}
				else{
					$dados_txt .= ",0<br />";
				}
				if ($trac[$i][4]>=$this->velmax){
					$dados_txt .= "</font>";
				}
			}
		}
		
		return $dados_txt;
	}

}