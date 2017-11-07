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
	
	public $zvc5_mintime_x2;
	public $zvc5_mintime_x3;
	public $zvc5_maxspeed;
	
	public $zvc6_mintime_x2;
	public $zvc6_mintime_x3;
	public $zvc6_maxspeed;
	
	public $zvc7_mintime_x2;
	public $zvc7_mintime_x3;
	public $zvc7_maxspeed;
	
	public $zvc8_mintime_x2;
	public $zvc8_mintime_x3;
	public $zvc8_maxspeed;
	
	public $zvc9_mintime_x2;
	public $zvc9_mintime_x3;
	public $zvc9_maxspeed;
	
	public $zvc10_mintime_x2;
	public $zvc10_mintime_x3;
	public $zvc10_maxspeed;
	
	public $zvc11_mintime_x2;
	public $zvc11_mintime_x3;
	public $zvc11_maxspeed;
	
	public $zvc12_mintime_x2;
	public $zvc12_mintime_x3;
	public $zvc12_maxspeed;
	
	public $zvc13_mintime_x2;
	public $zvc13_mintime_x3;
	public $zvc13_maxspeed;
	
	public $zvc14_mintime_x2;
	public $zvc14_mintime_x3;
	public $zvc14_maxspeed;
	
	public $zvc15_mintime_x2;
	public $zvc15_mintime_x3;
	public $zvc15_maxspeed;

	public $link;

	//public $pontos_radar1;
	//public $pontos_radar2;
	//public $pontos_radar3;
	
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

#region Métodos de orquestração
	
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
			'IR1' => 'entrada1',
			'FR1' => 'saida1',
			'IR2' => 'entrada2',
			'FR2' => 'saida2',
			'IR3' => 'entrada3',
			'FR3' => 'saida3',
			'IR4' => 'entrada4',
			'FR4' => 'saida4',
			'IR5' => 'entrada5',
			'FR5' => 'saida5'
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

		for ($z=1;$z<=15;$z++){
			$var_zvc_mintime_x2 = 'zvc'.$z.'_mintime_x2';
			$var_zvc_mintime_x3 = 'zvc'.$z.'_mintime_x3';
			$var_zvc_maxspeed = 'zvc'.$z.'_maxspeed';
			$this->$var_zvc_mintime_x2 = $st['Parametros'][$var_zvc_mintime_x2];
			$this->$var_zvc_mintime_x3 = $st['Parametros'][$var_zvc_mintime_x3];
			$this->$var_zvc_maxspeed = $st['Parametros'][$var_zvc_maxspeed];
		}
		//$this->link = mysqli_connect('mysql02.chronosat.com.br', 'chronosat1', 'chrono2002', 'chronosat1');
	}

	/** Método que orquestra o processamento dos tracks
	 *
	 */
	public function process(){
		try{			
			$this->getPoints();
			$this->getFiles();
			foreach ($this->arq_trac AS $file){
				$this->tracProcess($file);
				$folder = str_replace(".", "_", $file);
				$this->pointProcess($folder);
				$this->setTrechos($folder);
				$this->zoneProcess($folder);
				$this->radarProcess($folder);
				$this->stampProcess($folder);
				$this->reportPoints();
			}
			//mysqli_close($this->link);
		} catch(Exception $e){
			echo $e->getMessage();
			//mysqli_close($this->link);
		}
	}

	/** Converte as coordenadas no GPX em uma matriz de pontos
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 18/06/2014
	 * @version 01/11/2016 novos tipos de pontos (em csv)
	 */
	public function getPoints(){

		printf("Obtendo pontos do arquivo ".$this->arq_pontos.". . .\r\n");
			
		$array_data = array();

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

			$time = $wpt->time->asXml();

			if(strlen($time)){
				$ss = intval(date('d', strtotime($time)));
			} else{
				$ss = 0;
			}

			//$array_data[$ss][$tipo_pto][] = $pto;
			$array_data[$tipo_pto][] = $pto;
		}
		
		$this->points_ini = $this->points = $array_data;
	}

	#endregion

