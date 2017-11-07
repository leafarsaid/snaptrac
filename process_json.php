<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

ini_set('memory_limit', '128000M');
set_time_limit (0);

include_once 'php/snaptrac.php';
include_once 'php/functions.php';
//require_once 'lib/objDB.php';

$functions = new functions();
//$snaptrac = new snaptrac(parse_ini_file("snaptrac.ini"));
$parametros = parse_ini_file("snaptrac.ini");

$url = $parametros['report_path']."\\report.json";
$JSON = file_get_contents($url);

$data = json_decode($JSON);
$comps = array();
$quem_passou_wpt = array();
$quem_passou_carimbo = array();
$waypoints = array();
$trechos = array();
$report = "";
$sql = "";
$tipos = array('largada', 'chegada', 'inter1', 'inter2', 'inter3', 'inter4');

#region Definições

	//montando matriz de competidores e de trechos para waypoints		
	foreach ($data->waypoints AS $wpt) {
		$desc = str_replace(" ","_",$wpt->descricao);	
		foreach ($wpt->passagens AS $folder => $passagens){
			$quem_passou_wpt['waypoints'][$desc][] = $folder;
			foreach ($passagens AS $pass){
				if ($desc && $pass->ss){
					$comps[$functions->numeral($folder)]['waypoints'][$desc]['trechos'][] = $pass->ss;
					$comps[$functions->numeral($folder)]['waypoints'][$pass->ss][$desc]['horarios'] = $pass->hora;
					$comps[$functions->numeral($folder)]['waypoints'][$pass->ss][$desc]['velocidades'] = $pass->velocidade;
					$trechos[$pass->ss]++;
				}
			}
		}
	} 	
	//ordenando por chave
	ksort($comps);

	//montando matriz de competidores e de trechos para outros tipos
	foreach($tipos AS $tipo){
		foreach ($data->$tipo AS $wpt) {	
			$desc = str_replace(" ","_",$wpt->descricao);	
			foreach ($wpt->passagens AS $folder => $passagens){
				$quem_passou_wpt[$tipo][$desc][] = $folder;
				foreach ($passagens AS $pass){
					if ($desc && $pass->ss){
						$comps[$functions->numeral($folder)][$tipo][$desc]['trechos'][] = $pass->ss;
						$comps[$functions->numeral($folder)][$tipo][$desc][$pass->ss]['horarios'] = $pass->hora;
						$comps[$functions->numeral($folder)][$tipo][$desc][$pass->ss]['velocidades'] = $pass->velocidade;
					}
				}
			}
		} 
	}

	//montando matriz de competidores e de trechos para carimbos
	foreach ($data->carimbo AS $wpt) {
		foreach ($wpt->passagens AS $folder => $passagens){
			$quem_passou_carimbo[$wpt->descricao][] = $folder;
			foreach ($passagens AS $pass){
				if ($wpt->descricao && $pass->ss){
					$comps[$functions->numeral($folder)]['carimbo'][$wpt->descricao]['trechos'][] = $pass->ss;
					$comps[$functions->numeral($folder)]['carimbo'][$wpt->descricao][$pass->ss]['horarios'] = $pass->hora;
					$comps[$functions->numeral($folder)]['carimbo'][$wpt->descricao][$pass->ss]['velocidades'] = $pass->velocidade;			
				}
				//definindo perdas por já ter a penalidade
				if($pass->lost_stamp_penalty){				
					$comps[$functions->numeral($folder)]['perdas_carimbos_trechos'][$wpt->descricao][$pass->ss][]++;
				}
			}
		}
	}

	//montando matriz de competidores e de trechos para ZVCs

	for ($e=1;$e<=5;$e++){
		$txt_entrada = "entrada".$e;
		foreach ($data->$txt_entrada AS $wpt) {
			foreach ($wpt->passagens AS $folder => $passagens){
				$quem_passou_wpt[$txt_entrada][$desc][] = $folder;
				foreach ($passagens AS $pass){
					if ($wpt->descricao && $pass->ss){
						$comps[$functions->numeral($folder)][$txt_entrada][$wpt->descricao]['trechos'][] = $pass->ss;
						$comps[$functions->numeral($folder)][$txt_entrada][$wpt->descricao][$pass->ss]['horarios'] = $pass->hora;
						$comps[$functions->numeral($folder)][$txt_entrada][$wpt->descricao][$pass->ss]['velocidades'] = $pass->velocidade;
						$comps[$functions->numeral($folder)][$txt_entrada][$wpt->descricao][$pass->ss]['penalidade_por_radar'] = $pass->penalidade_por_radar;
						$comps[$functions->numeral($folder)][$txt_entrada][$wpt->descricao][$pass->ss]['penalidade_por_tempo'] = $pass->penalidade_por_tempo;
						$comps[$functions->numeral($folder)][$txt_entrada][$wpt->descricao][$pass->ss]['tempo'] = $pass->tempo;
					}
				}
			}
		}
	}

	#endregion

