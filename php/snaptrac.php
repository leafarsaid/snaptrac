<?php

include_once 'functions.php';

class snaptrac{
	
	public $velmax;
	public $fuso;
	public $gate;
	public $pt_nome;
	public $radarEntrada;
	public $radarSaida;
	public $trac;
	public $functions;
	
	public function __construct(){
		
		$st = parse_ini_file("../snaptrac.ini",true);
		$this->velmax = $st['Parametros']['velmax'];
		$this->fuso = $st['Parametros']['fuso'];
		$this->gate = $st['Parametros']['gate'];
		$this->functions = new functions();
	}
/* 
	public function getPoints($content) {	
		
		quebra=content.split('\n');
		for (i=0;i<quebra.length;i++) {
			if (quebra[i]!="" && quebra[i+1]!="") {
				//alert(quebra[i]);
				pt_nome.push(quebra[i]);
				quebra2 = quebra[i+1].split(' ');			
				
				if (quebra2[0][2]!=" ") {
					if (quebra2[0][0]=="S") sinal1="-";
					else sinal1="+";
					if (quebra2[1][0]=="W") sinal2="-";
					else sinal2="+";
					pcla.push(sinal1+quebra2[0].substring(1));
					pclo.push(sinal2+quebra2[1].substring(1));
				} else{
					if (quebra2[0][0]=="S") sinal1="-";
					else sinal1="+";
					if (quebra2[2][0]=="W") sinal2="-";
					else sinal2="+";
					pcla.push(sinal1+(60*60*(quebra2[0][1]+quebra2[0][2])+60*(quebra2[1]))/3600);
					pclo.push(sinal2+(60*60*(quebra2[2][1]+quebra2[2][2])+60*(quebra2[3]))/3600);
				}
			}
		}
		var cont="";
		for (i=0;i<pcla.length;i++) {
			cont+=i+"-> ponto:"+pt_nome[i]+" lat:"+pcla[i]+"\n";
		}
		for (i=0;i<pclo.length;i++) {
			cont+=i+"-> ponto:"+pt_nome[i]+" long:"+pclo[i]+"\n";
		}
		//alert(cont);
		$('#pts').tinymce().setContent('<pre>'+cont+'</pre>');
		document.getElementById('div1').className="transparencia";
		document.getElementById('div2').className="";
		
		realca(2);
	}
	
	public function retornaTrac(content) {
		tracla = new Array();
		traclo = new Array();
		tracdata = new Array();
		trachor = new Array();
		tracdist = new Array();
		tracalt = new Array();
		trac = new Array();
		$('#rel').tinymce().setContent('');
		quebra=content.split('\n');
		//alert("\n--0-->"+quebra[0]+"\n--1-->"+quebra[1]+"\n--2-->"+quebra[2]+"\n--3-->"+quebra[3]+"\n--4-->"+quebra[4]+"\n--5-->"+quebra[5]+"\n--6-->"+quebra[6]);
		for (i=5;i<quebra.length;i++) {
			//for (i=5;i<30;i++) {
			quebra2 = quebra[i].split(',');
			var lat = quebra2[2]+"";
			var lon = quebra2[3]+"";
			if (lat[6]=="'") {
				var lat2 = (lat[0]+(parseInt(lat[1]+lat[2]) + (lat[4]+lat[5])/60 + (lat[8]+lat[9]+lat[10]+lat[11]+lat[12]+lat[13]+lat[14]+lat[15])/3600).toFixed(6));
				var lon2 = (lon[0]+(parseInt(lon[2]+lon[3]) + (lon[5]+lon[6])/60 + (lon[9]+lon[10]+lon[11]+lon[12]+lon[13]+lon[14]+lon[15]+lon[16])/3600).toFixed(6));
				lat2 = (lat2*1).toFixed(6)+"";
				lon2 = (lon2*1).toFixed(6)+"";
				//alert("lat: "+lat2+" lon: "+lon2);
			} else {
				var lat2 = lat;
				var lon2 = lon;
				//alert("lat: "+lat2+" lon: "+lon2);
			}
			//latitudes
			tracla.push(lat2);
			//longitudes
			traclo.push(lon2);
			//datas
			tracdata.push(""+quebra2[4]);
			//horas
			trachor.push(toSec(""+quebra2[5]));
			//trachor.push(quebra2[5]);
	
			//distancias
			dist = new Array();
			for (k=0;k<pcla.length;k++) {
				dist[k] = distancia(pcla[k],pclo[k],lat2,lon2);
				dist[k] = dist[k]*1000;
				if (dist[k]>document.getElementById('gate').value) {
					dist[k]=99999999;
				}
			}
			tracdist.push(dist);
			//altitudes
			tracalt.push(""+quebra2[6]);
		}
		for (i=0;i<tracla.length;i++) {
			trac[i] = new Array();
			trac[i][0]=tracla[i];
			trac[i][1]=traclo[i];
			trac[i][2]=trachor[i];
			trac[i][3]=tracdata[i];
			trac[i][4]=distancia(tracla[i-1],traclo[i-1],tracla[i],traclo[i])/((trachor[i]-trachor[i-1])/3600);
			trac[i][5]=tracdist[i];
			trac[i][6]=tracalt[i];
		}
	
		return trac;
	}
	
	public function retornaVolta(trac,num_ponto) {
		distancias = new Array();
		horarios = new Array();
	
		for (i=0;i<trac.length;i++) {
			dist_array = new Array();
			dist_array = trac[i][5];
			distancias.push(dist_array[num_ponto]);
			horarios.push(trac[i][2]);
		}
		volta = new Array();
		var temp1 = 99999999;
		var temp2 = 0;
		for (i=0;i<horarios.length;i++) {
			//se a atual linha estiver no gate
			if (!isNaN(distancias[i]) && distancias[i]!=99999999) {
				if (distancias[i]<temp1) {
					temp1=distancias[i];
					temp2=horarios[i];
				}
				//se a proxima linha NÃO estiver no gate
				if (isNaN(distancias[i+1]) || distancias[i+1]==99999999) {
					volta.push(temp2);
					if (num_ponto%2==0) radarEntrada.push(temp2);
					else radarSaida.push(temp2);
					temp1 = 99999999;
				}
			}
		}
		//alert(volta.length+"="+radarEntrada.length+"+"+radarSaida.length);
		return volta;
	}
	
	public function geraRel(content,pt_nome) {
		realca(3);
		radarEntrada = new Array();
		radarSaida = new Array();
		trac = new Array();
		trac = retornaTrac(content);
	
		SortIt(trac,2);
		var dados_txt = "";
	
		for (k=0;k<pt_nome.length;k++) {
			var dados = new Array();
			dados = retornaVolta(trac,k);
	
			dados_txt += "Relatório do ponto:";
			dados_txt += pt_nome[k];
			dados_txt += " Número de passagens:";
			dados_txt += dados.length;
			dados_txt += "<br>";
			for (i=0;i<dados.length;i++) {
				dados_txt += toTime(dados[i])+"<br>";
			}
			dados_txt += "<br><br>";
		}
	
		$('#rel').tinymce().execCommand('mceInsertContent',false,dados_txt);
	
		//alert(toTime(radarEntrada[0])+"\n"+toTime(radarEntrada[1])+"\n"+toTime(radarEntrada[2])+"\n"+toTime(radarEntrada[3])+"\n"+toTime(radarEntrada[4]));
	}
 */	
	public function geraRadares($content) {
		
		$this->radarEntrada = array();
		$this->radarSaida = array();
		$this->trac = array();
		$this->trac = $this->retornaTrac($content);
		$this->functions->SortIt($this->trac,2);
		for ($k=0;$k<count($this->trac);$k++) {
			$dados = array();
			$dados = $this->retornaVolta($this->trac,$k);
		}
		
		$dados_txt = "";
	
		for ($k=0;$k<count($this->radarEntrada);$k++) {
			$num_radares = 0;
			$num_excessos = 0;
			$maxima = 0;
	
			for ($i=0;$i<count($this->trac);$i++) {
				$vel = $this->trac[$i][4]*1;
				$hora = $this->trac[$i][2]*1;
				if ($hora>=$this->radarEntrada[$k] && $hora<=$this->radarSaida[$k]) {
					$num_radares++;
					if ($vel>$this->velmax) $num_excessos++;
					if ($vel>$maxima) $maxima = $vel;
				}
			}
			
			$dados_txt += "<b>Trecho com Radar ";
			$dados_txt += eval($k+1)+":</b><br><br>Entrada:<b>";
			$dados_txt += $this->functions->toTime($radarEntrada[$k])+"</b><br>Saída:<b>";
			$dados_txt += $this->functions->toTime($radarSaida[$k])+"</b><br>Radares:<b>";
			$dados_txt += $num_radares+"</b><br>Excessos:<b>";
			$dados_txt += $num_excessos+"</b><br>Máxima:<b>";
			$dados_txt += round($maxima,2)+"</b><br><br><br>";
		}
		
		return $dados_txt;
	}
		