#region Métodos de processamento
		
	/** Processa o track
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 25/06/2014
	 * @version 02/11/2014
	 */
	private function tracProcess($file){
			
		printf("Processando arquivo ".$file.". . .\r\n");	

		$folder = str_replace(".", "_", $file);		
		
		//só processa não existir
		if (!is_dir($this->report_path."/".$folder)){
			
			$xml = simplexml_load_file($this->import_path."/".$file);			
			$previousKey = 0;
			$dist_acum = 0;

			//var_dump($xml->trk);
			//exit();

			foreach($xml->trk AS $trk){
				foreach($trk->trkseg AS $trkseg) {	
					foreach($trkseg->trkpt AS $trkpt){
						//Pega todas as informações de cada ponto da trilha
						$hora = $this->functions->toSec(substr($trkpt->time,11,8));
						
						$this->trac[$folder][$hora]['indice'] = $hora;
						$this->trac[$folder][$hora]['latitude'] = floatval($trkpt['lat']);
						$this->trac[$folder][$hora]['longitude'] = floatval($trkpt['lon']);
						$this->trac[$folder][$hora]['data'] = substr($trkpt->time,0,10);
						$this->trac[$folder][$hora]['hora'] = gmstrftime('%H:%M:%S',(strtotime(substr($trkpt->time,11,8)) + $this->fuso));  	
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
						
						$arr_step = $this->trac[$folder][$hora];
						
						//steps
						if ($dist_acum >= $this->steps_length){			
							$this->steps[$hora] = $arr_step;
							$dist_acum = 0;
						}
						
						$previousKey = $hora;
					}
				}
			}			
			rename($this->import_path."/".$file, $this->processed_path."/".$file);
		}	
	}
			
	/** Processa os pontos da planilha para achar a tangente na trilha
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 26/06/2014
	 */
	private function pointProcess($folder){
		
		foreach($this->arr_tipo AS $tipo){

			if (isset($this->points[$tipo])){
			
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
							$this->points[$tipo][$key]['snap'][$folder][] = $ponto_mais_proximo;
						}
					}
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
		for ($e=1;$e<=5;$e++){
			$txt_entrada = "entrada".$e;
			$txt_saida = "saida".$e;
			if(is_array($this->points[$txt_entrada])){
				foreach ($this->points[$txt_entrada] AS $key => $point){
					if(is_array($point['snap'][$folder])){

						foreach($point['snap'][$folder] AS $keyPass => $snap){

							//verifica se a zona está dentro de um trecho
							//necessário processar antes o setTrecho
							$continua = false;
							$keyPassSaida2 = $keyPass;
							$ss_entrada = $this->points[$txt_entrada][$key]['snap'][$folder][$keyPass]['ss'];
							if($ss_entrada > 0){
								$ss_saida = $this->points[$txt_saida][$key]['snap'][$folder][$keyPass]['ss'];
								if($ss_entrada == $ss_saida){
									$continua = true;
									$keyPassSaida2 = $keyPass;
								} else {
									if ($ss_entrada > 0){
										foreach ($this->points[$txt_saida][$key]['snap'][$folder] AS $keyPassSaida => $passSaida){
											if($passSaida['ss'] == $ss_entrada){
												$continua = true;
												$keyPassSaida2 = $keyPassSaida;
											}
										}
									} else {
										$continua = false;
									}
								}
							}

							if($continua){

								$pto_entrada = $this->points[$txt_entrada][$key]['snap'][$folder][$keyPass];
								$pto_saida = $this->points[$txt_saida][$key]['snap'][$folder][$keyPassSaida2];

								foreach ($this->trac[$folder] AS $keyTrac => $ptTrac){
									if ($ptTrac['indice'] >= $pto_entrada['indice']	&& $ptTrac['indice'] <= $pto_saida['indice'] && $ptTrac['velocidade'] > 0){

										$idx = $ptTrac['indice'];								

										//tempo em segundo de passagem pela zona
										$tempo = $pto_saida['indice'] - $pto_entrada['indice'];					

										if ($tempo > 0 || true){
											$this->points[$txt_entrada][$key]['snap'][$folder][$keyPass]['zone'][$idx] = $ptTrac['velocidade'];
											$this->points[$txt_entrada][$key]['snap'][$folder][$keyPass]['zone']['tempo'] = $tempo;	

											//penalização por tempo
											$mintime_x2 = "zvc".$e."_mintime_x2";
											$mintime_x3 = "zvc".$e."_mintime_x3";
											$tempo_x2 = $this->functions->toSec($this->$mintime_x2);
											$tempo_x3 = $this->functions->toSec($this->$mintime_x3);
											$diff_x2 = ($tempo_x2 - $tempo);
											$diff_x3 = ($tempo_x3 - $tempo);
											if($diff_x3 > 0){
												$pen_x3 = $diff_x3 * 3;
												$this->points[$txt_entrada][$key]['snap'][$folder][$keyPass]['zone']['penalty_per_time'] = gmdate("H:i:s", $pen_x3);		
											}
											elseif($diff_x2 > 0){
												$pen_x2 = $diff_x2 * 2;
												$this->points[$txt_entrada][$key]['snap'][$folder][$keyPass]['zone']['penalty_per_time'] = gmdate("H:i:s", $pen_x2);				
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}	
	
	/** Coloca nos pontos de entrada a informação de todos os pontos que estão dentro da zona referente a entrada, que ultrapassaram a velocidade máxima
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 06/12/2016
	 */
	private function radarProcess($folder){				

		for ($e=1;$e<=5;$e++){
			$txt_entrada = "entrada".$e;
			if(is_array($this->points[$txt_entrada])){
				foreach ($this->points[$txt_entrada] AS $keyEntrada => $entrada){

					for ($z=0;$z<15;$z++){
						$var_zvc = "zvc".($z+1)."_maxspeed";
						if ($keyEntrada == $z) {
							$maxspeed = $this->$var_zvc;
						}
					}

					if(is_array($entrada['snap'][$folder])){
						foreach ($entrada['snap'][$folder] AS $keyPass => $pass){
							if(is_array($pass['zone'])){
								foreach ($pass['zone'] AS $keyZone => $vel){
									if (is_numeric($keyZone)){								

										if ($vel >= $maxspeed && $vel < ($maxspeed+$this->radar1)){
											$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$keyPass]['zone']['radar1'][] = $vel;
										}

										if ($vel >= ($maxspeed+$this->radar1) && $vel < ($maxspeed+$this->radar2)){
											$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$keyPass]['zone']['radar2'][] = $vel;
										}

										if ($vel >= ($maxspeed+$this->radar2)){
											$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$keyPass]['zone']['radar3'][] = $vel;
										}										
									}
								}
							}
						}
					}

					for($passagem=0;$passagem<count($this->points[$txt_entrada][$keyEntrada]['snap'][$folder]);$passagem++){

						if(count($this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar1']) > 1){
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['gratervel'] = $this->penalizaRadar($this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar1'], $maxspeed);
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar_penalty'] = $this->radar1_penalty;
						}

						if(count($this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar2']) > 1){
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['gratervel'] = $this->penalizaRadar($this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar2'], $maxspeed);
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar_penalty'] = $this->radar2_penalty;
						}

						if(count($this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar3']) > 1){
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['gratervel'] = $this->penalizaRadar($this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar3'], $maxspeed);
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar_penalty'] = $this->radar3_penalty;
						} else {
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['gratervel'] = '';
							$this->points[$txt_entrada][$keyEntrada]['snap'][$folder][$passagem]['zone']['radar_penalty'] = '';
						}
					}
				}
			}
		}
	}

	/** Processo do carimbo
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 06/11/2017
	 */
	private function stampProcess($folder){
		if(is_array($this->points['carimbo'])){
			foreach ($this->points['carimbo'] AS $key => $point){
				if(is_array($point['snap'][$folder])){
					foreach($point['snap'][$folder] AS $keyPass => $snap){
						if ($snap['velocidade'] > $this->stamp_vel){
							$this->points['carimbo'][$key]['snap'][$folder][$keyPass]['lost_stamp_penalty'] = $this->lost_stamp_penalty;
						}
					}
				}
			}
		}
	}
	

	#endregion

#region Métodos de auxiliares
	
	/**  Retorna ponto mais próximo de uma lista para uma referência
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 26/06/2014
	 * @param unknown_type $point Referência
	 * @param unknown_type $arrPoints Lista
	 * @return Ambigous <multitype:, unknown>
	 */
	private function nearest($point, $arrPoints){
		
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
	private function group($arrPoints, $interval){
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
	private function getFiles(){

		printf("Obtendo arquivos da pasta ".$this->import_path.". . .\r\n");

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
	 */
	private function setTrechos($folder){
		$largadas = array();
		$chegadas = array();
		foreach ($this->points['largada'][0]['snap'][$folder] AS $largada) $largadas[] = $largada['indice'];
		foreach ($this->points['chegada'][0]['snap'][$folder] AS $chegada) $chegadas[] = $chegada['indice'];

		//var_dump($largadas);

		foreach($this->arr_tipo AS $tipo){
			if (isset($this->points[$tipo])){
				foreach ($this->points[$tipo] AS $key => $point){
					foreach($point['snap'][$folder] AS $keyPass => $pass){
						for($l=0; $l<count($largadas); $l++){
							if ($pass['indice']>=$largadas[$l] && $pass['indice']<=$chegadas[$l]){
								$this->points[$tipo][$key]['snap'][$folder][$keyPass]['ss'] = $l+1;
							}
						}
					}
				}
			}
		}
	}


	/** Gera relatórios
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 04/07/2014
	 */
	private function reportPoints($folder = ''){

		ob_flush();
		ob_start();

		$data = array();

		foreach($this->points AS $tipoPonto => $pontosRef){
			if ($tipoPonto != 'nada'){
				$i = 0;				
				foreach($pontosRef AS $pontoRef){
					$data[$tipoPonto][$i]["latitude"] = $pontoRef['latitude'];
					$data[$tipoPonto][$i]["longitude"] = $pontoRef['longitude'];					
					$data[$tipoPonto][$i]["descricao"] = $pontoRef['descricao'];

					foreach($pontoRef['snap'] AS $folder => $passagem){
						$p = 0;
						foreach($passagem AS $pontoPass){							
							$data[$tipoPonto][$i]["passagens"][$folder][$p]["ss"]=$pontoPass['ss'];
							$data[$tipoPonto][$i]["passagens"][$folder][$p]["hora"]=$pontoPass['hora'];
							$data[$tipoPonto][$i]["passagens"][$folder][$p]["velocidade"]=$pontoPass['velocidade'];
							$data[$tipoPonto][$i]["passagens"][$folder][$p]["latitude"]=$pontoPass['latitude'];
							$data[$tipoPonto][$i]["passagens"][$folder][$p]["longitude"]=$pontoPass['longitude'];
							if($pontoPass['lost_stamp_penalty']){
								$data[$tipoPonto][$i]["passagens"][$folder][$p]["lost_stamp_penalty"]=$pontoPass['lost_stamp_penalty'];
							}
							//if ($tipoPonto=='entradas'){

								if (is_array($pontoPass['zone'])){
									
									if(strlen($pontoPass['zone']['gratervel'])>0){
										$data[$tipoPonto][$i]["passagens"][$folder][$p]["velocidade_alta"] = $pontoPass['zone']['gratervel'];
									}
									if(strlen($pontoPass['zone']['radar_penalty'])>0){
										$data[$tipoPonto][$i]["passagens"][$folder][$p]["penalidade_por_radar"] = $pontoPass['zone']['radar_penalty'];
									}
									if(strlen($pontoPass['zone']['penalty_per_time'])>0){
										$data[$tipoPonto][$i]["passagens"][$folder][$p]["penalidade_por_tempo"] = $pontoPass['zone']['penalty_per_time'];
									}
									if(isset($pontoPass['zone']['tempo'])){
										$data[$tipoPonto][$i]["passagens"][$folder][$p]["tempo"] = gmdate("H:i:s", $pontoPass['zone']['tempo']);
									}
									
								}
							//}
							$p++;
						}
					}
					$i++;
				}
			}
		}

		echo json_encode($data, JSON_PRETTY_PRINT);

		//echo "<pre>";
		//var_dump($this->points);
		//echo "</pre>";

		file_put_contents($this->report_path."\\report.json", ob_get_flush());
	}
	
	/** Penaliza no radar */
	private function penalizaRadar($arr_radar, $velmax){
										
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