#region Perdas e Passagens

	//Passagens dos tipos
	foreach($tipos AS $tipo){
		foreach ($comps AS $folder => $comp){
			foreach ($comps[$folder][$tipo] AS $wpCompDesc => $ssComp){
				foreach ($ssComp AS $ssKeyComp => $numPass){
					foreach($trechos AS $ss => $vtrecho){
						if(in_array($ss, $comps[$folder][$tipo][$wpCompDesc]['trechos'])){
							$comps[$folder]['passagens'][$tipo][$wpCompDesc][$ss][]++;
						}
					}
				}
			}
			/*foreach($data->waypoints AS $wpt){
				$desc = str_replace(" ","_",$wpt->descricao);				
				foreach($trechos AS $ss => $vtrecho){
					if(in_array($folder, $quem_passou_wpt[$tipo][$desc])){
						$comps[$folder]['passagens'][$tipo][$desc][$ss][]++;
					}
				}
			}*/
		}
	}

	//Passagens das ZVCs
	for ($e=1;$e<=5;$e++){
		$tipo = "entrada".$e;
		foreach ($comps AS $folder => $comp){
			foreach ($comps[$folder][$tipo] AS $wpCompDesc => $ssComp){
				foreach ($ssComp AS $ssKeyComp => $numPass){
					foreach($trechos AS $ss => $vtrecho){
						if(in_array($ss, $comps[$folder][$tipo][$wpCompDesc]['trechos'])){
							$comps[$folder]['passagens'][$tipo][$wpCompDesc][$ss][]++;
						}
					}
				}
			}
		}
	}

	//definindo as perdas de WP
	foreach ($comps AS $folder => $comp){
		foreach ($comps[$folder]['waypoits'] AS $wpCompDesc => $ssComp){
			foreach ($ssComp AS $ssKeyComp => $numPass){
				foreach($trechos AS $ss => $vtrecho){
					if(!in_array($ss, $comps[$folder]['waypoits'][$wpCompDesc]['trechos'])){
						$comps[$folder]['perdas_trechos'][$wpCompDesc][$ss][]++;
					}
				}
			}		
		}
		foreach($data->waypoints AS $wpt){
			$desc = str_replace(" ","_",$wpt->descricao);
			
			foreach($trechos AS $ss => $vtrecho){
				if(!in_array($folder, $quem_passou_wpt['waypoints'][$desc])){
					$comps[$folder]['perdas_trechos'][$desc][$ss][]++;
				}
			}
		}
	}

	//definindo as perdas de CB
	foreach ($comps AS $folder => $comp){
		if (is_array($comps[$folder]['carimbo'])){
			foreach ($comps[$folder]['carimbo'] AS $stCompDesc => $ssComp){
				foreach ($ssComp AS $ssKeyComp => $numPass){
					foreach($trechos AS $ss => $vtrecho){				
						if(!in_array($ss, $comps[$folder]['carimbo'][$stCompDesc]['trechos'])){
							$comps[$folder]['perdas_carimbos_trechos'][$stCompDesc][$ss][]++;
						}				
					}
				}
			}
		}
		foreach($data->carimbo AS $wpt){
			foreach($trechos AS $ss => $vtrecho){
				if(is_array($quem_passou_carimbo[$wpt->descricao])){
					if(!in_array($folder, $quem_passou_carimbo[$wpt->descricao])){
						$comps[$folder]['perdas_carimbos_trechos'][$wpt->descricao][$ss][]++;
					}
				}
			}
		}
	}
	#endregion

