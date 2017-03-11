<?php

require_once 'functions.php';

class snaptrac{


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

	public $current_ss;
	public $lost_wp_penalty;
	public $lost_stamp_penalty;
	public $maxspeed_occ_penalty;
	public $laps_ss;
	public $stamp_vel;

	public $radar1;
	public $radar2;
	public $radar1_penalty;
	public $radar2_penalty;
	public $radar3_penalty;
	public $sec_continuous;

	public $zvc1_mintime_x2;
	public $zvc1_mintime_x3;
	public $zvc1_maxspeed;

	public $zvc2_mintime_x2;
	public $zvc2_mintime_x3;
	public $zvc2_maxspeed;

	public $zvc3_mintime_x2;
	public $zvc3_mintime_x3;
	public $zvc3_maxspeed;

	public $zvc4_mintime_x2;
	public $zvc4_mintime_x3;
	public $zvc4_maxspeed;
	
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
	public $relatorio_exportar_chronosat;	
	

	#endregion

#region Atributos acessórios
	
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

#region Métodos
	
	/** Método construtor
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 18/06/2014
	 * @param array $st
	 */
	public function __construct($st){
	
		$this->velmax = $st['Parametros']['velmax'];
		$this->fuso = $st['Parametros']['fuso'];
		$this->fuso = (substr($this->fuso,0,1)=='-') ? strtotime(substr($this->fuso,1))*-1 : strtotime(substr($this->fuso,1));
		$this->gate = floatval($st['Parametros']['gate']);
		$this->steps_length = floatval($st['Parametros']['steps_length']);
		$this->arq_pontos = $st['Parametros']['pontos'];
		$this->import_path = $st['Parametros']['import_path'];
		$this->processed_path = $st['Parametros']['processed_path'];
		$this->report_path = $st['Parametros']['report_path'];
		$this->functions = new functions();
		$this->arr_tipo = array(
			'LT' => 'largada',
			'CT' => 'chegada',
			'W' => 'waypoints',
			'CB' => 'carimbo',
			'I1' => 'inter1',
			'I2' => 'inter2',
			'I3' => 'inter3',
			'I4' => 'inter4',
			'IR' => 'entradas',
			'FR' => 'saidas'
		);
		
		$this->relatorio_geral_pontos = '';
		$this->relatorio_exportar_chronosat = '';		
		$this->current_ss = $st['Parametros']['current_ss'];
		$this->lost_wp_penalty = $st['Parametros']['lost_wp_penalty'];
		$this->lost_stamp_penalty = $st['Parametros']['lost_stamp_penalty'];
		$this->maxspeed_occ_penalty = $st['Parametros']['maxspeed_occ_penalty'];
		$this->laps_ss = $st['Parametros']['laps_ss'];
		$this->stamp_vel = $st['Parametros']['stamp_vel'];

		$this->radar1 = $st['Parametros']['radar1'];
		$this->radar2 = $st['Parametros']['radar2'];
		$this->radar1_penalty = $st['Parametros']['radar1_penalty'];
		$this->radar2_penalty = $st['Parametros']['radar2_penalty'];
		$this->radar3_penalty = $st['Parametros']['radar3_penalty'];
		$this->sec_continuous = $st['Parametros']['sec_continuous'];

		$this->zvc1_mintime_x2 = $st['Parametros']['zvc1_mintime_x2'];
		$this->zvc1_mintime_x3 = $st['Parametros']['zvc1_mintime_x3'];
		$this->zvc1_maxspeed = $st['Parametros']['zvc1_maxspeed'];

		$this->zvc2_mintime_x2 = $st['Parametros']['zvc2_mintime_x2'];
		$this->zvc2_mintime_x3 = $st['Parametros']['zvc2_mintime_x3'];
		$this->zvc2_maxspeed = $st['Parametros']['zvc2_maxspeed'];
		
		$this->zvc3_mintime_x2 = $st['Parametros']['zvc3_mintime_x2'];
		$this->zvc3_mintime_x3 = $st['Parametros']['zvc3_mintime_x3'];
		$this->zvc3_maxspeed = $st['Parametros']['zvc3_maxspeed'];
		
		$this->zvc4_mintime_x2 = $st['Parametros']['zvc4_mintime_x2'];
		$this->zvc4_mintime_x3 = $st['Parametros']['zvc4_mintime_x3'];
		$this->zvc4_maxspeed = $st['Parametros']['zvc4_maxspeed'];

	}

