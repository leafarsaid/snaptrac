	<?
	/** Gera relatórios
	 *
	 * @author Rafael Dias <rafael@chronosat.com.br>
	 * @version 04/07/2014
	 */
	private function reportFile2($file,$tipo='radar'){
		
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
				$string2 = $string;
				$string = sprintf("Veículo;SS;Tipo de tempo;Horário;Obs\r\n");
				$string_aux = "";
				$arr_linha = array();
				$falt_report = "";
				$pass_report = "";
				foreach ($this->arr_tipo AS $tipo_key => $tipo_desc){
					if (isset($this->points[$tipo_desc])){
						foreach ($this->points[$tipo_desc] AS $key_point => $point){
							$key_point_txt = $point['descricao'];

							// Perda de Waypoin ---------------------------------------------------------------

							if($tipo_key=='W' && count($point['snap'][$folder]) == 0){
								$falt_desc = 'Perda: '.$key_point_txt;
								$falt_report .= sprintf("%s\r\n",$falt_desc);
								$arr_linha[intval($folder)]['P'][$falt_desc][] = array("hora"=>$this->lost_wp_penalty);
							}

							// Perda de Carimbo ---------------------------------------------------------------

							//laço de ocorrências de passagens em carimbos
							for ($c=0; $c<count($point['snap'][$folder]);$c++) {
								if($tipo_key=='CB' && (count($point['snap'][$folder]) == 0 || $point['snap'][$folder][$c]['velocidade'] > $this->stamp_vel)){
									if(count($point['snap'][$folder]) > 0){
										$obs = ' - Velocidade: '.$point['snap'][$folder][$c]['velocidade'].'km/h';
									}
									else{
										$obs = '';
									}
									$falt_desc = 'Perda: '.$key_point_txt.$obs;
									$falt_report .= sprintf("%s\r\n",$falt_desc);
									$arr_linha[intval($folder)]['P'][$falt_desc][] = array("hora"=>$this->lost_stamp_penalty);
								}
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
							//laço de ocorrências de passagens em zonas
							for ($z=0; $z<count($point['snap'][$folder]);$z++) {
								if($tipo_key=='IR' && count($point['snap'][$folder][$z]['radar1']) > 1){
									$gratervel = $this->penalizaRadar($point['snap'][$folder][$z]['radar1'], $velmax);
									$radar_penalty = $this->radar1_penalty;	 
								}
								if($tipo_key=='IR' && count($point['snap'][$folder][$z]['radar2']) > 1){
									$gratervel = $this->penalizaRadar($point['snap'][$folder][$z]['radar2'], $velmax);
									$radar_penalty = $this->radar2_penalty;
								}
								if($tipo_key=='IR' && count($point['snap'][$folder][$z]['radar3']) > 1){
									$gratervel = $this->penalizaRadar($point['snap'][$folder][$z]['radar3'], $velmax);
									$radar_penalty = $this->radar3_penalty;
								}
							}
							if($tipo_key=='IR' && $gratervel > 0){
								$falt_desc = 'Alta velocidade: '.$key_point_txt.' - Velocidade mais alta na ZVC: '.$gratervel.'km/h';
								$falt_report .= sprintf("%s\r\n",$falt_desc);
								$arr_linha[intval($folder)]['P'][$falt_desc][] = array("hora"=>$radar_penalty);
							}

							$arr_radares = array('radar1','radar2','radar3');

							/*foreach($point['snap'][$folder][0]['radar1'] AS $rd1){
								$string2 .= sprintf("w,d,%s,%s,%s,05/28/2014,00/00/00,00:00:00,0,0,151,0,13\r\n"
									,'Radar1'
									,$rd1['latitude']
									,$rd1['longitude']
								);
							}


							$path2 = $this->report_path."/".$folder."/altas_velocidades.txt";
							touch($path2);			
							$handle2 = fopen($path2, "w");
							fwrite($handle2, $string2);
							fclose($handle2);*/

							// Radares -------------------------------------------------------

							// ---------------------------------------------------------------

							// Tempo ---------------------------------------------------------
							
							//tempo de passagem pela zona
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
							if($key_point == 4){
								$tempo_x2 = $this->functions->toSec($this->zvc5_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc5_mintime_x3);
							}
							if($key_point == 5){
								$tempo_x2 = $this->functions->toSec($this->zvc6_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc6_mintime_x3);
							}
							if($key_point == 6){
								$tempo_x2 = $this->functions->toSec($this->zvc7_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc7_mintime_x3);
							}
							if($key_point == 7){
								$tempo_x2 = $this->functions->toSec($this->zvc8_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc8_mintime_x3);
							}
							if($key_point == 8){
								$tempo_x2 = $this->functions->toSec($this->zvc9_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc9_mintime_x3);
							}
							if($key_point == 9){
								$tempo_x2 = $this->functions->toSec($this->zvc10_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc10_mintime_x3);
							}
							if($key_point == 10){
								$tempo_x2 = $this->functions->toSec($this->zvc11_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc11_mintime_x3);
							}
							if($key_point == 11){
								$tempo_x2 = $this->functions->toSec($this->zvc12_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc12_mintime_x3);
							}
							if($key_point == 12){
								$tempo_x2 = $this->functions->toSec($this->zvc13_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc13_mintime_x3);
							}
							if($key_point == 13){
								$tempo_x2 = $this->functions->toSec($this->zvc14_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc14_mintime_x3);
							}
							if($key_point == 14){
								$tempo_x2 = $this->functions->toSec($this->zvc15_mintime_x2);
								$tempo_x3 = $this->functions->toSec($this->zvc15_mintime_x3);
							}

							$diff_x2 = ($tempo_x2 - $tempo);
							$diff_x3 = ($tempo_x3 - $tempo);

							//$diff_x3 = $key_point;
							
							if($tempo > 0){
								if($diff_x3 > 0){
									$pen_x3 = $diff_x3 * 3;
									$falt_desc = 'Tempo abaixo (x3): '.$key_point_txt;
									$falt_report .= sprintf("%s\r\n",$falt_desc);
									$arr_linha[intval($folder)]['P'][$falt_desc][] = array("hora"=>gmdate("H:i:s", $pen_x3));					
								}
								elseif($diff_x2 > 0){
									$pen_x2 = $diff_x2 * 2;
									$falt_desc = 'Tempo abaixo (x2): '.$key_point_txt;
									$falt_report .= sprintf("%s\r\n",$falt_desc);
									$arr_linha[intval($folder)]['P'][$falt_desc][] = array("hora"=>gmdate("H:i:s", $pen_x2));					
								}
							}
							// Tempo ---------------------------------------------------------

							foreach($point['snap'] AS $key_snap => $snap){
								foreach($snap AS $volta => $detalhes){
									$pass_report .= sprintf("Passagem: %s\r\n",$key_point_txt);
									$arr_linha[intval($folder)][$tipo_key][$key_point_txt][$volta] = $detalhes;	
								}
							}
						}
					}
				}

				foreach($arr_linha AS $veiculo => $tipos_ponto){
					foreach($tipos_ponto AS $tipo_key => $pontos){
						foreach($pontos AS $point_key => $voltas){
							foreach($voltas AS $num_volta => $volta){

								//descartar outras voltas
								//if($num_volta==0){

									//$obs = ($tipo_key != 'PT') ? " - Ocorrência: ".($num_volta+1)." - Velocidade: ".$volta['velocidade']."km/h" : "";
									$obs = ($tipo_key != 'P') ? " - Velocidade: ".$volta['velocidade']."km/h" : "";

									$especial = $this->current_ss + $num_volta;

									$string_tmp = sprintf("%s;%s;%s;%s;%s\r\n"
										,$veiculo
										,$especial
										,$tipo_key
										,$volta['hora']
										,$point_key.$obs
									);

									$string .= $string_tmp;

									if( in_array($tipo_key, array("L","LT","I1","I2","I3","I4","CT")) ){

										

										$tempo_valor = $volta['hora'];

										$parte_decimal = explode('.', $tempo_valor);
										$parte_decimal = end($parte_decimal);
										$parte_decimal = str_pad($parte_decimal, 2, '0', STR_PAD_RIGHT);
										$parte_decimal = $parte_decimal*1;
										$parte_decimal = ($parte_decimal<10) ? 0 : $parte_decimal;

										$sql = "INSERT INTO t01_tempos (c01_valor, c01_tipo, c01_status, c03_codigo, c02_codigo, c01_obs, c01_sigla) VALUES (TIME_TO_SEC('$tempo_valor'), '$tipo_key', getTempoStatus($veiculo, ".$especial.", '$tipo_key'), $veiculo, ".$especial.", '".$point_key.$obs."','SNAPTRAC')";

										/*if(!$this->link->query($sql)){
											printf("***********************************\r\n");
											printf("ERRO AO INSERIR NO BD: %s\r\n",$point_key.$obs);
											printf("***********************************\r\n");
										}*/

										//var_dump($result);

										//print_r("\r\n\r\n");

										//
										$string_aux .= $string_tmp;
									}
								//}
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

				if(strlen($pass_report)){
					printf("***********************************\r\n");

					printf("*                                 *\r\n");

					printf("*        P A S S A G E N S        *\r\n");

					printf("*                                 *\r\n");

					printf("***********************************\r\n");
					echo $pass_report;
				}
				if(strlen($falt_report)){
					printf("***********************************\r\n");

					printf("*                                 *\r\n");

					printf("*           P E N A I S           *\r\n");

					printf("*                                 *\r\n");

					printf("***********************************\r\n");
					echo $falt_report;
				}
						
			} 
			elseif ($tipo=='exportar_chronosat'){
				$string = sprintf("Veículo;SS;Tipo de tempo;Horário;Obs\r\n");
				$string .= $this->relatorio_exportar_chronosat;
				
			}
			elseif ($tipo=='relatorio_pontos_zona_radar'){

				if (!is_array($radares_arr)){
					$radares_arr = array();
				}

				//laço de zonas
				for ($e=0; $e<count($this->points['entradas']); $e++){

					//laço de ocorrências de enrtrada nas zonas
					for ($z=0; $z<count($this->points['entradas'][$e]['snap'][$folder]); $z++){

						if (count($this->points['entradas'][$e]['snap'][$folder][$z]['zone']['radar1']) > 0){
							$radares_arr = $radares_arr + $this->points['entradas'][$e]['snap'][$folder][$z]['radar1'];
						}

						if (count($this->points['entradas'][$e]['snap'][$folder][$z]['zone']['radar2']) > 0){
							$radares_arr = $radares_arr + $this->points['entradas'][$e]['snap'][$folder][$z]['radar2'];
						}

						if (count($this->points['entradas'][$e]['snap'][$folder][$z]['zone']['radar3']) > 0){
							$radares_arr = $radares_arr + $this->points['entradas'][$e]['snap'][$folder][$z]['radar3'];
						}

					}

				}

				foreach($radares_arr AS $kpoint => $val){
					if (is_numeric($kpoint)){

						$point = $this->trac[$folder][$kpoint];

						$string .= sprintf("w,d,%s,%s,%s,00/00/0000,00/00/00,00:00:00,0,0,48,0,13\r\n"
							,$point['velocidade']
							,$point['latitude']
							,$point['longitude']
						);
					}
				}
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