#rengion Relatórios

	//escrevendo o relatório parcial dos tipos
	foreach($tipos AS $tipo){
		//$tipo_txt = ucfirst($tipo);
		$report .= "#### Ocorrências de $tipo ########################\r\n\r\n";	
		$sql .= "-- Ocorrências de $tipo\r\n\r\n";
		foreach ($comps AS $folder => $comp){
			$report .= "   Ocorrências de $tipo do competidor $folder:\r\n";
			$sql .= "-- Ocorrências de $tipo do competidor $folder:\r\n";

			if(is_array($comps[$folder]['passagens'][$tipo])){
				foreach ($comps[$folder]['passagens'][$tipo] AS $wpCompDesc => $ssComp){
					foreach ($ssComp AS $ssKeyComp => $numPass){
						$hora = $comps[$folder][$tipo][$wpCompDesc][$ssKeyComp]['horarios'];
						$velocidade = $comps[$folder][$tipo][$wpCompDesc][$ssKeyComp]['velocidades'];	
						$report .= "   ".$functions->strReport($tipo.'_ss', $folder, $wpCompDesc, $ssKeyComp, $hora, $velocidade);
						$sql .= $functions->strReport($tipo.'_ss_sql', $folder, $wpCompDesc, $ssKeyComp, $hora);
					}
				}
			} else{
				$report .= "   (nenhuma)\r\n";
				$sql .= "-- nenhuma\r\n";
			}
			$report .= "\r\n";
			$sql .= "\r\n";
		}
	}


	//escrevendo o relatório parcial de perdas de waypoints
	$report .= "#### Perdas de Waypoints ##########################\r\n\r\n";	
	$sql .= "-- Perdas de Waypoints\r\n\r\n";
	foreach ($comps AS $folder => $comp){
		$report .= "   Perdas para o competidor $folder:\r\n";
		$sql .= "-- Perdas para o competidor $folder:\r\n";

		if(is_array($comps[$folder]['perdas_trechos'])){
			foreach ($comps[$folder]['perdas_trechos'] AS $wpCompDesc => $ssComp){
				foreach ($ssComp AS $ssKeyComp => $numPass){					
					$report .= "   ".$functions->strReport('perda_wp_ss', $folder, $wpCompDesc, $ssKeyComp, $parametros['lost_wp_penalty']);
					$sql .= $functions->strReport('perda_wp_ss_sql', $folder, $wpCompDesc, $ssKeyComp, $parametros['lost_wp_penalty']);
				}
			}
		} else{
			$report .= "   (nenhuma)\r\n";
			$sql .= "-- nenhuma\r\n";
		}
		$report .= "\r\n";		
		$sql .= "\r\n";	
	}

	//escrevendo o relatório parcial de perdas de carimbos
	$report .= "#### Perdas de Carimbos ###########################\r\n\r\n";
	$sql .= "-- Perdas de Carimbos\r\n\r\n";
	foreach ($comps AS $folder => $comp){
		$report .= "   Perdas para o competidor $folder:\r\n";
		$sql .= "-- Perdas para o competidor $folder:\r\n";
		
		if(is_array($comps[$folder]['perdas_carimbos_trechos'])){
			foreach ($comps[$folder]['perdas_carimbos_trechos'] AS $wpCompDesc => $ssComp){
				foreach ($ssComp AS $ssKeyComp => $numPass){
					$report .= "   ".$functions->strReport('perda_cb_ss', $folder, $wpCompDesc, $ssKeyComp, $parametros['lost_stamp_penalty']);
					$sql .= $functions->strReport('perda_cb_ss_sql', $folder, $wpCompDesc, $ssKeyComp, $parametros['lost_stamp_penalty']);
				}
			}
		} else{
			$report .= "   (nenhuma)\r\n";
			$sql .= "-- nenhuma\r\n"; 
		}
		$report .= "\r\n";		
		$sql .= "\r\n";	
	}


	//escrevendo o relatório parcial dos tipos
	for ($e=1;$e<=5;$e++){
		$tipo = "entrada".$e;
		$report .= "#### ZVC$e ########################\r\n\r\n";	
		$sql .= "-- ZVC$e\r\n\r\n";
		foreach ($comps AS $folder => $comp){
			$report .= "   ZVC$e para o competidor $folder:\r\n";
			$sql .= "-- ZVC$e para o competidor $folder:\r\n";

			if(is_array($comps[$folder]['passagens'][$tipo])){
				foreach ($comps[$folder]['passagens'][$tipo] AS $wpCompDesc => $ssComp){
					foreach ($ssComp AS $ssKeyComp => $numPass){
						$hora = $comps[$folder][$tipo][$wpCompDesc][$ssKeyComp]['horarios'];
						$velocidade = $comps[$folder][$tipo][$wpCompDesc][$ssKeyComp]['velocidades'];	
						$penalidade_por_radar = $comps[$folder][$tipo][$wpCompDesc][$ssKeyComp]['penalidade_por_radar'];
                        $penalidade_por_tempo = $comps[$folder][$tipo][$wpCompDesc][$ssKeyComp]['penalidade_por_tempo'];
                        $tempo = $comps[$folder][$tipo][$wpCompDesc][$ssKeyComp]['tempo'];
                        if($penalidade_por_radar){
							$report .= "   ".$functions->strReport('penalidade_por_radar', $folder, $wpCompDesc, $ssKeyComp, $penalidade_por_radar, $velocidade, $tempo);
							$sql .= $functions->strReport('penalidade_por_radar_sql', $folder, $wpCompDesc, $ssKeyComp, $penalidade_por_radar);
						}
						if($penalidade_por_tempo){
							$report .= "   ".$functions->strReport('penalidade_por_tempo', $folder, $wpCompDesc, $ssKeyComp, $penalidade_por_tempo, $velocidade, $tempo);
							$sql .= $functions->strReport('penalidade_por_tempo_sql', $folder, $wpCompDesc, $ssKeyComp, $penalidade_por_tempo);
						}
					}
				}
			} else{
				$report .= "   (nenhuma)\r\n";
				$sql .= "-- nenhuma\r\n";
			}
			$report .= "\r\n";
			$sql .= "\r\n";
		}
	}

	#endregion

ob_flush();
ob_start();
echo $report;
file_put_contents($parametros['report_path']."\\report.txt", ob_get_flush());

ob_flush();
ob_start();
echo $sql;
file_put_contents($parametros['report_path']."\\report.sql", ob_get_flush());


/*
ob_flush();
ob_start();
var_dump($comps[2]['passagens_largada']);
file_put_contents($parametros['report_path']."\\report.txt", ob_get_flush());
*/
?>