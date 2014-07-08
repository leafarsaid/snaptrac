<?php

require_once 'functions.php';
require_once 'lib/PHPExcel/Classes/PHPExcel.php';

class snaptrac{
	
#region Atributos
#region Constantes
	
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
	 * Tamanho do espaço entre as referências
	 * @var string
	 */
	public $steps_length;
	
#endregion
	
#region Resultantes
	
	/**
	 * Matriz de coordenadas da planinha de pontos
	 * @var array
	 */
	public $points;
	
	/**
	 * Matriz com todos os pontos
	 * @var array
	 */
	public $trac;
		
	/**
	 * Matriz com as referências
	 * @var array
	 */
	public $steps;
	
	/**
	 * Matriz com os pontos onde houveram avanços na velocidade acima da máxima
	 * @var array
	 */
	public $radar;
	
	/**
	 * Array com os pontos dentro dos pontos de entrada e saída
	 * @var array
	 */
	public $trechos;
			

#endregion
	
	/**
	 * Array com caminhos para arquivos dos competidores
	 * @var array
	 */
	public $arq_trac;
	
	/**
	 * Funções
	 * @var object
	 */
	public $functions;
	
#endregion

#region Construtor
	
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

#endregion	
	
#region getPoints
	
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
			foreach ($cellIterator as $cell){
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
					$array_data[$pointer]['snap'] = array();
				}
			}
		}
		
		foreach($array_data AS $key => $val){
			$key1 = strtoupper(substr($key,0,1));
			$key2 = intval(substr($key,1));
			if($key1 == "I"){
				$this->points['entradas'][$key2] = $val;
			} else{
				$this->points['saidas'][$key2] = $val;
			}
		}
	}

#endregion

#region Processo Principal
	
	public function process(){
		try{
			$this->getPoints();
			$this->tracProcess();			
		} catch(Exception $e){
			echo $e->getMessage();
		}
	}

#endregion

#region Processos Auxiliares

	#region tracProcess
	/**
	 * 
	 * Processa a trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 25/06/2014
	 */
	public function tracProcess(){
	
		$this->getFiles();
	
		foreach ($this->arq_trac AS $file){			
			
			//$this->points = array();
			//$this->trac = array();
			
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
				}
			}
			$this->pointProcess();						
			$this->zoneProcess();
			$this->radarProcess();
			$this->reportFile($file,'radar');
			$this->reportFile($file,'points');
			$this->reportFile($file,'relatorio_pontos');
			$this->radar = array();
		}
	}
	#endregion
	
	#region pointProcess
	/** Processa os pontos da planilha para achar a tangente na trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 26/06/2014
	 */
	public function pointProcess(){
	
		$arr_tipo = array('entradas','saidas');
		
		foreach($arr_tipo AS $tipo){
			$temp_arr = array();
			
			foreach ($this->points[$tipo] AS $key => $point){
				
				//pegando todos os pontos que passam perto
				foreach ($this->trac AS $ptTrac){
					$distancia = $this->functions->distancia($ptTrac,$point);
					if ($distancia <= ($this->gate/1000)){
						$this->points[$tipo][$key]['snap'][] = $ptTrac;						
					}					
				}
				
				//filtrando pontos
				if (isset($this->points[$tipo][$key]['snap'])){
					$group = $this->group($this->points[$tipo][$key]['snap'],300);
					//limpando array
					$this->points[$tipo][$key]['snap'] = array();
					
					foreach($group AS $lap){
						$this->points[$tipo][$key]['snap'][] = $this->nearest($point, $lap);
					}
				}
			}
		}
	}
	#endregion

	#region zoneProcess
	public function zoneProcess(){
		//somente entradas possuem zones
		foreach ($this->points['entradas'] AS $key => $point){
			foreach($point['snap'] AS $keySnap => $snap){
				foreach ($this->trac AS $keyTrac => $ptTrac){
					if ($ptTrac['indice'] >= $snap['indice'] && $ptTrac['indice'] <= $this->points['saidas'][$key]['snap'][$keySnap]['indice']){
						$this->points['entradas'][$key]['snap'][$keySnap]['zone'][] = $ptTrac['indice'];
					}
				}
			}
		}
		
	}
	#endregion
	
	#region radarProcess
	public function radarProcess(){
		$zones = array();
		
		foreach($this->points['entradas'] AS $key => $point){
			foreach($this->points['entradas'][$key]['snap'] AS $snap){
				if (is_array($zones) && is_array($snap['zone'])){
					$zones = array_merge($zones,$snap['zone']);			
				}
			}
		}
		
		foreach($this->trac AS $trac){
			
			if ($trac['velocidade'] > $this->velmax && in_array($trac['indice'],$zones)){
				$this->radar[] = $trac;
			}
		}
	}
	#endregion

#endregion
	
#region Métodos Úteis	
	/**  Retorna ponto mais próximo de uma lista para uma referência
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
	
	/**  Agrupa pontos dentro de um mesmo intervalo
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
	
	/** Lê pasta dos arquivos dos competidores
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 04/07/2014
	*/
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
	
	/** Gera relatórios
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 04/07/2014
	*/
	public function reportFile($file,$tipo='radar'){
		
		$folder = str_replace(".", "_", $file);
			
		$path = $this->report_path."/".$folder;
		if (!is_dir($path)){
			mkdir($path, 0777);
		}
		$path = $this->report_path."/".$folder."/$tipo.txt";
		touch($path);			
		$handle = fopen($path, "w");
		if ($handle){
			$string = "Version,212

WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0
USER GRID,0,0,0,0,0

";			
			if ($tipo=='points'){
				foreach ($this->points['entradas'] AS $key => $point){
					$string .= sprintf("w,d,%s,%s,%s,05/28/2014,00/00/00,00:00:00,0,0,151,0,13\r\n"
						,'I'.$key
						,$point['latitude']
						,$point['longitude']
					);
				}
				foreach ($this->points['saidas'] AS $key => $point){
					$string .= sprintf("w,d,%s,%s,%s,05/28/2014,00/00/00,00:00:00,0,0,151,0,13\r\n"
						,'F'.$key
						,$point['latitude']
						,$point['longitude']
					);
				}
			} elseif($tipo=='relatorio_pontos'){
				$string = "";
				$arr_tipo = array('entradas','saidas');
				foreach ($arr_tipo AS $tipo){
					if ($tipo=='entradas') $letra = 'I';
					if ($tipo=='saidas') $letra = 'F';
					foreach ($this->points[$tipo] AS $key => $point){
						$string .= sprintf("Ponto %s\r\n"
							,$letra.$key
						);
						$volta = 1;
						foreach($point['snap'] AS $snap){
							$string .= sprintf("Horário passagem %s-> %s\r\n"
								,$volta
								,$snap['hora']
							);
							$volta++;
						}
						$string .= sprintf("\r\n");
					}
				}
			} else {
				foreach ($this->$tipo AS $point){
					$string .= sprintf("w,d,%s,%s,%s,05/28/2014,00/00/00,00:00:00,0,0,48,0,13\r\n"
						,$point['velocidade']
						,$point['latitude']
						,$point['longitude']
					);
				}
			}
			
			fwrite($handle, $string);
		}
		fclose($handle);
		
	}
#endregion

}