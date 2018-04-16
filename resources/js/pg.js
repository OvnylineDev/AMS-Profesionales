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
     alert(url);
     //
     // switch (location) {
     //      case "index.html":
     //           alert(location);
     //           // navigator.app.exitApp();
     //      break;
     //      case "principal.html":
     //           alert(location);
     //      break;
     //      case "listado.html":
     //           alert(location);
     //      break;
     //      case "menup.html":
     //           alert(location);
     //      break;
     //      case "llamada.html":
     //           alert(location);
     //      break;
     //      case "historial.html":
     //           alert(location);
     //      break;
     //      case "seguimiento.html":
     //           alert(location);
     //      break;
     //      case "foto.html":
     //           alert(location);
     //      break;
     //      case "planos.html":
     //           alert(location);
     //      break;
     //      case "parte.html":
     //           alert(location);
     //      break;
     //      case "fincita.html":
     //           alert(location);
     //      break;
     //      case "val-foto.html":
     //           alert(location);
     //      break;
     //      case "val-general.html":
     //           alert(location);
     //      break;
     //      case "val-texto.html":
     //           alert(location);
     //      break;
     //      case "fest-eligeparte.html":
     //           alert(location);
     //      break;
     //      case "fest-foto.html":
     //           alert(location);
     //      break;
     //      case "fest-general.html":
     //           alert(location);
     //      break;
     //      case "fest-historial.html":
     //           alert(location);
     //      break;
     //      case "fest-parte.html":
     //           alert(location);
     //      break;
     //      case "fest-planos.html":
     //           alert(location);
     //      break;
     //      default:
     //
     // }
}

function getURL() {
     var url = window.location.pathname;
     var filename = url.substring(url.lastIndexOf('/')+1);
     return filename;
}
