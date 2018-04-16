//app strings
const NAMEAPP 		= "GESTIÓN DE INTERVENCIONES";
const NAMECOMP		= "AMS";
const VERSION		= "v0.5";

const LOCALUSER 	= "gv_usuario";
const LOCALADM 	= "gv_admin";
const LOCALIDOP	= "gv_id";
const LOCALPASS 	= "gv_clave";
const LOCALGREM	= "gv_gremio"
const LOCALSUBC	= "gv_subcontrata"
const LOCALTIMEACC 	= "gv_time";
const LOCALTOKEN 	= "gv_token";
const LOCALIDEXPEST	= "gv_idexp";
const LOCALIDINCID	= "gv_idincid";
const LOCALTIPOPARTE= "gv_tipoparte";

const TIPOEXPPEND	= "pendiente";
const TIPOEXPCOMP	= "completado";

const FILEFIRMA	= "FIRMA";
const FILEFOTO		= "FOTOS";
const FILEPLAN		= "planos";
const FILEVAL		= "valoracon economica";
const FILEVALC		= "valoracion economica";
const FILEPRES		= "PRESUPUESTOS";
const FILEPARTE	= "PARTES";
const FILEPARTEA	= "PARTE";

// $IMGTYPES=array("pjpeg"=>"image/pjpeg","svg+xml"=>"image/svg+xml","tiff"=>"image/tiff","vnd.microsoft.icon"=>"image/vnd.microsoft.icon");
const IMGTGIF = ["gif","image/gif"];
const IMGTJPEG = ["jpeg","image/jpeg"];
const IMGTPNG = ["png","image/png"];

// const APIURL		= 'https://amsconnect.ovnyline.net/.pruebasams/.YXBzYXBw/api.php';

//TEST
// const APIURL		= 'https://amsconnect.ovnyline.net/.test/.YXBzYXBw/api.php';
// const SERVERURL	= 'https://amsconnect.ovnyline.net/.test/.YXBzYXBw/';

//FINAL
const APIURL		= 'https://amsconnect.ovnyline.net/.YXBzYXBw/api.php';
const SERVERURL	= 'https://amsconnect.ovnyline.net/.YXBzYXBw/';



function gv_get_URL_parameter(sParam)
{
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++)
	{
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam)
		{
			return decodeURIComponent(sParameterName[1]);
		}
	}
}

function gv_get_localstorage(element){
	localStorage.getItem(element);
}

function gv_set_localstorage(element, value){
	localStorage.setItem(element, value);
}

function showTopNav() {

	var flag = localStorage.getItem(LOCALSUBC) === '1';

	if (!$("#sections").hasClass("responsive")) {
		$("#sections").addClass("responsive");

		if (flag) {
			$("#val").show();
			$("#val").css('display', 'block');
		}
	} else {
		$("#sections").removeClass("responsive");
		$("#val").hide();
	}

	// var x = document.getElementById("sections");
	// if (x.className === "topnav") {
	// 	x.className += " responsive";
	// } else {
	// 	x.className = "topnav";
	// }
}

function tipo_data_img(base64){
	var ext='';
	var base64ext='';

	if (base64.includes(IMGTGIF[1])) {
		ext = IMGTGIF[0];
		base64ext = IMGTGIF[1];
	}else if (base64.includes(IMGTJPEG[1])) {
		ext = IMGTJPEG[0];
		base64ext = IMGTJPEG[1];
	}else if (base64.includes(IMGTPNG[1])) {
		ext = IMGTPNG[0];
		base64ext = IMGTPNG[1];
	}else {
		return false;
	}

	var data = new Array();
	data[0]=ext;
	data[1]=base64.replace('data:'+base64ext+';base64,', '');

	return data;
}

function makefilename(cadena){
	return getCleanedString(removeSpaces(cadena)).substring(0,6);
}

