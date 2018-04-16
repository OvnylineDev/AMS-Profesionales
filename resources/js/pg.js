var url = "";
//events
// Wait for device API libraries to load

document.addEventListener("deviceready", phonegapReady, false);

function phonegapReady(){
     url = getURL();
     document.addEventListener("backbutton", onBackKeyDown, false);
}

function onBackKeyDown() {
     // alert(document.URL);
     // alert(location.pathname);
     // alert(window.location.pathname);
     // var url = window.location.pathname;
     // var filename = url.substring(url.lastIndexOf('/')+1);

     switch (url) {
          case "index.html":
               navigator.app.exitApp();
          break;
          case "principal.html":
               window.location.href = "index.html";
          break;
          case "listado.html":
               window.location.href = "principal.html" + "?token=" + token;
          break;
          case "menup.html":
               window.location.href = "principal.html" + "?token=" + token;
          break;
          case "llamada.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "historial.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "seguimiento.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "foto.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "planos.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "parte.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "fincita.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "val-general.html":
               window.location.href = "menup.html" + "?token=" + token +
          		"&idSerie=" + idSerie +
          		"&numeroOrden=" + numeroOrden;
          break;
          case "val-foto.html":
               alert(url);
          break;
          case "val-texto.html":
               alert(url);
          break;
          case "fest-eligeparte.html":
               alert(url);
          break;
          case "fest-foto.html":
               alert(url);
          break;
          case "fest-general.html":
               alert(url);
          break;
          case "fest-historial.html":
               alert(url);
          break;
          case "fest-parte.html":
               alert(url);
          break;
          case "fest-planos.html":
               alert(url);
          break;
          default:

     }
}

function getURL() {
     var url = window.location.pathname;
     var filename = url.substring(url.lastIndexOf('/')+1);
     return filename;
}
