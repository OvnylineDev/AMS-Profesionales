<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers');
// header("Expires: 0");
// header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
// header("Pragma: no-cache");
header('content-type: json; charset=utf-8');

$TIPO_USUARIOS=array(0=>"Administradores", 1=>"Operarios AMS", 2=>"Subcontratas");
$ESTADO=array(0=>"NO", 1=>"SI");

//Recogida de parámetros
$operation = '';

if(isset($_POST))
{
	$operation = $_POST['op'];
	$token = $_POST['token'];
}

switch($operation){
	case 'login':

		$usernameop = '';
		$passop = '';

		if(isset($_POST))
		{
		     $usernameop = $_POST['usuario'];
			$passop = $_POST['clave'];
		}

		include("./data/mssql.php");

		$conn = new OV_SQLConnect();

		$query = "SELECT IdOperario, Usuario, PassWord FROM TbOperarios WHERE inactivo=0 AND Usuario LIKE '".$usernameop."'";

		$getUser = $conn->query($query);

		if($getUser === false)
		{
			echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
			return false;
		}

		$rowCount = $conn->num_rows($getUser);
		if($rowCount>=1){

			if($rowCount>1){
				echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Más de un usuario con el mismo identificador' ));
				return false;
			}

			//TODO mssql_fetch_array to conn->fetcharray
			$row = mssql_fetch_array($getUser);

			if($row['Usuario']==$usernameop && $row['PassWord']==$passop){
				$token = bin2hex(openssl_random_pseudo_bytes(10));
				echo json_encode(array('status'=>'OK', 'usuario'=>$row['Usuario'], 'id'=>$row['IdOperario'], 'token'=>$token));
				// return true
			}else{
				echo json_encode(array( 'status'=>'KO', 'error'=>'3', 'message'=>'No coinciden los datos de acceso' ));
				return false;
			}
		}else{
			echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No existe el usuario' ));
			return false;
		}

		/* Free the statement and connection resources. */
		//$conn->mssql_free_statement($getUser);
          $conn->disconnect();
     break;

	case 'pendiente':

	     $idoperario = '';
	     $token = '';

	     if(isset($_POST))
	     {
	          $idoperario = $_POST['id'];
	     }

	     include("./data/mssql.php");

	     $conn = new OV_SQLConnect();

	     $query = make_query_exp("pendiente", $idoperario);

	     $getExpedientes = $conn->query($query);

	     if($getExpedientes === false)
	     {
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		//$rows = mssql_fetch_array($getExpedientes);
		if(count($getExpedientes)>0){
			$output = get_output_exp("Pendiente", $getExpedientes);

	          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
	          return true;
	     }else{
	          echo json_encode(array('status'=>'KO', 'error'=>'1', 'message'=>"No hay expedientes en pendientes"));
	          return false;
	     }

	break;

	case 'completado':

	     $idoperario = '';
	     $token = '';

	     if(isset($_POST))
	     {
	          $idoperario = $_POST['id'];
	     }

	     include("./data/mssql.php");

	     $conn = new OV_SQLConnect();

	     $query = make_query_exp("completado", $idoperario);

	     $getExpedientes = $conn->query($query);

	     if($getExpedientes === false)
	     {
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		//$rows = mssql_fetch_array($getExpedientes);
		if(count($getExpedientes)>0){
			$output = get_output_exp("Completado", $getExpedientes);

	          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
	          return true;
	     }else{
	          echo json_encode(array('status'=>'KO', 'error'=>'1', 'message'=>"No hay expedientes en completados"));
	          return false;
	     }

	break;

	case 'contactos':

		$idSerie = '';
		$numeroOrden = '';

	     if(isset($_POST))
	     {
			$idSerie = $_POST['idSerie'];
	          $numeroOrden = $_POST['numeroOrden'];
	     }

	     include("./data/mssql.php");

	     $conn = new OV_SQLConnect();

		$query = "SELECT 	expedest.FechaCita,
				          exped.Contacto AS ExpContacto,
				          exped.TelefonoContacto AS ExpTlf,
				          cdir.direccion,
				          cdir.localidad,
				          cdir.codigopostal,
						cdir.telefono AS DirTlf,
						cdir.movil AS DirMovil,
						client.Telfono AS ClienteTlf,
						client.movil AS ClienteMovil
				FROM 	TbExpedientesEstados expedest,
				          TbExpedientes exped,
				          TbClientesDirecciones cdir,
						TbClientes client
				WHERE 	cdir.IdCliente = client.IdCliente
					AND	exped.IdSerie = expedest.IdSerie
				     AND 	exped.NumeroOrden = expedest.NumeroOrden
				     AND 	exped.IdDireccion=cdir.IdDireccion
					AND 	exped.IdSerie LIKE '".$idSerie."'
					AND	exped.numeroorden LIKE '".$numeroOrden."'";

	     $getContactos = $conn->query($query);

	     if($getContactos === false)
	     {
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		$rowCount = $conn->num_rows($getContactos);

		if($rowCount==0){
			echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'No hay datos de este expediente: '. $idSerie . " " . $numeroOrden));
			return false;
		}

		if($rowCount>1){
			echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Expediente duplicado' ));
			return false;
		}

		$row = mssql_fetch_array($getContactos);

		$output = array(
			'fechaCita'		=>trim($row['FechaCita']),
			'exp_contacto'		=>trim($row['ExpContacto']),
			'exp_telefono'		=>trim($row['ExpTlf']),
			'direccion'		=>trim($row['direccion']),
			'localidad'		=>trim($row['localidad']),
			'codigoPostal'		=>trim($row['codigopostal']),
			'dir_telefono'		=>trim($row['DirTlf']),
			'dir_movil'		=>trim($row['DirMovil']),
			'client_telefono'	=>trim($row['ClienteTlf']),
			'client_movil'		=>trim($row['ClienteMovil']),
		 );

          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
          return true;

	break;

	case 'fincitamostrar':

		$idSerie = '';
		$numeroOrden = '';

	     if(isset($_POST))
	     {
			$idSerie = $_POST['idSerie'];
	          $numeroOrden = $_POST['numeroOrden'];
	     }

	     include("./data/mssql.php");

	     $conn = new OV_SQLConnect();

		$query = "SELECT 	expedest.FechaCita,
				          exped.Contacto,
				          cdir.direccion,
				          cdir.localidad,
				          cdir.codigopostal
				FROM 	TbExpedientesEstados expedest,
				          TbExpedientes exped,
				          TbClientesDirecciones cdir
				WHERE 	exped.IdSerie = expedest.IdSerie
				     AND 	exped.NumeroOrden = expedest.NumeroOrden
				     AND 	exped.IdDireccion=cdir.IdDireccion
					AND 	exped.IdSerie LIKE '".$idSerie."'
					AND	exped.numeroorden LIKE '".$numeroOrden."'";

	     $getContactos = $conn->query($query);

	     if($getContactos === false)
	     {
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		$rowCount = $conn->num_rows($getContactos);

		if($rowCount==0){
			echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'No hay datos de este expediente: '. $idSerie . " " . $numeroOrden));
			return false;
		}

		if($rowCount>1){
			echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Expediente duplicado' ));
			return false;
		}

		$row = mssql_fetch_array($getContactos);

		$output = array(
			'fechaCita'		=>trim($row['FechaCita']),
			'contacto'		=>trim($row['Contacto']),
			'direccion'		=>trim($row['direccion']),
			'localidad'		=>trim($row['localidad']),
			'codigoPostal'		=>trim($row['codigopostal'])
		 );

          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
          return true;

	break;

	case 'seguimiento':

	     $idSerie = '';
	     $numeroOrden = '';

	     if(isset($_POST))
	     {
	          $idSerie = $_POST['idSerie'];
	          $numeroOrden = $_POST['numeroOrden'];
	     }

	     include("./data/mssql.php");

	     $conn = new OV_SQLConnect();

	     $query = "SELECT 	id,
						idserie,
						numeroorden,
						IdOperario,
						Fecha,
						Observaciones
				FROM 	TbExpedientesSeguimiento
				WHERE 	IdSerie LIKE '".$idSerie."'
					AND	numeroorden LIKE '".$numeroOrden."'
				ORDER BY Fecha ASC";

	     $getExpedientes = $conn->query($query);

	     if($getExpedientes === false)
	     {
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		//$rows = mssql_fetch_array($getExpedientes);
		if(count($getExpedientes)>0){

			$output = [];

			while (($row = mssql_fetch_array($getExpedientes))){

				// $observaciones = rtftophp($row['Observaciones']);

				$output[] = array(
					'id'				=>trim($row['id']),
					'idSerie'			=>trim($row['idserie']),
					'numeroOrden'		=>trim($row['numeroorden']),
					'IdOperario'		=>trim($row['IdOperario']),
					'Fecha'			=>trim($row['Fecha']),
					'Observaciones'	=>trim($row['Observaciones'])
					// 'Observaciones'	=>$observaciones
					// 'Observaciones'	=>"<span style='font-size: 11px;'>se creo para prueba test<\/span><p>"
				 );
			}

	          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
	          return true;
	     }else{
	          echo json_encode(array('status'=>'KO', 'error'=>'1', 'message'=>"No hay expedientes en completados"));
	          return false;
	     }

	break;

	case 'nuevoseguimiento':

	     $idSerie = '';
	     $numeroOrden = '';
		$idOperario = '';
		$fecha = '';
		$observaciones = '';

	     if(isset($_POST))
	     {
	          $idSerie = $_POST['idSerie'];
	          $numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$fecha = $_POST['fecha'];
			$observaciones = $_POST['observaciones'];
	     }

	     include("./data/mssql.php");

		echo json_encode(array(	'status'=>'OK',
							'idSerie'=>$idSerie,
							'numeroOrden'=>$numeroOrden,
							'idOperario'=>$idOperario,
							'fecha'=>$fecha,
							'observaciones'=>$observaciones,
							'token'=>$token));
	break;

	default:{
          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"El servidor no procesa los datos enviados. Valores enviados -- " . $cadena));
		return false;
     }
}

