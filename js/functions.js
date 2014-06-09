var pt_nome = new Array();
var pcla = new Array();
var pclo = new Array();

var radarEntrada = new Array();
var radarSaida = new Array();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function apagaPTS() {
	realca(1);
	//tinyMCE.activeEditor.setContent('');
	$('#pts').tinymce().setContent('');
	pt_nome = new Array();
	pcla = new Array();
	pclo = new Array();
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getPoints(content) {
	
	realca(1);
	
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

function retornaTrac(content) {
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



///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function retornaVolta(trac,num_ponto) {
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function geraRel(content,pt_nome) {
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function geraRadares(content) {
	realca(3);
	radarEntrada = new Array();
	radarSaida = new Array();
	trac = new Array();
	trac = retornaTrac(content);
	SortIt(trac,2);	
	for (k=0;k<pt_nome.length;k++) {
		var dados = new Array();
		dados = retornaVolta(trac,k);
	}
		
	var velmax = (document.getElementById('velmax').value)*1;
	var dados_txt = "";
	
	//alert(radarEntrada.length);
	for (k=0;k<radarEntrada.length;k++) {
		var num_radares = 0;
		var num_excessos = 0;
		var maxima = 0;
		
		for (i=0;i<trac.length;i++) {
			var vel = trac[i][4]*1;
			var hora = trac[i][2]*1;
			if (hora>=radarEntrada[k] && hora<=radarSaida[k]) {
				num_radares++;
				if (vel>velmax) num_excessos++;
				if (vel>maxima) maxima = vel;
			}
		}
		dados_txt += "<b>Trecho com Radar "+eval(k+1)+":</b><br><br>Entrada:<b>"+toTime(radarEntrada[k])+"</b><br>Saída:<b>"+toTime(radarSaida[k])+"</b><br>Radares:<b>"+num_radares+"</b><br>Excessos:<b>"+num_excessos+"</b><br>Máxima:<b>"+maxima.toFixed(2)+"</b><br><br><br>";
	}
	
	
		
	$('#rel').tinymce().execCommand('mceInsertContent',false,dados_txt);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function geraRadares2(content) {
	realca(3);
	radarEntrada = new Array();
	radarSaida = new Array();
	trac = new Array();
	trac = retornaTrac(content);
	SortIt(trac,2);	
	for (k=0;k<pt_nome.length;k++) {
		var dados = new Array();
		dados = retornaVolta(trac,k);
	}
	
	
	var tadentro = false;	
	var velmax = (document.getElementById('velmax').value)*1;
	var dados_txt = "Version,212<br><br>WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0<br>USER GRID,0,0,0,0,0<br><br>";
	for (i=0;i<trac.length;i++) {
		if (trac[i][4]>=0) {
			if (trac[i][4]>=velmax) {
				var tadentro = false;
				for (k=0;k<radarEntrada.length;k++) {
					if (trac[i][2]>=radarEntrada[k] && trac[i][2]<=radarSaida[k]) tadentro = true;
				}
				if (tadentro == true) {
					dados_txt += "w,dms,";
					dados_txt += trac[i][4].toFixed(2);
					dados_txt += ",";
					dados_txt += trac[i][0]+","+trac[i][1]+","+trac[i][3]+" "+toTimeGreenwitch(trac[i][2])+","+trac[i][5]+",2,133,0,13<br>";
				}
			}
		}
	}
	dados_txt += "<br>";
	for (i=0;i<trac.length;i++) {
		if (!isNaN(trac[i][0]) && !isNaN(trac[i][2])) {
			var tadentro = false;
			for (k=0;k<radarEntrada.length;k++) {
				if (trac[i][2]>=radarEntrada[k] && trac[i][2]<=radarSaida[k]) tadentro = true;
			}
			if (trac[i][4]>=velmax && (tadentro == true)) dados_txt += "<font color=red>";
			dados_txt += "t,dms,";
			dados_txt += trac[i][0]+","+trac[i][1]+","+trac[i][3]+","+toTimeGreenwitch(trac[i][2])+","+trac[i][5];
			if (i==0) dados_txt += ",1<br>";
			else dados_txt += ",0<br>";
			if (trac[i][4]>=velmax && (tadentro == true)) dados_txt += "</font>";
		}
	}			
	$('#rel').tinymce().execCommand('mceInsertContent',false,dados_txt);
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function geraRadaresTot(content) {
	realca(3);
	tracla = new Array();
	traclo = new Array();
	trachor = new Array();
	tracvel = new Array();
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
		tracla.push(lat2);
		traclo.push(lon2);
		trachor.push(toSec(""+quebra2[5]));
		//trachor.push(quebra2[5]);
		var vel = distancia(tracla[i-1],traclo[i-1],tracla[i],traclo[i])/(trachor[i]-trachor[i-1]);
		//var vel = distancia(0,0,tracla[i],traclo[i]);
		//tracvel.push(vel);
	}
	for (i=0;i<tracla.length;i++) {
		trac[i] = new Array();
		trac[i][0]=tracla[i];
		trac[i][1]=traclo[i];
		trac[i][2]=trachor[i];
		trac[i][3]=distancia(tracla[i-1],traclo[i-1],tracla[i],traclo[i])/((trachor[i]-trachor[i-1])/3600);
		
	}
		
	var velmax = (document.getElementById('velmax').value)*1;
	var dados_txt = "";
	var num_radares = 0;
	var num_excessos = 0;
	var maxima = 0;
	var entrada = 0;
	var maxhora = 0;
	for (i=0;i<trac.length;i++) {
		var vel = trac[i][3]*1;
		var hora = trac[i][2]*1;
		//dados_txt += i+"-> "+toTime(hora)+" | "+tempo+" | "+dist.toFixed(2)+"<br>";
		//if (vel>velmax) dados_txt += i+"-> "+toTime(hora)+" | "+velmax+" | "+vel+"<br>";
		if (hora>maxhora) maxhora = hora;
		if (hora>0) {
			num_radares++;
			if (entrada==0) entrada=toTime(hora);
		}
		if (vel>velmax) {
			num_excessos++; 
		}
		if (vel>maxima) maxima = vel;

	}	
	dados_txt = "Entrada:<b>"+entrada+"</b><br>Saída:<b>"+toTime(maxhora)+"</b><br>Radares:<b>"+num_radares+"</b><br>Excessos:<b>"+num_excessos+"</b><br>Máxima:<b>"+maxima.toFixed(2)+"</b>";
		
	$('#rel').tinymce().execCommand('mceInsertContent',false,dados_txt);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function geraRadaresTot2(content) {
	realca(3);
	radarEntrada = new Array();
	radarSaida = new Array();
	trac = new Array();
	trac = retornaTrac(content);
	SortIt(trac,2);	
	for (k=0;k<pt_nome.length;k++) {
		var dados = new Array();
		dados = retornaVolta(trac,k);
	}
	
	
	var tadentro = false;	
	var velmax = (document.getElementById('velmax').value)*1;
	var dados_txt = "Version,212<br><br>WGS 1984 (GPS),217, 6378137, 298.257223563, 0, 0, 0<br>USER GRID,0,0,0,0,0<br><br>";
	for (i=0;i<trac.length;i++) {
		if (trac[i][4]>=0) {
			if (trac[i][4]>=velmax) {
				dados_txt += "w,dms,";
				dados_txt += trac[i][4].toFixed(2);
				dados_txt += ",";
				dados_txt += trac[i][0]+","+trac[i][1]+","+trac[i][3]+" "+toTimeGreenwitch(trac[i][2])+","+trac[i][5]+",2,133,0,13<br>";
			}
		}
	}
	dados_txt += "<br>";
	for (i=0;i<trac.length;i++) {
		if (!isNaN(trac[i][0]) && !isNaN(trac[i][2])) {
			if (trac[i][4]>=velmax) dados_txt += "<font color=red>";
			dados_txt += "t,dms,";
			dados_txt += trac[i][0]+","+trac[i][1]+","+trac[i][3]+","+toTimeGreenwitch(trac[i][2])+","+trac[i][5];
			if (i==0) dados_txt += ",1<br>";
			else dados_txt += ",0<br>";
			if (trac[i][4]>=velmax) dados_txt += "</font>";
		}
	}			
	$('#rel').tinymce().execCommand('mceInsertContent',false,dados_txt);
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////
function debugTrac(content) {
	realca(3);	
	dados = new Array();
	dados = retornaTrac(content);
	
	var dados_txt = "";
	for (i=0;i<trac.length;i++) {
		distancia = dados[i][5];
		dados_txt += i+"-> "+dados[i][0]+" | "+dados[i][1]+" | "+toTime(dados[i][2])+" | "+dados[i][3]+" | "+dados[i][4].toFixed(10)+" | "+distancia[0]+","+distancia[1]+","+distancia[2]+","+distancia[3]+","+distancia[4]+"<br>";
		//dados_txt += i+"-> "+dados[i][0]+" | "+dados[i][1]+" | "+dados[i][2]+" | "+dados[i][3]+" | "+dados[i][4]+" | "+dados[i][5]+"<br>";
	}	
	$('#rel').tinymce().execCommand('mceInsertContent',false,dados_txt);
}