function getCleanedString(cadena){
   // Definimos los caracteres que queremos eliminar
   // var specialChars = "!@#$^&%*()+=-[]\/{}|:<>?,.";
   var specialChars = "!@#$^&%*()+=[]\/{}|:<>?,.";

   // Los eliminamos todos
   for (var i = 0; i < specialChars.length; i++) {
       cadena= cadena.replace(new RegExp("\\" + specialChars[i], 'gi'), '');
   }

   // Lo queremos devolver limpio en minusculas
   cadena = cadena.toUpperCase();

   // Quitamos espacios y los sustituimos por _ porque nos gusta mas asi
   cadena = cadena.replace(/ /g,"_");

   // Quitamos acentos y "ñ". Fijate en que va sin comillas el primer parametro
   cadena = cadena.replace(/Á/gi,"A");
   cadena = cadena.replace(/É/gi,"E");
   cadena = cadena.replace(/Í/gi,"I");
   cadena = cadena.replace(/Ó/gi,"O");
   cadena = cadena.replace(/Ú/gi,"U");
   cadena = cadena.replace(/Ñ/gi,"N");
   return cadena;
}

function removeSpaces(cadena){
	return cadena.replace(" ", "");
}

function setDestinoNav(destino){
	var destinoCompleto = destino + "?token=" + token +
		"&idSerie=" + idSerie +
		"&numeroOrden=" + numeroOrden;

	if (destino == "parte.html") {
		destinoCompleto = destinoCompleto + "&parteExtra=" + false;
	}

	window.location.href = destinoCompleto;
}

function setDestinoMP(destino){
	var destinoCompleto = destino + "?token=" + token +
		"&idSerie=" + idSerie +
		"&numeroOrden=" + numeroOrden;

	if (destino == "parte.html") {
		destinoCompleto = destinoCompleto + "&parteExtra=" + false;
	}

	window.location.href = destinoCompleto;
}

function setDestinoInci(destino){
	var destinoCompleto = destino + "?token=" + token;

	window.location.href = destinoCompleto;
}

function getDestinoNav(destino){
	var destinoCompleto = destino + "?token=" + token +
		"&idSerie=" + idSerie +
		"&numeroOrden=" + numeroOrden;

	if (destino == "parte.html") {
		destinoCompleto = destinoCompleto + "&parteExtra=" + false;
	}

	return destinoCompleto;
}

function setDestinoTop(destino){
	switch (destino) {
		case "principal.html":
			window.location.href = destino + "?token=" + token;
			break;
		case "index.html":
			logout(token,localStorage.getItem(LOCALIDOP));
			break;
		case "fest-general.html":
			window.location.href = destino + "?token=" + token;
			break;
		case "reload":
			location.reload();
			break;
		default:

	}
}

// function getDestinoTop(destino){
// 	switch (destino) {
// 		case "principal.html":
// 			return destino + "?token=" + token;
// 			break;
// 		case "index.html":
// 			logout(token,localStorage.getItem(LOCALIDOP));
// 			break;
// 		case "fest-general.html":
// 			window.location.href = destino + "?token=" + token;
// 			break;
// 		case "reload":
// 			location.reload();
// 			break;
// 		default:
//
// 	}
// }

function logout(token, idop){
	var params = {
		"op": "logout",
		"token": token,
		"idOperario": idop
	};

	$.ajax({
		url: APIURL,
		data: params, //parámetros pasados
		type: 'POST',
		dataType: "json",
		crossDomain: true,
		success: function(jsonrespuesta) {
			localStorage.clear();
			window.location.href = "index.html";
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$("#info").html('<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Error de acceso. El servidor no responde.');
		},
	});
}

function convertdatesql(date){
	//Y-m-d H:i:s

	var datetxt = "";

	datetxt += date.getFullYear();
	datetxt += "-";

	var month = date.getMonth() + 1;

	datetxt += (month < 10) ? "0" + month : month;
	datetxt += "-";

	var day = date.getDate();
	datetxt += (day < 10) ? "0" + day : day;
	datetxt += " ";

	datetxt += date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();

	return datetxt;
}

function phonenumber(inputtxt){
	inputtxt = inputtxt.replace(/ /g,'')
	var phoneno = /^\d{9,11}$/;
	if(inputtxt.match(phoneno)){
		return true;
	}else{
		alert("No se puede realizar esta llamada, teléfono con formato erróneo");
		return false;
	}
}


function isImage(imgtype){

	switch (imgtype) {
		// case "image/gif":
		case "image/jpeg":
		// case "image/pjpeg":
		// case "image/svg+xml":
		// case "image/tiff":
		case "image/png":
		// case "image/vnd.microsoft.icon":
			return true;
			break;
		default:
			return false;
	}
}

// function checkMobincube(){
// 	if (navigator.appVersion.indexOf("mobincube") != -1) {
// 		// alert("true");
// 		return true;
// 	}else{
// 		// alert("false");
// 		return false;
// 	}
// }