	public function geraRadares2($content) {
		
		$this->radarEntrada = array();
		$this->radarSaida = array();
		$this->trac = array();
		$this->trac = retornaTrac($content);
		$this->functions->SortIt($this->trac,2);	
		for ($k=0;$k<count($this->trac);$k++){
			$dados = array();
			$dados = $this->retornaVolta($this->trac,$k);
		}		
		
		$tadentro = false;	
		$dados_txt = "Version,212<br><br>WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0<br>USER GRID,0,0,0,0,0<br><br>";
		for ($i=0;$i<$k<count($this->trac);$i++) {
			if ($this->trac[$i][4]>=0) {
				if ($this->trac[$i][4]>=$this->velmax) {
					$tadentro = false;
					for ($k=0;$k<count($this->radarEntrada);$k++) {
						if ($this->trac[$i][2]>=$this->radarEntrada[$k] && $this->trac[$i][2]<=$this->radarSaida[$k]) {
							$tadentro = true;
						}
					}
					if ($tadentro) {
						$dados_txt .= "w,dms,";
						$dados_txt .= round($this->trac[$i][4],2);
						$dados_txt .= ",";
						$dados_txt .= $this->trac[$i][0].",".$this->trac[$i][1].",".$this->trac[$i][3]." ".$this->functions->toTimeGreenwitch($this->trac[$i][2]).",".$this->trac[$i][5].",2,133,0,13<br>";
					}
				}
			}
		}
		$dados_txt += "<br>";
		for ($i=0;$i<count($this->trac);$i++) {
			if (!is_nan($this->trac[$i][0]) && !is_nan($this->trac[$i][2])) {
				$tadentro = false;
				for ($k=0;$k<count($this->radarEntrada);$k++) {
					if ($this->trac[$i][2]>=$this->radarEntrada[$k] && $this->trac[$i][2]<=$this->radarSaida[$k]){
						$tadentro = true;
					}
				}
				if ($this->trac[$i][4]>=$this->velmax && ($tadentro == true)){
					$dados_txt += "<font color=red>";
				}
				$dados_txt .= "t,dms,";
				$dados_txt .= $this->trac[$i][0].",".$this->trac[$i][1].",".$this->trac[$i][3].",".$this->functions->toTime($this->trac[$i][2],$this->fuso).",".$this->trac[$i][5];
				if ($i==0){
					$dados_txt .= ",1<br>";
				}
				else{
					$dados_txt .= ",0<br>";
				}
				if ($this->trac[$i][4] >= $this->velmax && ($tadentro == true)){
					$dados_txt .= "</font>";
				}
			}
		}			
		
		return $dados_txt;
	}
	
