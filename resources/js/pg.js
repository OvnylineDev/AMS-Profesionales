var url = "";
var camera = ["foto.html", "fest-foto.html", "fest-foto.html"];
//events
// Wait for device API libraries to load

document.addEventListener("deviceready", phonegapReady, false);

function phonegapReady(){
     url = getURL();
     document.addEventListener("backbutton", onBackKeyDown, false);

     if (camera.indexOf(url) != -1) {
          var cambutton = document.getElementById("cam");
          cambutton.addEventListener("click", openCamera, false});
     }
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
               window.location.href = "val-general.html" + "?token=" + token +
                    "&idSerie=" + idSerie +
                    "&numeroOrden=" + numeroOrden;
          break;
          case "val-texto.html":
               window.location.href = "val-general.html" + "?token=" + token +
                    "&idSerie=" + idSerie +
                    "&numeroOrden=" + numeroOrden;
          break;
          case "fest-general.html":
               window.location.href = "principal.html" + "?token=" + token;
          break;
          case "fest-foto.html":
               window.location.href = "fest-general.html" + "?token=" + token;
          break;
          case "fest-planos.html":
               window.location.href = "fest-general.html" + "?token=" + token;
          break;
          case "fest-historial.html":
               window.location.href = "fest-general.html" + "?token=" + token;
          break;
          case "fest-eligeparte.html":
               window.location.href = "fest-general.html" + "?token=" + token;
          break;
          case "fest-parte.html":
               window.location.href = "fest-general.html" + "?token=" + token;
          break;
          default:
               window.location.href = "index.html";
     }
}

function getURL() {
     var url = window.location.pathname;
     var filename = url.substring(url.lastIndexOf('/')+1);
     return filename;
}

function openCamera(){
     navigator.camera.getPicture( onSuccess, onFail, {
          quality: 50,
          destinationType : Camera.DestinationType.NATIVE_URI,
          sourceType : Camera.PictureSourceType.CAMERA,
          encodingType: Camera.EncodingType.JPEG,
          mediaType: Camera.MediaType.PICTURE,
          saveToPhotoAlbum: true
     });
}
