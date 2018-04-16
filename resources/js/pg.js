//events
// Wait for device API libraries to load
//
document.addEventListener("deviceready", phonegapReady, false);

function phonegapReady(){
     document.addEventListener("backbutton", onBackKeyDown, false);
}

function onBackKeyDown() {
     alert(document.URL);
     alert(location.pathname);
     alert(window.location.pathname);



     var url = window.location.pathname;
     var filename = url.substring(url.lastIndexOf('/')+1);
     alert(filename);
}
