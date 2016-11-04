<?php

require_once 'functions.php';

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
	 * Caminho para arquivos processados dos competidores
	 * @var string
	 */
	public $processed_path;
	
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
	 * Matriz de coordenadas da planinha de pontos - guarda estado inicial
	 * @var array
	 */
	public $points_ini;
	
	/**
	 * Matriz de coordenadas da planinha de pontos - guarda todos
	 * @var array
	 */
	public $points_geral;
	
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
	 * Matriz com os pontos onde houveram avanços na velocidade acima da máxima - para todos os arquivos
	 * @var array
	 */
	public $radar_geral;
	
	/**
	 * Array com os pontos dentro dos pontos de entrada e saída
	 * @var array
	 */
	public $trechos;
	
	/**
	 * Array com tipos de pontos
	 * @var array
	 */
	public $arr_tipo;
	
	/**
	 * String com relátório de todos os carros
	 * @var string
	 */
	public $relatorio_geral_pontos;
	
	/**
	 * String com relátório de todos os carros para exportar para Chronosat
	 * @var string
	 */
	public $relatorio_exportar_chronosat;
			

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
	
	/** Método construtor
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
		$this->processed_path = $st['Parametros']['processed_path'];
		$this->report_path = $st['Parametros']['report_path'];
		$this->functions = new functions();
		$this->arr_tipo = array(
			'L' => 'largada',
			'F' => 'chegada',
			'W' => 'waypoints',
			'C' => 'carimbo',
			'I1' => 'inter1',
			'I2' => 'inter2',
			'I3' => 'inter3',
			'I4' => 'inter4',
			'IR' => 'entradas',
			'FR' => 'saidas'
		);		
		
		$this->relatorio_geral_pontos = '';
		$this->relatorio_exportar_chronosat = '';		
	}

#endregion	
	
#region getPoints
	
	/** Converte as coordenadas da planinha em uma matriz de pontos
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 18/06/2014
	 * @version 01/11/2016 novos tipos de pontos (em csv)
	 */
	public function getPoints(){
			
		$array_data = array();
		
		if (($handle = fopen($this->arq_pontos, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				$pointer = trim($data[0]);
				$coords = $data[1];
				$coords = str_replace('S', '-', $coords);
				$coords = str_replace('N', '+', $coords);
				$coords = str_replace('W', '-', $coords);
				$coords = str_replace('E', '+', $coords);
				$exp = explode(' ',$coords);
				
				$pto = array();
				
				$pto['latitude'] = $exp[0]*1;
				$pto['longitude'] = $exp[1]*1;
				$pto['snap'] = array();
				
				$tipo_pto = 'nada';				
				foreach ($this->arr_tipo AS $tipo_pto_key => $tipo_pto_val){
					if ($pointer === $tipo_pto_key){
						$tipo_pto = $tipo_pto_val;
					}
				}
				
				$array_data[$tipo_pto][] = $pto;
			}
			fclose($handle);
		}
		
		$this->points_ini = $this->points = $array_data;
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
	/** Processa a trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 25/06/2014
	 * @version 02/11/2014
	 */
	public function tracProcess(){
	
		$this->getFiles();
	
		foreach ($this->arq_trac AS $file){			
			
			//$this->points = array();
			//$this->trac = array();
			
			$folder = str_replace(".", "_", $file);
			
			//só processa não existir
			if (!is_dir($this->report_path."/".$folder)){
				
				$xml = simplexml_load_file($this->import_path."/".$file);
				
				$previousKey = 0;
				//acumulador de distância
				$dist_acum = 0;
				
				foreach($xml->trk->trkseg->trkpt AS $trkpt){
					//Pega todas as informações de cada ponto da trilha
					$hora = $this->functions->toSec(substr($trkpt->time,-9,8));
					
					$this->trac[$folder][$hora]['indice'] = $hora;
					$this->trac[$folder][$hora]['latitude'] = floatval($trkpt['lat']);
					$this->trac[$folder][$hora]['longitude'] = floatval($trkpt['lon']);
					$this->trac[$folder][$hora]['data'] = substr($trkpt->time,0,10);
					$this->trac[$folder][$hora]['hora'] = substr($trkpt->time,-9,8);   	
					$this->trac[$folder][$hora]['altitude'] = floatval($trkpt->ele);
					//distancia em km
					if ($previousKey > 0){
						$this->trac[$folder][$hora]['distancia'] = $this->functions->distancia($this->trac[$folder][$previousKey],$this->trac[$folder][$hora]);
					} else{
						$this->trac[$folder][$hora]['distancia'] = floatval(0);
					}
					$dist_acum += ($this->trac[$folder][$hora]['distancia']);		
					$this->trac[$folder][$hora]['distancia_acumulada'] = $dist_acum;
					$vel = round(($this->trac[$folder][$hora]['distancia'] / (($hora-$previousKey)/3600)),2);
					$vel = ($vel > 0) ? $vel : 0;
					$this->trac[$folder][$hora]['velocidade'] = $vel;
					if ($vel > $this->velmax){	
						$this->trac[$folder][$hora]['ultrapassou_velmax'] = 'SIM';
					} else{
						$this->trac[$folder][$hora]['ultrapassou_velmax'] = 'NAO';
					}
					
					$arr_step = $this->trac[$folder][$hora];
					
					//steps
					if ($dist_acum >= $this->steps_length){			
						$this->steps[$hora] = $arr_step;
						$dist_acum = 0;
					}
					
					$previousKey = $hora;
				}			
				rename($this->import_path."/".$file, $this->processed_path."/".$file);
			}
			$this->pointProcess($folder);
			$this->zoneProcess($folder);
			$this->radarProcess($folder);
			$this->reportFile($file,'exportar_chronosat_unitario');
			//$this->reportFile($file,'radar');
			//$this->reportFile($file,'points');
			//$this->reportFile($file,'relatorio_pontos');
			//$this->reportFile($file,'relatorio_radar');
			//$this->radar = array();
			//$this->points = $this->points_ini;
			
		}
		//$this->reportFile('','relatorio_geral_pontos');
		$this->reportFile('','exportar_chronosat');
	}
	#endregion
		
	/** Processa os pontos da planilha para achar a tangente na trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 26/06/2014
	 */
	private function pointProcess($folder){
		
		foreach($this->arr_tipo AS $tipo){
			
			foreach ($this->points[$tipo] AS $key => $point){
				
				//pegando todos os pontos que passam perto
				foreach ($this->trac[$folder] AS $ptTrac){
					$distancia = $this->functions->distancia($ptTrac,$point);
					if ($distancia <= ($this->gate/1000)){
						$this->points[$tipo][$key]['snap'][$folder][] = $ptTrac;						
					}					
				}
				
				//filtrando pontos
				if (isset($this->points[$tipo][$key]['snap'][$folder])){
					$laps = $this->group($this->points[$tipo][$key]['snap'][$folder],300);
					//limpando array
					$this->points[$tipo][$key]['snap'][$folder] = array();
					
					foreach($laps AS $lap){
						$ponto_mais_proximo = $this->nearest($point, $lap);	
						
						//se for waypoint, retira se for passagem por outro waypoint
						//$outro_waypoint = false;
						
						//if(!($tipo == 'waypoints' && $outro_waypoint)){
							$this->points[$tipo][$key]['snap'][$folder][] = $ponto_mais_proximo;
						//}
					}
				}
			}
		}
	}
	

	private function zoneProcess($folder){
		
		//somente entradas possuem zones
		foreach ($this->points['entradas'] AS $key => $point){
			foreach($point['snap'][$folder] AS $keySnap => $snap){
				foreach ($this->trac[$folder] AS $keyTrac => $ptTrac){
					if ($ptTrac['indice'] >= $snap['indice'] && $ptTrac['indice'] <= $this->points['saidas'][$key]['snap'][$folder][$keySnap]['indice']){
						$this->points['entradas'][$key]['snap'][$folder][$keySnap]['zone'][] = $ptTrac['indice'];
					}
				}
			}
		}
		
	}
	
	private function radarProcess($folder){
		$zones = array();
		
		foreach($this->points['entradas'] AS $key => $point){
			foreach($this->points['entradas'][$key]['snap'][$folder] AS $snap){
				if (is_array($zones) && is_array($snap['zone'])){
					$zones = array_merge($zones,$snap['zone']);	
				}
			}
		}
		
		foreach($this->trac[$folder] AS $trac){
			
			if ($trac['velocidade'] > $this->velmax && in_array($trac['indice'],$zones)){
				$this->radar[] = $trac;
			}
		}
	}
	
	/*private function radarProcess(){
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
	}*/

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
		
		$ext = "txt";
		if ($tipo=='relatorio_pontos' || $tipo=='relatorio_geral_pontos' || $tipo=='exportar_chronosat_unitario' || $tipo=='exportar_chronosat'){
			$ext = "csv";
		}
		
		$path = $this->report_path."/".$folder."/$tipo.$ext";
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
			} 
			elseif ($tipo=='relatorio_pontos'){
				$string = sprintf("Veículo;Passagem;Largada;Chegada;Tempo\r\n");
				$string_aux = '';
				$arr_linha = array();
				$arr_tipo = array('entradas','saidas');
				foreach ($arr_tipo AS $tipo){
					if ($tipo=='entradas') $letra = 'I';
					if ($tipo=='saidas') $letra = 'F';
					foreach ($this->points[$tipo] AS $key => $point){						
						$volta = 1;
						foreach($point['snap'] AS $snap){
							$arr_linha[intval($folder)][$volta][$tipo] = $snap['hora'];
							$volta++;
						}
					}
				}
				date_default_timezone_set("Brazil/East");
				foreach($arr_linha AS $veiculo => $voltas){
					foreach($voltas AS $num_volta => $volta){
						$string_aux .= sprintf("%s;%s;%s;%s;%s\r\n"
							,$veiculo
							,$num_volta
							,$volta['entradas']
							,$volta['saidas']
							,gmstrftime('%H:%M:%S',(strtotime($volta['saidas']))-(strtotime($volta['entradas'])))
						);
					}
				}
				
				$string .= $string_aux;
				$this->relatorio_geral_pontos .= $string_aux;
				
				
			} 
			elseif ($tipo=='relatorio_radar'){
				
				$maiorVelocidade = 0;
				foreach($this->radar AS $trac){	
					if ($maiorVelocidade < $trac['velocidade']){
						$maiorVelocidade = floatval($trac['velocidade']);
					}
				}
				
				$string = "Quantidade de pontos acima da velocidade máxima dentro das zonas de radar: ".count($this->radar);
				$string .= sprintf("\r\n");
				$string .= "Maior velocidade encontrada (km/h): ".$maiorVelocidade;
				$string .= sprintf("\r\n");
				
				
				
			} 
			elseif ($tipo=='relatorio_geral_pontos'){	
				$string = sprintf("Veículo;Passagem;Largada;Chegada;Tempo\r\n");			
				$string .= $this->relatorio_geral_pontos;
			
			} 
			elseif ($tipo=='exportar_chronosat_unitario'){
				$string = sprintf("Veículo;Tipo de tempo;Tempo;Obs\r\n");
				$string_aux = "";
				$arr_linha = array();
				foreach ($this->arr_tipo AS $tipo_key => $tipo){
					foreach ($this->points[$tipo] AS $key => $point){						
						$volta = 1;
						foreach($point['snap'] AS $key_snap => $snap){
							if ($folder == $key_snap){
								$arr_linha[intval($folder)][$volta][$tipo_key][$key] = $snap[$volta-1];
								$volta++;
							}
						}
					}
				}
				foreach($arr_linha AS $veiculo => $voltas){
					foreach($voltas AS $num_volta => $volta){
						foreach($volta AS $tipo_key => $ocorrencia){
							foreach($ocorrencia AS $oco_key => $oco_val){
								$string_aux .= sprintf("%s;%s;%s;%s\r\n"
									,$veiculo
									,$tipo_key
									,$oco_val['hora']
									,"Passagem ".$num_volta." em ".$this->arr_tipo[$tipo_key]." (".($oco_key+1).") a ".$oco_val['velocidade']."km/h"
								);
							}
						}
					}
				}
				foreach($this->radar AS $keyRadar => $radar){
					$string_aux .= sprintf("%s;%s;%s;%s\r\n"
						,intval($folder)
						,'P'
						,$radar['hora']
						,"Ocorrência de excesso de velocidade (".$radar['velocidade']."km/h) em ZVC."
					);
				}
				
				$string .= $string_aux;
				$this->relatorio_exportar_chronosat .= $string_aux;
						
			} 
			elseif ($tipo=='exportar_chronosat'){
				$string = sprintf("Veículo;Tipo de tempo;Tempo;Obs\r\n");
				$string .= $this->relatorio_exportar_chronosat;
				
			}
			else {
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