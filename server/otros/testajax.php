<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers');
header("Expires: 0");
header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");
header('content-type: json; charset=utf-8');
//Recogida de parámetros
if(isset($_POST) && $_POST!=null)
{
	$operation = $_POST['op'];
     $usernameop = $_POST['usuario'];
	$passop = $_POST['clave'];
}

$cadena = "";
foreach ($_POST as $key => $value) {
   $cadena .= $key . "=" . $value . " ";
}
foreach ($_GET as $key => $value) {
   $cadena .= $key . "=" . $value . " ";
}
foreach ($_REQUEST as $key => $value) {
   $cadena .= $key . "=" . $value . " ";
}

echo json_encode(array( 'status'=>'OK', 'result'=>$cadena));
return false;

?>
