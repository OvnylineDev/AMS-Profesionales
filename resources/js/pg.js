//events
// Wait for device API libraries to load
//
document.addEventListener("deviceready", phonegapReady, false);

function phonegapReady(){
     document.addEventListener("backbutton", onBackKeyDown, false);
}

function onBackKeyDown() {
     alert("ha pulsado back");
}