	public function geraRadaresTot($content) {
		
		$tracla = array();
		$traclo = array();
		$trachor = array();
		$tracvel = array();
		$this->trac = array();
		
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
		for ($i=0;$i<count($this->trac);$i++) {
			$vel = $this->trac[$i][3];
			$hora = $this->trac[$i][2];
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
		
		$dados_txt = "Entrada:<b>".$entrada."</b><br>Saída:<b>".$this->functions->toTime($maxhora)."</b><br>Radares:<b>".$num_radares."</b><br>Excessos:<b>".$num_excessos."</b><br>Máxima:<b>".round($maxima,2)."</b>";
			
		return $dados_txt;
	}

	public function geraRadaresTot2($content) {
		
		$this->radarEntrada = array();
		$this->radarSaida = array();
		$this->trac = array();
		$this->trac = $this->retornaTrac($content);
		$this->functions->SortIt($this->trac,2);	
		for ($k=0;$k<count($this->trac);$k++) {
			$dados = array();
			$dados = $this->retornaVolta($this->trac,$k);
		}	
		
		$tadentro = false;	
		$dados_txt = "Version,212<br><br>WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0<br>USER GRID,0,0,0,0,0<br><br>";
		for ($i=0;$i<count($this->trac);$i++) {
			if ($this->trac[$i][4]>=0) {
				if ($this->trac[$i][4]>=$this->velmax) {
					$dados_txt .= "w,dms,";
					$dados_txt .= $this->trac[$i][4].toFixed(2);
					$dados_txt .= ",";
					$dados_txt .= $this->trac[$i][0].",".$this->trac[$i][1].",".$this->trac[$i][3]." ".$this->functions->toTime($this->trac[$i][2],$this->fuso).",".$this->trac[$i][5].",2,133,0,13<br>";
				}
			}
		}
		$dados_txt .= "<br>";
		for ($i=0;$i<count($this->$this->trac);$i++) {
			if (!is_nan($this->trac[$i][0]) && !is_nan($this->trac[$i][2])) {
				if ($this->trac[$i][4]>=$this->velmax){
					$dados_txt .= "<font color=red>";
				}
				$dados_txt .= "t,dms,";
				$dados_txt .= $this->trac[$i][0].",".$this->trac[$i][1].",".$this->trac[$i][3].",".$this->functions->toTime($this->trac[$i][2],$this->fuso).",".$this->trac[$i][5];
				if ($i==0){
					$dados_txt .= ",1<br>";
				}
				else{
					$dados_txt .= ",0<br>";
				}
				if ($this->trac[$i][4]>=$this->velmax){
					$dados_txt .= "</font>";
				}
			}
		}
		
		return $dados_txt;
	}

}