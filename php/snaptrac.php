<?php

require_once 'functions.php';
require_once 'lib/PHPExcel/Classes/PHPExcel.php';

class snaptrac{
	
	/**
	 * Velocidade máxima
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
	 * Caminho para arquivos competidores
	 * @var string
	 */
	public $import_path;
	
	/**
	 * Caminho para relatórios
	 * @var string
	 */
	public $report_path;
	
	/**
	 * Array com caminhos para arquivos dos competidores
	 * @var array
	 */
	public $arq_trac;
	
	/**
	 * Matriz de coordenadas da planinha de pontos
	 * @var unknown_type
	 */
	public $points;
	
	/**
	 * Relatório do GPS
	 * @var array
	 */
	public $trac;
		
	/**
	 * Funções
	 * @var object
	 */
	public $functions;
	
	public $steps_length;
	
	public $steps;
	
	public $radar;
	
	public $trechos;
	
	/**
	 * 
	 * Método construtor
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 18/06/2014
	 * @param array $st
	 */
	public function __construct($st){
		
		$this->velmax = $st['Parametros']['velmax'];
		$this->fuso = $st['Parametros']['fuso'];
		$this->gate = floatval($st['Parametros']['gate']);
		$this->steps_length = floatval($st['Parametros']['steps_length']);
		$this->arq_pontos = $st['Parametros']['pontos'];
		$this->import_path = $st['Parametros']['import_path'];
		$this->report_path = $st['Parametros']['report_path'];
		$this->functions = new functions();
	}
	