function formatError($errors)
{
    /* Display errors. */
	$errores="";
    $errores .= "Error information: ";

    foreach ( $errors[0] as $key=>$error )
    {
		/*
		if($key=='SQLSTATE')
			$errores .= "  *SQLSTATE: ".$error;
		if($key=='code')
			$errores .= "  *Code: ".$error;
		*/
		if($key=='message')
			$errores .= "  *Message: ".$error;
    }
	return $errores;
}

function check_authorization()
{
	if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){

		$user = $_SERVER['PHP_AUTH_USER'];

		if($_SERVER['PHP_AUTH_PW']==$GLOBALS['xApiKey'])
			return array(0, $user);
		else
			return array(-1, 'Error ApiKey');
	}
	else
		return array(-2, 'Error Authorization');
}

function make_query_exp($tipo, $idoperario){

	$fincita = "";

	if ($tipo == "pendiente") {
		$fincita = "AND 	Fincita=0";
	}else if ("completado") {
		$fincita = "AND 	Fincita=1";
	}

	/*
	 * expedest.id, 			- ID del Expediente Estado
	 * expedest.FechaCita,		- Fecha de la cita en Expediente Estado
	 * exped.IdSerie,			- ID Serie del Expediente
	 * exped.NumeroOrden,		- Número de Orden del Expediente
	 * exped.NumeroExpediente,	- Número de Orden del Expediente				**
	 * exped.Contacto,			- Persona de contacto del Expediente
	 * exped.TelefonoContacto,	- Teléfono de contacto del Expediente			**
	 * exped.Urgente,			- Urgencia del Expediente
	 * dir.direccion,			- Dirección del Expediente en Direcciones
	 * dir.localidad,			- Localidad del Expediente en Direcciones
	 * dir.codigopostal			- Código Postal del Expediente en Direcciones
	 *
	 */

	$query = "SELECT 	expedest.id,
					expedest.FechaCita,
					exped.IdSerie,
					exped.NumeroOrden,
					exped.NumeroExpediente,
					exped.Contacto,
					exped.TelefonoContacto,
					exped.Urgente,
					dir.direccion,
					dir.localidad,
					dir.codigopostal
			FROM 	TbExpedientesEstados expedest,
					TbExpedientes exped,
					TbClientesDirecciones dir
			WHERE 	exped.IdSerie = expedest.IdSerie
				AND 	exped.NumeroOrden = expedest.NumeroOrden
				AND 	exped.IdDireccion=dir.IdDireccion
				".$fincita."
				AND 	expedest.IdOperario LIKE '".$idoperario."'";

	return $query;
}

function get_output_exp($tipo, $getExpedientes){
	$output = [];

	while (($row = mssql_fetch_array($getExpedientes))){
		$output[] = array(
			'id'				=>trim($row['id']),
			'idSerie'			=>trim($row['IdSerie']),
			'numeroOrden'		=>trim($row['NumeroOrden']),
			'numeroExpediente'	=>trim($row['NumeroExpediente']),
			'fechaCita'		=>trim($row['FechaCita']),
			'contacto'		=>trim($row['Contacto']),
			'telefono'		=>trim($row['TelefonoContacto']),
			'direccion'		=>trim($row['direccion']),
			'localidad'		=>trim($row['localidad']),
			'codigoPostal'		=>trim($row['codigopostal']),
			'urgente'			=>trim($row['Urgente']),
			'status'			=>$tipo
		 );
	}

	return $output;
}

// function rtftophp($rtf){
// 	include("./resources/rtf-html.php");
// 	$reader = new RtfReader(); // or use a string
//      $reader->Parse($rtf);
//      //$reader->root->dump(); // to see what the reader read
// 	$formatter = new RtfHtml();
//      return $formatter->Format($reader->root);
//
// }
?>