	/** Converte as coordenadas da planinha em uma matriz de pontos
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 18/06/2014
	 * @version 01/11/2016 novos tipos de pontos (em csv)
	 */
	public function getPoints(){
			
		$array_data = array();
		
		/*if (($handle = fopen($this->arq_pontos, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				$pointer = trim($data[0]);
				$coords = $data[2];
				$coords = str_replace('S', '-', $coords);
				$coords = str_replace('N', '+', $coords);
				$coords = str_replace('W', '-', $coords);
				$coords = str_replace('E', '+', $coords);
				$exp = explode(' ',$coords);
				
				$pto = array();
				
				$pto['latitude'] = $exp[0]*1;
				$pto['longitude'] = $exp[1]*1;
				$pto['descricao'] = $data[1];
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
		}*/

		$xml = simplexml_load_file($this->arq_pontos);
		foreach($xml->wpt AS $wpt){

			$pto = array();
			
			$pto['latitude'] = floatval($wpt['lat']);
			$pto['longitude'] = floatval($wpt['lon']);
			$pto['descricao'] = strip_tags($wpt->desc->asXml());
			$pto['snap'] = array();

			$tipo_pto = 'nada';				
			foreach ($this->arr_tipo AS $tipo_pto_key => $tipo_pto_val){
				if (strip_tags($wpt->name->asXml()) === $tipo_pto_key){
					$tipo_pto = $tipo_pto_val;
				}
			}

			$array_data[$tipo_pto][] = $pto;
		}
		
		$this->points_ini = $this->points = $array_data;
	}
	