	public function getFiles(){
		if ($handle = opendir($this->import_path)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$this->arq_trac[] = $file;
				}
			}
			closedir($handle);
		}
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
		
		$this->points = $array_data;
	}
	
	/**
	 * 
	 * Processa a trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 25/06/2014
	 */
	public function tracProcess(){
		
		try{
		
			$this->getPoints();
			$this->getFiles();
			
			foreach ($this->arq_trac AS $file){
				$folder = str_replace(".", "_", $file);
				
				//se já não existir
				if (!is_dir($this->report_path."/".$folder)){
				
					$handle = fopen($this->import_path."/".$file, "r");
					if ($handle){
			
						$previousKey = 0;
						//acumulador de distância
						$dist_acum = 0;
						
					    while (!feof($handle)){
					        $buffer = fgets($handle, 4096);
					        $arr_buffer = explode(',',$buffer);
					        if ($buffer[0]=='t'){
					        	//Pega todas as informações de cada ponto da trilha
					        	$hora = $this->functions->toSec($arr_buffer[5]);
					        	
					        	$this->trac[$hora]['indice'] = $hora;
					        	$this->trac[$hora]['latitude'] = $arr_buffer[2]*1;
					        	$this->trac[$hora]['longitude'] = $arr_buffer[3]*1;
					        	$this->trac[$hora]['data'] = $arr_buffer[4];
					        	$this->trac[$hora]['hora'] = $arr_buffer[5];   	
					        	$this->trac[$hora]['altitude'] = floatval($arr_buffer[6]);
								if ($previousKey > 0){
									$this->trac[$hora]['distancia'] = $this->functions->distancia($this->trac[$previousKey],$this->trac[$hora]);
								} else{
									$this->trac[$hora]['distancia'] = floatval(0);
								}
								$dist_acum += ($this->trac[$hora]['distancia']*1000);		
								$this->trac[$hora]['distancia_acumulada'] = $dist_acum;
								$vel = round(($this->trac[$hora]['distancia'] / (($hora-$previousKey)/3600)),2);
								$vel = ($vel > 0) ? $vel : 0;
								$this->trac[$hora]['velocidade'] = $vel;
								if ($vel > $this->velmax){									
									$this->trac[$hora]['ultrapassou_velmax'] = 'SIM';
								} else{
			
									$this->trac[$hora]['ultrapassou_velmax'] = 'NAO';
								}
								
								$arr_step = $this->trac[$hora];
								
								//steps
								if ($dist_acum >= $this->steps_length){				
									$this->steps[$hora] = $arr_step;
									$dist_acum = 0;
								}
								
								$previousKey = $hora;
					        }
					    }
					    fclose($handle);
									
						foreach ($this->points AS $key => $coord){
							foreach ($this->trac AS $key2 => $point){
								$distancia = $this->functions->distancia($point,$coord);
								if ($distancia <= ($this->gate/1000)){
									/*
									 * guardando o bolo de pontos que fica perto do ponto
									 * o método $this->pointProcess() refina depois esses pontos
									 */
									$this->points[$key]['snap'][] = $point;
								}
							}
						}
					}
					
					$this->pointProcess();
					$this->trechoProcess();
					$this->radarProcess();
					$this->reportFile($file);
				}
			}
		} catch (Exception $e){
			
		}
	}
	
	/**
	 * 
	 * Processa os pontos da planilha para achar a tangente na trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 26/06/2014
	 */
	public function pointProcess(){
		
		$temp_arr = array();
		
		foreach ($this->points AS $key => $point){			
			if (isset($point['snap'])){
				$group = $this->group($point['snap'],300);
				$this->points[$key]['snap'] = array();
				
				foreach($group AS $lap){
					$this->points[$key]['snap'][] = $this->nearest($point, $lap);
				}
			}
		}
	}
	
	public function trechoProcess(){
	
		$this->trechos = array();
		
		foreach ($this->points AS $key => $point){
			
			if(substr($key,0,1) == "I"){
				$key2 = "F".substr($key,1);
				foreach($this->trac AS $hora_trac => $trac){
					if(isset($this->points[$key]['snap'])){
					if(is_array($this->points[$key]['snap'])){
						foreach($this->points[$key]['snap'] AS $snap){
							if(isset($snap['indice']) && isset($this->points[$key2]['snap']['indice'])){
							if($hora_trac >= $snap['indice'] && $hora_trac <= $this->points[$key2]['snap']['indice']){
								$this->trechos[] = $hora_trac;
							}
							}
						}
					}
					}
				}
			}
		}
	}
	
	
	public function radarProcess(){
		foreach($this->trac AS $trac){
			if (true){
				if ($trac['velocidade'] > $this->velmax && in_array($trac['indice'],$this->trechos)){
					$this->radar[] = $trac;
				}
			}
		}
	}
	
	
	/**
	 * 
	 * Retorna ponto mais próximo de uma lista para uma referência
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 26/06/2014
	 * @param unknown_type $point Referência
	 * @param unknown_type $arrPoints Lista
	 * @return Ambigous <multitype:, unknown>
	 */
	public function nearest($point, $arrPoints){
		
		$retorno = array();
		
		$distanciaAnterior = 999999999999999999999;
		
		foreach($arrPoints AS $tracPoint){
			$distancia = $this->functions->distancia($tracPoint,$point);
			if ($distancia < $distanciaAnterior){
				$retorno = $tracPoint;
			}
			$distanciaAnterior = $distancia;
		}
		
		return $retorno;
		
	}
	
	/**
	 * 
	 * Agrupa pontos dentro de um mesmo intervalo
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 26/06/2014
	 * @param unknown_type $arrPoints
	 * @param unknown_type $interval
	 */
	public function group($arrPoints, $interval){
		$group = array();
		
		$indiceAnterior = 0;
		$j = 0;
				
		foreach($arrPoints AS $tracPoint){
			$indice = $tracPoint['indice'];
			if (($indice-$indiceAnterior) > $interval){
				$j++;
			}				
			$group[$j][] = $tracPoint;
			$indiceAnterior = $indice;
		}
		
		return $group;
	}
	
	
	public function reportFile($file){
		
		$folder = str_replace(".", "_", $file);
			
		$path = $this->report_path."/".$folder;
		if (!is_dir($path)){
			mkdir($path, 0777);
		}
		$path = $this->report_path."/".$folder."/radar.txt";
		touch($path);			
		$handle = fopen($path, "w");
		if ($handle){
			$string = "Version,212

WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0
USER GRID,0,0,0,0,0

";
			foreach ($this->radar AS $point){
				$string .= sprintf("w,d,%s,%s,%s,05/28/2014,00/00/00,00:00:00,0,0,48,0,13\r\n",$point['velocidade'],$point['latitude'],$point['longitude']);
			}
		
			fwrite($handle, $string);
		}
		fclose($handle);
		
	}
}