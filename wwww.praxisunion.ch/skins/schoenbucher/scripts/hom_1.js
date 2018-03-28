

<!--



 var text = new Array();

// Hier wird der Text vorgeladen!

 text[0] = "1";
 text[1] = "2";
 text[2] = "3";
 text[3] = "4";
 text[4] = "5";
 text[5] = "6";
 text[6] = "7";
 text[7] = "8";

function loadImg(i) {
  if(i < bilder.length) {
  document.images[4].src = bilder[i].src; // Beim Bild laden ist es fuer alle Browser gleich
  } else {
  alert("Ein Ladefehler ist aufgetreten!");
  }
 }
 
var c = 0;
function switchImages() {
document.images[4].src = bilder[c].src;
c++;
timer=window.setTimeout("switchImages()",3000); // Zahl gibt Intervall an (Millisec.)
if (c < bilder.length) {
} else {
c = 0;
}
}

function stoppBild() {
   if(timer != null) {
     clearTimeout(timer);
     timer=null;
  }
}

function changeText(a) {
var browser = navigator.appName;
var version = navigator.appVersion;
  if(browser == "Netscape" && document.getElementsByTagName || browser == "Microsoft Internet Explorer" && document.getElementsByTagName && version > 5) {
   document.getElementsByTagName("p")[0].firstChild.data = unescape(text[a]); // funktioniert ab IE 5 und Mozilla 6 also wennDOM unterstuetzt wird
  } else {
    if(browser == "Microsoft Internet Explorer" && document.all) {
      document.all.bildBeschrieb.innerText = unescape(text[a]); // funktioniert ab Browser IE 4
    } else {
        if(browser == "Netscape" && document.layers) {
        for(var i=0;i<text.length;i++) {
            document.layers[i].visibility = "hide"; // funktioniert fuer NC die Layer unterstuetzten
            }
            document.layers[a].visibility = "show";
        }  else {
         alert("Ihr Browser zeigt die Bildbeschriftung nicht an!"); // Browser die keine dynamischen Seiten unterstuetzen
        }
    }
  }
 }
//-->