	public function process(){
		try{
			printf("Obtendo pontos do arquivo ".$this->arq_pontos.". . .\r\n");
			$this->getPoints();
			$this->tracProcess();			
		} catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	/** Processa a trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 25/06/2014
	 * @version 02/11/2014
	 */
	public function tracProcess(){
	
		$this->getFiles();
	
		foreach ($this->arq_trac AS $file){		

			printf("Processando arquivo ".$file.". . .\r\n");
			
			//$this->points = array();
			//$this->trac = array();
			
			$folder = str_replace(".", "_", $file);
			
			//só processa não existir
			if (!is_dir($this->report_path."/".$folder)){
				
				$xml = simplexml_load_file($this->import_path."/".$file);
				
				$previousKey = 0;
				//acumulador de distância
				$dist_acum = 0;

				foreach($xml->trk AS $trk){			
					foreach($trk->trkseg->trkpt AS $trkpt){
						//Pega todas as informações de cada ponto da trilha
						$hora = $this->functions->toSec(substr($trkpt->time,-9,8));
						
						$this->trac[$folder][$hora]['indice'] = $hora;
						$this->trac[$folder][$hora]['latitude'] = floatval($trkpt['lat']);
						$this->trac[$folder][$hora]['longitude'] = floatval($trkpt['lon']);
						$this->trac[$folder][$hora]['data'] = substr($trkpt->time,0,10);
						$this->trac[$folder][$hora]['hora'] = gmstrftime('%H:%M:%S',(strtotime(substr($trkpt->time,-9,8)) + $this->fuso));  	
						$this->trac[$folder][$hora]['altitude'] = floatval($trkpt->ele);
						//distancia em km
						if ($previousKey > 0){
							$this->trac[$folder][$hora]['distancia'] = $this->functions->distancia($this->trac[$folder][$previousKey],$this->trac[$folder][$hora]);
						} else{
							$this->trac[$folder][$hora]['distancia'] = floatval(0);
						}
						$dist_acum += ($this->trac[$folder][$hora]['distancia']);		
						$this->trac[$folder][$hora]['distancia_acumulada'] = $dist_acum;
						if($hora > $previousKey){
							$vel = round(($this->trac[$folder][$hora]['distancia'] / (($hora-$previousKey)/3600)),2);
						}
						$vel = ($vel > 0) ? $vel : 0;
						$this->trac[$folder][$hora]['velocidade'] = $vel;
						/*if ($vel > $this->velmax){	
							$this->trac[$folder][$hora]['ultrapassou_velmax'] = 'SIM';
						} else{
							$this->trac[$folder][$hora]['ultrapassou_velmax'] = 'NAO';
						}*/
						
						$arr_step = $this->trac[$folder][$hora];
						
						//steps
						if ($dist_acum >= $this->steps_length){			
							$this->steps[$hora] = $arr_step;
							$dist_acum = 0;
						}
						
						$previousKey = $hora;
					}
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
					//echo(count($laps));
					
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

					/*if($tipo=='waypoints' && $key===0){
						var_dump($this->points[$tipo][$key]['snap'][$folder]);
						echo('----------------------------------------------------------------------------------');
					}*/
				}
			}
		}
	}
	
	/** Coloca nos pontos de entrada a informação de todos os pontos que estão dentro da zona referente a entrada
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 06/12/2016
	 */
	private function zoneProcess($folder){
		
		//somente entradas possuem zones
		foreach ($this->points['entradas'] AS $key => $point){
			if(is_array($point['snap'][$folder])){
				foreach($point['snap'][$folder] AS $keySnap => $snap){
					foreach ($this->trac[$folder] AS $keyTrac => $ptTrac){
						if ($ptTrac['indice'] >= $this->points['entradas'][$key]['snap'][$folder][$keySnap]['indice']
							&& $ptTrac['indice'] <= $this->points['saidas'][$key]['snap'][$folder][$keySnap]['indice']
							&& $ptTrac['velocidade'] > 0){

							$idx = $ptTrac['indice'];
							$this->points['entradas'][$key]['snap'][$folder][$keySnap]['zone'][$idx] = $ptTrac['velocidade'];
							$tempo = $this->points['saidas'][$key]['snap'][$folder][$keySnap]['indice'] - $this->points['entradas'][$key]['snap'][$folder][$keySnap]['indice'];
							$this->points['entradas'][$key]['snap'][$folder][$keySnap]['zone']['tempo'] = $tempo;
						}
					}
				}
			}
		}
	}	
	
	/** Coloca nos pontos de entrada a informação de todos os pontos que estão dentro da zona referente a entrada, 
	que ultrapassaram a velocidade máxima
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 06/12/2016
	 */
	private function radarProcess($folder){	
		
		foreach ($this->points['entradas'] AS $keyEntrada => $entrada){
			if(is_array($entrada['snap'][$folder])){
				foreach ($entrada['snap'][$folder] AS $keySnap => $snap){
					if(is_array($snap['zone'])){
						foreach ($snap['zone'] AS $keyZone => $vel){						
							if ($keyEntrada == 0) $maxspeed = $this->zvc1_maxspeed;
							if ($keyEntrada == 1) $maxspeed = $this->zvc2_maxspeed;
							if ($keyEntrada == 2) $maxspeed = $this->zvc3_maxspeed;
							if ($keyEntrada == 3) $maxspeed = $this->zvc4_maxspeed;
							if ($vel >= $maxspeed && $vel < ($maxspeed+$this->radar1)){
								$this->points['entradas'][$keyEntrada]['snap'][$folder][$keySnap]['radar1'][$keyZone] = $vel;
							}
							if ($vel >= ($maxspeed+$this->radar1) && $vel < ($maxspeed+$this->radar2)){
								$this->points['entradas'][$keyEntrada]['snap'][$folder][$keySnap]['radar2'][$keyZone] = $vel;
							}
							if ($vel >= ($maxspeed+$this->radar2)){
								$this->points['entradas'][$keyEntrada]['snap'][$folder][$keySnap]['radar3'][$keyZone] = $vel;
							}
						}
					}
				}
			}
		}
	}
	
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
		$pontoAnterior = array();
		$j = 0;
		//echo "(";		
		foreach($arrPoints AS $tracPoint){
			
			$indice = $tracPoint['indice'];

			//echo ($this->functions->distancia($tracPoint,$pontoAnterior) > ($this->gate/1000)) ? "SIM" : "NAO";
			//echo "<br>";
			$pontoAnterior = $tracPoint;

			if (($indice-$indiceAnterior) > $interval){
				$j++;
			}				
			$group[$j][] = $tracPoint;
			$indiceAnterior = $indice;
		}
		//echo ")<br><br><br><br>";
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
			$string = sprintf("Version,212\r\n\r\nWGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0\r\nUSER GRID,0,0,0,0,0\r\n\r\n");			
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
				$string = sprintf("Veículo;SS;Tipo de tempo;Horário;Obs\r\n");
				$string_aux = "";
				$arr_linha = array();
				foreach ($this->arr_tipo AS $tipo_key => $tipo_desc){
					foreach ($this->points[$tipo_desc] AS $key_point => $point){
						$key_point_txt = $point['descricao'];

						// ---------------------------------------------------------------

						if($tipo_key=='W' && count($point['snap'][$folder]) == 0){
							$arr_linha[intval($folder)]['P']['Perda: '.$key_point_txt][] = array("hora"=>$this->lost_wp_penalty);
						}

						// ---------------------------------------------------------------

						if($tipo_key=='CB' && (count($point['snap'][$folder]) == 0 || $point['snap'][$folder][0]['velocidade'] > $this->stamp_vel)){
							if(count($point['snap'][$folder]) > 0){
								$obs = ' - Velocidade: '.$point['snap'][$folder][0]['velocidade'].'km/h';
							}
							else{
								$obs = '';
							}
							$arr_linha[intval($folder)]['P']['Perda: '.$key_point_txt.$obs][] = array("hora"=>$this->lost_stamp_penalty);
						}

						// Radares -------------------------------------------------------
						$radar_penalty = '';
						if($key_point == 0){
							$velmax = $this->zvc1_maxspeed;
						}
						if($key_point == 1){
							$velmax = $this->zvc2_maxspeed;
						}
						if($key_point == 2){
							$velmax = $this->zvc3_maxspeed;
						}
						if($key_point == 3){
							$velmax = $this->zvc4_maxspeed;
						}
						if($tipo_key=='IR' && count($point['snap'][$folder][0]['radar1']) > 1){
							$gratervel = $this->penalizaRadar($point['snap'][$folder][0]['radar1'], $velmax);
							$radar_penalty = $this->radar1_penalty;	
						}
						if($tipo_key=='IR' && count($point['snap'][$folder][0]['radar2']) > 1){
							$gratervel = $this->penalizaRadar($point['snap'][$folder][0]['radar2'], $velmax);
							$radar_penalty = $this->radar2_penalty;
						}
						if($tipo_key=='IR' && count($point['snap'][$folder][0]['radar3']) > 1){
							$gratervel = $this->penalizaRadar($point['snap'][$folder][0]['radar3'], $velmax);
							$radar_penalty = $this->radar3_penalty;
						}
						if($tipo_key=='IR' && $gratervel > 0){
							$arr_linha[intval($folder)]['P']['Alta velocidade: '.$key_point_txt.' - Velocidade mais alta na ZVC: '.$gratervel.'km/h'][] = array("hora"=>$radar_penalty);
						}
						// Radares -------------------------------------------------------

						// ---------------------------------------------------------------

						// Tempo ---------------------------------------------------------
						$tempo = $point['snap'][$folder][0]['zone']['tempo'];
						if($key_point == 0){
							$tempo_x2 = $this->functions->toSec($this->zvc1_mintime_x2);
							$tempo_x3 = $this->functions->toSec($this->zvc1_mintime_x3);
						}
						if($key_point == 1){
							$tempo_x2 = $this->functions->toSec($this->zvc2_mintime_x2);
							$tempo_x3 = $this->functions->toSec($this->zvc2_mintime_x3);
						}
						if($key_point == 2){
							$tempo_x2 = $this->functions->toSec($this->zvc3_mintime_x2);
							$tempo_x3 = $this->functions->toSec($this->zvc3_mintime_x3);
						}
						if($key_point == 3){
							$tempo_x2 = $this->functions->toSec($this->zvc4_mintime_x2);
							$tempo_x3 = $this->functions->toSec($this->zvc4_mintime_x3);
						}

						$diff_x2 = ($tempo_x2 - $tempo);
						$diff_x3 = ($tempo_x3 - $tempo);

						//$diff_x3 = $key_point;
						
						if($tempo > 0){
							if($tipo_key=='IR' && $diff_x3 > 0){
								$pen_x3 = $diff_x2 * 3;
								$arr_linha[intval($folder)]['P']['Tempo abaixo (x3): '.$key_point_txt][] = array("hora"=>gmdate("H:i:s", $pen_x3));					
							}
							elseif($tipo_key=='IR' && $diff_x2 > 0){
								$pen_x2 = $diff_x2 * 2;
								$arr_linha[intval($folder)]['P']['Tempo abaixo (x2): '.$key_point_txt][] = array("hora"=>gmdate("H:i:s", $pen_x2));					
							}
						}
						// Tempo ---------------------------------------------------------

						foreach($point['snap'] AS $key_snap => $snap){
							foreach($snap AS $volta => $detalhes){
								$arr_linha[intval($folder)][$tipo_key][$key_point_txt][$volta] = $detalhes;	
							}
						}
					}
				}

				foreach($arr_linha AS $veiculo => $tipos_ponto){
					foreach($tipos_ponto AS $tipo_key => $pontos){
						foreach($pontos AS $point_key => $voltas){
							foreach($voltas AS $num_volta => $volta){

								//descartar outras voltas
								if($num_volta==0){

									//$obs = ($tipo_key != 'PT') ? " - Ocorrência: ".($num_volta+1)." - Velocidade: ".$volta['velocidade']."km/h" : "";
									$obs = ($tipo_key != 'P') ? " - Velocidade: ".$volta['velocidade']."km/h" : "";

									$string_tmp = sprintf("%s;%s;%s;%s;%s\r\n"
										,$veiculo
										,$this->current_ss
										,$tipo_key
										,$volta['hora']
										,$point_key.$obs
									);

									$string .= $string_tmp;

									$link1 = mysql_connect('mysql02.chronosat.com.br', 'chronosat1', 'chrono2002');
									$link2 = mysql_connect('mysql03.chronosat.com.br', 'chronosat2', 'chrono2002');
									$link3 = mysql_connect('mysql04.chronosat.com.br', 'chronosat3', 'chrono2002');

									$tempo_valor = $volta['hora'];

									$parte_decimal = end(explode('.', $tempo_valor));
									$parte_decimal = str_pad($parte_decimal, 2, '0', STR_PAD_RIGHT);
									$parte_decimal = $parte_decimal*1;
									$parte_decimal = ($parte_decimal<10) ? 0 : $parte_decimal;

									$sql = "INSERT INTO t01_tempos (c01_valor, c01_tipo, c01_status, c03_codigo, c02_codigo, c01_sigla) VALUES (TIME_TO_SEC('$tempo_valor'), '$tipo_key', getTempoStatus($veiculo, ".$this->current_ss.", '$tipo_key'), $veiculo, ".$this->current_ss.", 'snaptrac')";

									$result1 = mysql_query($sql,$link1);
									$result2 = mysql_query($sql,$link2);
									$result3 = mysql_query($sql,$link3);

									mysql_close($link1);
									mysql_close($link2);
									mysql_close($link3);

									if( in_array($tipo_key, array("L","LT","C","CT","I1","I2","I3","I4","P","PT")) ){
										$string_aux .= $string_tmp;
									}
								}
							}
						}						
					}					
				}

				
				/*foreach($this->radar AS $keyRadar => $radar){
					$string .= sprintf("%s;%s;%s;%s;%s\r\n"
						,intval($folder)
						,$this->current_ss
						,'PR'
						,$radar['hora']
						,"Ocorrência de excesso de velocidade (".$radar['velocidade']."km/h) em ZVC."
					);
				}*/
				
				
				//$string .= $string_aux;
				$this->relatorio_exportar_chronosat .= $string_aux;
						
			} 
			elseif ($tipo=='exportar_chronosat'){
				$string = sprintf("Veículo;SS;Tipo de tempo;Horário;Obs\r\n");
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

	/** Penaliza no radar */
	public function penalizaRadar($arr_radar, $velmax){
										
		$idx_anterior = 0;
		$continuo = 0;
		$gratervel = 0;

		foreach ($arr_radar AS $idx => $vel){

			if ($vel > $velmax){

				if ($idx_anterior > 0 && (($idx-$idx_anterior) == 1) ){
					$continuo++;
				}
				elseif($continuo < $this->sec_continuous){
					//falhando volta a zero caso já não tenha encontrado ocorrencia de continuidade
					$continuo = 0;
				}

				$idx_anterior = $idx;

				if ($gratervel < $vel){
					$gratervel = $vel;
				}
			}
		}	

		if ($continuo < $this->sec_continuous){			
			$gratervel = 0;
		}
		
		return $gratervel;
	}

	#endregion
}