////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function ajaxLoad(content,id) {	
	var ed = tinyMCE.get(id);
	// Do you ajax call here, window.setTimeout fakes ajax call
	ed.setProgressState(1); // Show progress
	window.setTimeout(function() {
		ed.setProgressState(0); // Hide progress
		ed.setContent('<pre>'+content+'</pre>');
		realca(3);
	}, 500);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function distancia(pt1_la,pt1_lo,pt2_la,pt2_lo) {
	var arco1 = 90 - pt1_la;
	var arco2 = 90 - pt2_la;
	var diff = Math.abs(Math.abs(pt2_lo)-Math.abs(pt1_lo));
	var pre_dista = Math.cos(toGrad(arco1))*Math.cos(toGrad(arco2));
	var pre_distb = Math.sin(toGrad(arco1))*Math.sin(toGrad(arco2))*Math.cos(toGrad(diff));
	var pre_dist = pre_dista + pre_distb;
	var pre_dist2 = toRad(Math.acos(pre_dist));
	var dist = (40030 * pre_dist2)/360;
	
	return dist;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function toGrad(num) {
	var grads = num * (Math.PI / 180);	
	return grads;
}

function toRad(num) {
	var rads = num * (180 / Math.PI);	
	return rads;
}

function toSec(num) {
	var arrsec = num.split(":");
	var tosec = (arrsec[0]*3600+arrsec[1]*60+arrsec[2]);
	
	return tosec;
}

function toHour(num) {
	var arrsec = num.split(":");
	var tosec = (arrsec[0]*3600+arrsec[1]*60+arrsec[2]);
	var tohour = tosec/3600;
	
	return tohour;
}

function toTime(secs) {
   var hh = parseInt(secs/360000);
   if(hh < 1){
      hh = '00';
   }
   else if(hh < 10){
	  hh = '0' + hh;
   }
   var tmp = ((secs/360000) - hh)*3600;
   
   var mm = Math.round(tmp/60);
   if(mm < 1){
      mm = '00';
   }
   else if(mm < 10){
	  mm = '0' + mm;
   }
   var tmp = Math.round(secs)+""; 
   
   var ss = tmp.substring(5,7);
   var hora = ((hh*1)+(document.getElementById('fuso').value*1));
   
   return hora+":"+mm+":"+ss;
   //return secs;
}

function toTimeGreenwitch(secs) {
   var hh = parseInt(secs/360000);
   if(hh < 1){
      hh = '00';
   }
   else if(hh < 10){
	  hh = '0' + hh;
   }
   var tmp = ((secs/360000) - hh)*3600;
   
   var mm = Math.round(tmp/60);
   if(mm < 1){
      mm = '00';
   }
   else if(mm < 10){
	  mm = '0' + mm;
   }
   var tmp = Math.round(secs)+""; 
   
   var ss = tmp.substring(5,7);
   var hora = ((hh*1));
   
   return hora+":"+mm+":"+ss;
   //return secs;
}

function debug() {
	//console.log("123");
	//alert(distancia(-20.560688,-48.657530,-20.551805,-48.668417)+"km");	
	//alert(distancia(-20.560688,-48.657530,-22.902778,-43.206667)+"km");		
	//alert(distancia(-22.902778 ,-43.206667,-23.548333,-46.636111)+"km");	
	//var num = 22.123;
	//alert(num.toFixed(6));
}

function realca(div) {
	
	if (div==1) {
		dv1=2;
		dv2=3
		dv3=1;
	}	
	if (div==2) {
		dv1=3;
		dv2=1
		dv3=2;
	}	
	if (div==3) {
		dv1=1;
		dv2=2
		dv3=3;
	}	
	document.getElementById('div'+dv1).className="transparencia";
	document.getElementById('div'+dv2).className="transparencia";
	document.getElementById('div'+dv3).className="";
	
}

//////////////////////////////////////////////////////////////////////////////////////////////////

// GENERAL SORT FUNCTION:
// Sort on single or multi-column arrays.
// Sort set up for six colums, in order of u,v,w,x,y,z.   For single columns (single-dimensioned array), omit all u,v....
// Sort will continue only as far as the specified number of columns: "w,x" only sorts on two columns, etc.
// Sort will place numbers before strings, and swap until all columns are in ascending order.
// Sorter algorithm:
// Is result of a-b NaN?.  Then one or both is text.
//   Are both text?  Then do a general swap. Set var 'swap' to 1:0:-1, accordingly: 1 push up list, -1 push down.
//   Else one is text, the other a number.  Therefore, is 'a' text?  Then push up, else 'b' is text - push 'a' down.
// Else both are numbers.
// return result in var 'swap'.
// To do multi-columns, repeat the operations for each column.
// To do ascending.descending, asending, etc columns, see the code further down the page.

function SortIt(TheArr,u,v,w,x,y,z){

  if(u==undefined){TheArr.sort(Sortsingle);} // this is a simple array, not multi-dimensional, ie, SortIt(TheArr);
  else{TheArr.sort(Sortmulti);}

  function Sortsingle(a,b){
    var swap=0;
    if(isNaN(a-b)){
      if((isNaN(a))&&(isNaN(b))){swap=(b<a)-(a<b);}
      else {swap=(isNaN(a)?1:-1);}
    }
    else {swap=(a-b);}
    return swap;
  }

 function Sortmulti(a,b){
  var swap=0;
    if(isNaN(a[u]-b[u])){
      if((isNaN(a[u]))&&(isNaN(b[u]))){swap=(b[u]<a[u])-(a[u]<b[u]);}
      else{swap=(isNaN(a[u])?1:-1);}
    }
    else{swap=(a[u]-b[u]);}
    if((v==undefined)||(swap!=0)){return swap;}
    else{
      if(isNaN(a[v]-b[v])){
        if((isNaN(a[v]))&&(isNaN(b[v]))){swap=(b[v]<a[v])-(a[v]<b[v]);}
        else{swap=(isNaN(a[v])?1:-1);}
      }
      else{swap=(a[v]-b[v]);}
      if((w==undefined)||(swap!=0)){return swap;}
      else{
        if(isNaN(a[w]-b[w])){
          if((isNaN(a[w]))&&(isNaN(b[w]))){swap=(b[w]<a[w])-(a[w]<b[w]);}
          else{swap=(isNaN(a[w])?1:-1);}
        }
        else{swap=(a[w]-b[w]);}
        if((x==undefined)||(swap!=0)){return swap;}
        else{
          if(isNaN(a[x]-b[x])){
            if((isNaN(a[x]))&&(isNaN(b[x]))){swap=(b[x]<a[x])-(a[x]<b[x]);}
            else{swap=(isNaN(a[x])?1:-1);}
          }
          else{swap=(a[x]-b[x]);}
          if((y==undefined)||(swap!=0)){return swap;}
          else{
            if(isNaN(a[y]-b[y])){
              if((isNaN(a[y]))&&(isNaN(b[y]))){swap=(b[y]<a[y])-(a[y]<b[y]);}
              else{swap=(isNaN(a[y])?1:-1);}
            }
            else{swap=(a[y]-b[y]);}
            if((z=undefined)||(swap!=0)){return swap;}
            else{
              if(isNaN(a[z]-b[z])){
                if((isNaN(a[z]))&&(isNaN(b[z]))){swap=(b[z]<a[z])-(a[z]<b[z]);}
                else{swap=(isNaN(a[z])?1:-1);}
              }
              else{swap=(a[z]-b[z]);}
              return swap;
} } } } } } }
