<?php
require_once "resources/dompdf/autoload.inc.php";
// reference the Dompdf namespace
use Dompdf\Dompdf;


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers');
// header("Expires: 0");
// header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
// header("Pragma: no-cache");
header('content-type: json; charset=utf-8');

define('UPLOAD_DIR', './tmp/');
// $UPLOAD_DIR = "./tmp/";
define('FTPDEF', 'ORDENES');
// $FTPDEF = "ORDENES ";
define('JSON_FOLDER', './data/json/');
// $JSONFOLDER = "./data/json/";
//2017-12-14 16:47:54.243
// $DATEFORSQL='Y-m-d H:i:s';
define('DATE_FOR_SQL', 'Y-m-d H:i:s');
// $DATEFORCLI='d-m-y | H:i:s';
define('DATE_FOR_CLI', 'd-m-y | H:i:s');
// $DATEDOW='N';
define('$DATE_DOW', 'N');

// # define constant, serialize array
// define ("FRUITS", serialize (array ("apple", "cherry", "banana")));
//
// # use it
// $my_fruits = unserialize (FRUITS);

$TIPO_USUARIOS=array(0=>"Administradores", 1=>"Operarios AMS", 2=>"Subcontratas");

$ESTADO=array(0=>"NO", 1=>"SI");

$IMGTYPES=array("gif"=>"image/gif","jpeg"=>"image/jpeg","pjpeg"=>"image/pjpeg","png"=>"image/png","svg+xml"=>"image/svg+xml","tiff"=>"image/tiff","vnd.microsoft.icon"=>"image/vnd.microsoft.icon");

$DIASEM=array(1=>"Lunes", 2=>"Martes", 3=>"Miércoles", 4=>"Jueves", 5=>"Viernes", 6=>"Sábado", 7=>"Domingo");

$JSON=array("admin"=>"272dd9f801", "operario"=>"5db655c116", "seguimiento"=>"41f72cba96", "fichero"=>"68f03a0308");

//Recogida de parámetros
$operation = '';

if(isset($_POST))
{
	$operation = $_POST['op'];
	$token = $_POST['token'];
}

switch($operation){
	/*
	 * Operation: login
	 * Source: index.html
	 * Parameters:
	 * 	usuario - usuario
	 * 	clave - clave
	 * Rerturn:
	 * 	'usuario'=>$row['Usuario'],
	 * 	'id'=>$row['IdOperario'],
	 * 	'gremio' =>$row['Gremio'],
	 * 	'subcont' =>$row['Subcontrata'],
	 * 	'token'=>$token
	 * Description: el origen envia unas claves de usuario y el sistema las valida.
	 * Message Return:
	 * 	OK
	 * 	KO - error 1 - Database error
	 * 	KO - error 2 - Más de un usuario con el mismo identificador
	 * 	KO - error 3 - No coinciden los datos de acceso
	 * 	KO - error 4 - No existe el usuario
	 */
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

		$query = "SELECT IdOperario, Usuario, PassWord, Gremio, Subcontrata FROM TbOperarios WHERE inactivo=0 AND Usuario LIKE '".$usernameop."'";

		$getUser = $conn->query($query);

		if($getUser === false)
		{
			//Error 1 - Base de datos
			echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
			return false;
		}

		$rowCount = $conn->num_rows($getUser);
		if($rowCount>=1){

			if($rowCount>1){
				//Error 2 - Usuarios duplicados
				echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Más de un usuario con el mismo identificador' ));
				return false;
			}

			//TODO mssql_fetch_array to conn->fetcharray
			$row = mssql_fetch_array($getUser);

			if($row['Usuario']==$usernameop && $row['PassWord']==$passop){
				$token = bin2hex(openssl_random_pseudo_bytes(10));

				// Añadir al json la información
				$arr_op = array(
					"token" => $token,
					"IdOperario" => $row['IdOperario'],
					"nombre" => $row['Usuario'],
					"gremio" => $row['Gremio'],
					"subcont" => $row['Subcontrata'],
					"Fecha" => date(DATE_FOR_SQL)
				);

				writeJSON($arr_op, "operario");

				echo json_encode(array(
					'status'=>'OK',
					'usuario'=>$row['Usuario'],
					'id'=>$row['IdOperario'],
					'gremio' =>$row['Gremio'],
					'subcont' =>$row['Subcontrata'],
					'token'=>$token
				));
				// return true
			}else{
				//Error 3 - Datos de acceso erróneos
				echo json_encode(array( 'status'=>'KO', 'error'=>'3', 'message'=>'No coinciden los datos de acceso' ));
				return false;
			}
		}else{
			//Error 4 - No existe el usuario
			echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No existe el usuario' ));
			return false;
		}

		/* Free the statement and connection resources. */
		//$conn->mssql_free_statement($getUser);
          $conn->disconnect();
     break;

	/*
	 * Operation: pendiente
	 * Source: listado.html
	 * Parameters:
	 * 	idoperario - id
	 * 	token - token
	 * Rerturn:
	 * 	'items'=>array,
	 * 		'id'=>trim($row['id']),
	 * 		'idSerie'=>trim($row['IdSerie']),
	 * 		'numeroOrden'=>trim($row['NumeroOrden']),
	 * 		'numeroExpediente'=>trim($row['NumeroExpediente']),
	 * 		'fechaCita'=>trim($row['FechaCita']),
	 * 		'contacto'=>trim($row['Contacto']),
	 * 		'telefono'=>trim($row['TelefonoContacto']),
	 * 		'direccion'=>trim($row['direccion']),
	 * 		'localidad'=>trim($row['localidad']),
	 * 		'codigoPostal'=>trim($row['codigopostal']),
	 * 		'urgente'=>trim($row['Urgente']),
	 * 		'status'=>pendiente
	 * 	'token'=>$token
	 * Description: Devuelve todos los expedientes abiertos de un operario - pendientes
	 * Message Return:
	 * 	OK
	 * 	KO - error 1 - Database error
	 * 	KO - error 5 - No hay expedientes en pendientes
	 */
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
			//Error 1 - Base de datos
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		//$rows = mssql_fetch_array($getExpedientes);
		if($conn->num_rows($getExpedientes)>0){
			$output = get_output_exp("Pendiente", $getExpedientes);

	          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
	          return true;
	     }else{
			//Error 5 - No hay expedientes que mostrar
	          echo json_encode(array('status'=>'KO', 'error'=>'5', 'message'=>"No hay expedientes en pendientes"));
	          return false;
	     }

	break;

	/*
	 * Operation: completado
	 * Source: listado.html
	 * Parameters:
	 * 	idoperario - id
	 * 	token - token
	 * Rerturn:
	 * 	'items'=>$output[],
	 * 		'id'				=>trim($row['id']),
	 * 		'idSerie'			=>trim($row['IdSerie']),
	 * 		'numeroOrden'		=>trim($row['NumeroOrden']),
	 * 		'numeroExpediente'	=>trim($row['NumeroExpediente']),
	 * 		'fechaCita'		=>trim($row['FechaCita']),
	 * 		'contacto'		=>trim($row['Contacto']),
	 * 		'telefono'		=>trim($row['TelefonoContacto']),
	 * 		'direccion'		=>trim($row['direccion']),
	 * 		'localidad'		=>trim($row['localidad']),
	 * 		'codigoPostal'		=>trim($row['codigopostal']),
	 * 		'urgente'			=>trim($row['Urgente']),
	 * 		'status'			=>completado
	 * 	'token'=>$token
	 * Description: Devuelve todos los expedientes abiertos de un operario - completado
	 * Message Return:
	 * 	OK
	 * 	KO - error 1 - Database error
	 * 	KO - error 4 - No hay expedientes en completados
	 */
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
		if($conn->num_rows($getExpedientes)>0){
			$output = get_output_exp("Completado", $getExpedientes);

	          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
	          return true;
	     }else{
			//Error 4 - No hay expedientes que mostrar
	          echo json_encode(array('status'=>'KO', 'error'=>'4', 'message'=>"No hay expedientes en completados"));
	          return false;
	     }

	break;

	/*
	 * Operation: contactos
	 * Source: llamada.html
	 * Parameters:
	 * 	idExpEst - idExpEst;
	 * 	token - token
	 * Rerturn:
	 * 	'items'=>$output[]
	 * 		'fechaCita'		=>$fechaCita,
	 * 		'exp_contacto'		=>trim($row['ExpContacto']),
	 * 		'exp_telefono'		=>trim($row['ExpTlf']),
	 * 		'direccion'		=>trim($row['direccion']),
	 * 		'localidad'		=>trim($row['localidad']),
	 * 		'codigoPostal'		=>trim($row['codigopostal']),
	 * 		'dir_telefono'		=>trim($row['DirTlf']),
	 * 		'dir_movil'		=>trim($row['DirMovil']),
	 * 		'client_telefono'	=>trim($row['ClienteTlf']),
	 * 		'client_movil'		=>trim($row['ClienteMovil']),
	 * 	'token'=>$token
	 * Description: Devuelve todos los datos de un expediente en concreto
	 * Message Return:
	 * 	OK
	 * 	KO - error 1 - Database error
	 * 	KO - error 2 - Expediente duplicado
	 * 	KO - error 4 - No hay datos de este expediente que tengan relación con el operario
	 */
	case 'contactos':

		$idExpEst = '';
		$token = '';

	     if(isset($_POST))
	     {
	          $idExpEst = $_POST['idExpEst'];
			$token = $_POST['token'];
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
					AND	expedest.id LIKE '".$idExpEst."'";

	     $getContactos = $conn->query($query);

	     if($getContactos === false)
	     {
			//Error 1 - Base de datos
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		$rowCount = $conn->num_rows($getContactos);

		if($rowCount==0){
			//Error 4 - La búsqueda no da resultados
			echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No hay datos de este expediente que tengan relación con el operario' . $IdOperario));
			return false;
		}

		if($rowCount>1){
			//Error 2 - La búsqueda da varios resultados
			echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Expediente duplicado: '.$rowCount ));
			return false;
		}

		$row = mssql_fetch_array($getContactos);

		$date = new DateTime(trim($row['FechaCita']));
		$fechaCita = $DIASEM[$date->format($DATE_DOW)] . ", " . $date->format(DATE_FOR_CLI);

		$output = array(
			'fechaCita'		=>$fechaCita,
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

	/*
	 * Operation: rellenarparte
	 * Source: parte.html
	 * Parameters:
	 * 	idExpEst - idExpEst;
	 * 	token - token
	 * Rerturn:
	 * 	'items'=>$output[]
	 * 		'fechaCita'			=>$fechaCita,
	 *		 'NumeroOrden'			=>trim($row['NumeroOrden']),
	 *		 'NumeroExpediente'		=>trim($row['NumeroExpediente']),
	 *		 'NumeroPoliza'		=>trim($row['NumeroPoliza']),
	 *		 'exp_contacto'		=>trim($row['ExpContacto']),
	 *		 'exp_telefono'		=>trim($row['ExpTlf']),
	 *		 'nombrePerjudicado'	=>trim($row['perjudicado']),
	 *		 'DirPerjudicado'		=>trim($row['DireccionPerjudicado']),
	 *		 'Observaciones'		=>trim($row['Observaciones']),
	 *		 'ObservPerjudicado'	=>trim($row['ObservacionesPerjudicado']),
	 *		 'direccion'			=>trim($row['direccion']),
	 *		 'localidad'			=>trim($row['localidad']),
	 *		 'codigoPostal'		=>trim($row['codigopostal']),
	 *		 'dir_telefono'		=>trim($row['DirTlf']),
	 *		 'dir_movil'			=>trim($row['DirMovil']),
	 *		 'client_nombre'		=>trim($row['Nombre']),
	 *		 'client_cif'			=>trim($row['Cif']),
	 *		 'client_telefono'		=>trim($row['ClienteTlf']),
	 *		 'client_movil'		=>trim($row['ClienteMovil']),
	 * 	'token'=>$token
	 * Description: Devuelve todos datos de un expediente necesarios para rellenar un parte
	 * Message Return:
	 * 	OK
	 * 	KO - error 1 - Database error
	 * 	KO - error 2 - Expediente duplicado
	 * 	KO - error 4 - No hay datos de este expediente que tengan relación con el operario
	 */
	case 'rellenarparte':

		$idExpEst = '';
		$token = '';

	     if(isset($_POST))
	     {
			$idExpEst = $_POST['idExpEst'];
			$token = $_POST['token'];
	     }

	     include("./data/mssql.php");

	     $conn = new OV_SQLConnect();

		$query = "SELECT 	expedest.FechaCita,
						exped.NumeroOrden,
						exped.NumeroExpediente,
						exped.NumeroPoliza,
				          exped.Contacto AS ExpContacto,
				          exped.TelefonoContacto AS ExpTlf,
						exped.perjudicado,
						exped.DireccionPerjudicado,
						exped.Observaciones,
						exped.ObservacionesPerjudicado,
				          cdir.direccion,
				          cdir.localidad,
				          cdir.codigopostal,
						cdir.telefono AS DirTlf,
						cdir.movil AS DirMovil,
						client.Nombre,
						client.Cif,
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
					AND	expedest.id LIKE '".$idExpEst."'";

	     $getContactos = $conn->query($query);

	     if($getContactos === false)
	     {
			//Error 1 - Base de datos
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		$rowCount = $conn->num_rows($getContactos);

		if($rowCount==0){
			//Error 4 - La búsqueda no da resultados
			echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No hay datos de este expediente que tengan relación con el operario' . $IdOperario));
			return false;
		}

		if($rowCount>1){
			//Error 2 - La búsqueda da varios resultados
			echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Expediente duplicado: '.$rowCount ));
			return false;
		}

		$row = mssql_fetch_array($getContactos);

		$date = new DateTime(trim($row['FechaCita']));
		$fechaCita = $DIASEM[$date->format($DATE_DOW)] . ", " . $date->format(DATE_FOR_CLI);

		$output = array(
			'fechaCita'		=>$fechaCita,
			'NumeroOrden'		=>trim($row['NumeroOrden']),
			'NumeroExpediente'	=>trim($row['NumeroExpediente']),
			'NumeroPoliza'		=>trim($row['NumeroPoliza']),
			'exp_contacto'		=>trim($row['ExpContacto']),
			'exp_telefono'		=>trim($row['ExpTlf']),
			'nombrePerjudicado'	=>trim($row['perjudicado']),
			'DirPerjudicado'	=>trim($row['DireccionPerjudicado']),
			'Observaciones'	=>trim($row['Observaciones']),
			'ObservPerjudicado'	=>trim($row['ObservacionesPerjudicado']),
			'direccion'		=>trim($row['direccion']),
			'localidad'		=>trim($row['localidad']),
			'codigoPostal'		=>trim($row['codigopostal']),
			'dir_telefono'		=>trim($row['DirTlf']),
			'dir_movil'		=>trim($row['DirMovil']),
			'client_nombre'	=>trim($row['Nombre']),
			'client_cif'		=>trim($row['Cif']),
			'client_telefono'	=>trim($row['ClienteTlf']),
			'client_movil'		=>trim($row['ClienteMovil']),
		 );

          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
          return true;

	break;

	/*
	 * Operation: fincitamostrar
	 * Source: fincita.html
	 * Parameters:
	 * 	idSerie - idSerie;
	 * 	numeroOrden - numeroOrden;
	 * 	token - token
	 * Rerturn:
	 * 	'fechaCita'		=>$fechaCita,
	 * 	'contacto'		=>trim($row['Contacto']),
	 * 	'direccion'		=>trim($row['direccion']),
	 * 	'localidad'		=>trim($row['localidad']),
	 *  	'codigoPostal'		=>trim($row['codigopostal'])
	 * Description: Muestra los datos de una cita para finalizarla
	 * Message Return:
	 * 	OK
	 * 	KO - error 1 - Database error
	 * 	KO - error 2 - Expediente duplicado
	 * 	KO - error 4 - No hay datos de este expediente
	 *
	 *
	 * Sin token
	 */
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
			//Error 1 - Base de datos¡
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		$rowCount = $conn->num_rows($getContactos);

		if($rowCount==0){
			//Error 4 - La búsqueda no da resultados
			echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No hay datos de este expediente: '. $idSerie . " " . $numeroOrden));
			return false;
		}

		if($rowCount>1){
			//Error 2 - La búsqueda da varios resultados
			echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Expediente duplicado' ));
			return false;
		}

		$row = mssql_fetch_array($getContactos);

		$date = new DateTime(trim($row['FechaCita']));
		$fechaCita = $DIASEM[$date->format($DATE_DOW)] . ", " . $date->format(DATE_FOR_CLI);

		$output = array(
			'fechaCita'		=>$fechaCita,
			'contacto'		=>trim($row['Contacto']),
			'direccion'		=>trim($row['direccion']),
			'localidad'		=>trim($row['localidad']),
			'codigoPostal'		=>trim($row['codigopostal'])
		 );

          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
          return true;

	break;

	// case 'fincita':
	// 	$idSerie = '';
	// 	$numeroOrden = '';
	// 	$idOperario = '';
	// 	$filename = '';
	// 	$firma = '';
	// 	$duracioncita = '';
	//
	// 	if(isset($_POST))
	// 	{
	// 		$idSerie = $_POST['idSerie'];
	// 		$numeroOrden = $_POST['numeroOrden'];
	// 		$idOperario = $_POST['idOperario'];
	// 		$filename = $_POST['filename'];
	// 		$firma = $_POST['firma'];
	// 		$duracioncita = $_POST['duracioncita'];
	// 	}
	//
	// 	$fileurl = saveimg($firma, $filename);
	//
	// 	if($fileurl!=false){
	// 		include("./data/ftp.php");
	// 		$conn = new OV_FTPConnect();
	// 		if ($idSerie=='O') {
	// 			$folder = $FTPDEF . $numeroOrden;
	// 		}else{
	// 			$folder = $FTPDEF . $idSerie . $numeroOrden;
	// 		}
	//
	//
	// 	}
	//
	// 	// include("./data/ftp.php");
	// 	//
	// 	// $conn = new OV_FTPConnect();
	//
	// 	// $folder = "ORDENES " .
	// 	//
	// 	// $funciona = $conn->query($query);
	//
	// 	$funciona = true;
	//
	// 	if ($funciona) {
	// 		echo json_encode(array(	'status'=>'OK','message'=>"Sección en desarrollo"));
	// 	}else{
	// 		echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al registrar seguimiento: ". utf8_encode($conn->see_errors())));
	// 	}
	// break;

	/*
	 * Operation: subirarch
	 * Source: foto.html
	 * 		 planos.html
	 * Parameters:
	 * 	idSerie - idSerie;
	 * 	numeroOrden - numeroOrden;
	 * 	idOperario - idOperario;
	 * 	filenames - filenames;
	 * 	filedescs - filedescs;
	 * 	filedata - filedata;
	 * 	filext - filext;
	 * 	tipofichero - tipofichero;
	 * 	token - token
	 * Rerturn:
	 * 	'message'
	 * Description: Sube un archivo al FTP y lo registra en la BD
	 * Message Return:
	 * 	OK - Se han subido ** archivos al la carpeta ** del FTP
	 * 	KO - error 1 - Database Error¿?
	 * 	KO - error 5 - Problemas a subir archivos al ftp: ***
	 * 	KO - error 6 - No se han enviado ficheros
	 *
	 *
	 * Sin token
	 */
	case 'subirarch':
		$idSerie = '';
		$numeroOrden = '';
		$idOperario = '';
		$filenames = '';
		$filedescs = '';
		$filedata = '';
		$filext = '';
		$tipofichero = '';

		if(isset($_POST))
		{
			$idSerie = $_POST['idSerie'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$filenames = $_POST['filenames'];
			$filedescs = $_POST['filedescs'];
			$filedata = $_POST['filedata'];
			$filext = $_POST['filext'];
			$tipofichero = $_POST['tipofichero'];
		}


		$fileurls = array();
		$filetxts = array();

		for ($i=0; $i < count($filedata); $i++) {
			$folders = array($idOperario, FTPDEF . " " . $numeroOrden);
			$nameftp = $filenames[$i] . "." . $filext[$i];
			$fileurls[$nameftp] = saveimg($folders, $filedata[$i], $filenames[$i], $filext[$i]);
			$filetxts[$nameftp] = $filedescs[$i];
		}

		$message = "";

		if (count($fileurls)>0) {
			include("./data/ftp.php");
			include("./data/mssql.php");
			$remote_folder = "/" . FTPDEF . " " . $numeroOrden . "/";
			$connftp = new OV_FTPConnect();
			$conndb = new OV_SQLConnect();

			foreach ($fileurls as $remote_file => $local_file) {
				if($local_file){

					// $fp = fopen($file, 'r');
					//$remote_folder, $remote_file, $local_file, $binary
					if(!$connftp->ov_ftp_upload($remote_folder, $remote_file, $local_file, true)){
						$message .= "No se ha conseguido subir el archivo " . $remote_folder . ". " . $remote_file . ". " . $local_file . ". ";
					}else{
						// ingresarFichero($idSerie, $NumOrden, "Fotografía ", $ruta, $tipoFichero, $idOperario);
						$winfolder = "Z:" . str_replace('/', '\\', $remote_folder);

						$descr = "Fotografía: ".$filetxts[$remote_file];

						$query = makeQueryFich($idSerie, $numeroOrden, $descr, $winfolder.$remote_file, $tipofichero, $idOperario);

						$funciona = $conndb->query($query);

						if (!$funciona) {
							$message .= "No se ha conseguido registrar el fichero ". utf8_encode($conndb->see_errors());
						}else{
							// Añadir al json la información
							$rutaRemota = "" . $winfolder . $remote_file;

							saveFich($idSerie, $numeroOrden, $idOperario, $descr, $rutaRemota, $local_file, $tipofichero);

						}
					}
					// fclose($fp);
				}else{
					$message .= "Ha ocurrido un error en el servidor - No se detectan archvios subidos";
				}
			}

			$connftp->ov_ftp_close();
			// $conndb->disconnect();

			if ($message=="") {
				$message = "";

				if (count($fileurls)>1) {
					$message = "Subidos ". count($fileurls) . " archivos del tipo " . strtolower($tipofichero);
				}else{
					$message = "Subido ". count($fileurls) . " archivo del tipo " . strtolower($tipofichero);
				}

				$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $message);
				$funcionaseg = $conndb->query($query);

				// $funcionaseg = false;
				if ($funcionaseg) {
					$conndb->disconnect();

					saveSeg($idSerie, $numeroOrden, $idOperario, $message);

					echo json_encode(array('status'=>'OK', 'message'=>'Se han subido ' . count($fileurls) . ' archivos al la carpeta '.$folder.' del FTP'));
			          return true;
				}else{
					$conndb->disconnect();

					echo json_encode(array('status'=>'OK', 'message'=>'NO SEG - Se han subido ' . count($fileurls) . ' archivos al la carpeta '.$folder.' del FTP'));
			          return false;
				}
			}else{
				$conndb->disconnect();

				echo json_encode(array( 'status'=>'KO', 'error'=>'10', 'message'=>"Problemas a subir archivos al ftp: ". $message));
				return false;
			}
		}else{
			$conndb->disconnect();

			echo json_encode(array( 'status'=>'KO', 'error'=>'10', 'message'=>"No se han enviado ficheros"));
			return false;
		}

		break;

	case 'crearparte':
		$idSerie = '';
		$numeroOrden = '';
		$idOperario = '';
		$pdfname = '';
		$pdfdata = '';
		$firmopdata = '';
		$firmopname = '';
		$firmocldata = '';
		$firmclname = '';
		$tipofichero = '';

		if(isset($_POST))
		{
			$idSerie = $_POST['idSerie'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$pdfname = $_POST['pdfname'];
			$pdfdata = str_replace('+', ' ', $_POST['pdfdata']);
			$firmopdata = $_POST['firmopdata'];
			$firmopname = $_POST['firmopname'];
			$firmocldata = $_POST['firmocldata'];
			$firmclname = $_POST['firmclname'];
			$firmclname = $_POST['firmclname'];
			$tipofichero = $_POST['tipofichero'];
		}

		$pdfurl = "";
		$fopurl = "";
		$fclurl = "";

		$folders = array($idOperario, FTPDEF . " " . $numeroOrden);

		$fopurl = saveimg($folders, $firmopdata, $firmopname, "png");
		$fclurl = saveimg($folders, $firmocldata, $firmclname, "png");

		$nameftp = $pdfname . ".pdf";

		$pdfdata = str_replace('<img id="dtfirmaR" src=""','<img id="dtfirmaR" src="'.$fopurl.'"', $pdfdata);
		$pdfdata = str_replace('<img id="dtfirmaC" src=""','<img id="dtfirmaC" src="'.$fclurl.'"', $pdfdata);
		//<img id="dtfirmaR" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAABLCAYAAACSoX4TAAAChElEQVR4nO3bIWiUYRzH8W94QcOC4YLgwsLCgmHBYDAsXJhgMBgGThBcWDAYFhYMomFhYUHBoLAwwTBhoGFB0LBgHKJwYWGCIKiI6IQXPHSG/3vcvXfnbr6Hvtz5/cAT7riD5+DH8/yf//MeSJIkSZIkSZIkSZIkSZIkSZIkSZIkSZL+rgSYyUZS8lw0RC4A+9lYBU4AR0udkYbCNM1g7QM/gS/AOnAZGC1tZhp4VaBOPmCtowbcJrZLg6Y/chH4we/D1Tq2gUVgrIyJavDMEKvTYcLVGFvAWWCkhPlqwIwDC8AmkNI7XHXgI/Ak+95pDJoO4RQwBywBK0RhvwG8AF4D7+is0T4Dz4Eb2felQirALNGy2KFzVdsjgnYdOIM9MxU0CswD94BdOoOWElvnIjBZ0hw1BMaJoD0k6rH2oL0F1oje2fFypqhhMAlcI2q1Pbr3zlaAKbwJUEEJUeAvENtj+4qWEgGcJ1Y+qZCEaFlcJU6grUGrE6vZGnFj4GqmvkwQW+c6UY81gvaSWOWWiDB62lRhCXCSWNHuA6/ItzU2iENApaT5aUgkxGHgFvmQpcBTojbzpKm+TRD9sS3ytdk2sWVO4ZWT+nSMuFy/SwSrcfdZz95bJkJYJZq61mgqJKHZrL1C9MpqNAOXEtvpJvngzQLniLbIGJ0rXpWo65aBZ8DNLp/Rf6hCnCrniIvzVeJus0a0O9ov2VPioPAtG92e+nj0L3+ABtcl4APwnXiEu9fjRF+xz6YeEro/tXHQeIM1m3o4QrQuWoPzHrgDPCC2zMZ2mWavz5cyUw2caWLV+gQ8pvOPJSNEreYqJUmSJEmSJEmSJEmSJEmSJEmSJEmSDvQLlunYJE+U2TsAAAAASUVORK5CYII=">

		$pdfurl = savepdf($folders, $pdfdata, $pdfname);

		$message = "";

		if ($pdfurl!="") {
			include("./data/ftp.php");
			include("./data/mssql.php");
			$remote_folder = "/" . FTPDEF . " " . $numeroOrden . "/";
			$remote_file = $nameftp;
			$local_file = $pdfurl;
			$connftp = new OV_FTPConnect();
			$conndb = new OV_SQLConnect();

			if(!$connftp->ov_ftp_upload($remote_folder, $remote_file, $local_file, true)){
				$message .= "No se ha conseguido subir el archivo " . $remote_folder . ". " . $remote_file . ". " . $local_file . ". ";
			}else{
				$winfolder = "Z:" . str_replace('/', '\\', $remote_folder);
				$descr = "Fotografía realizada en la visita";
				$query = makeQueryFich($idSerie, $numeroOrden, $descr, $winfolder.$remote_file, $tipofichero, $idOperario, $local_file);

				$funciona = $conndb->query($query);

				if (!$funciona) {
					$message .= "No se ha conseguido registrar el fichero ". utf8_encode($conndb->see_errors());
				}else{
					// Añadir al json la información
					$rutaRemota = "" . $winfolder . $remote_file;

					saveFich($idSerie, $numeroOrden, $idOperario, $descr, $rutaRemota, $local_file, $tipofichero);

				}
			}

			$connftp->ov_ftp_close();

			if ($message=="") {
				$messeg = "Subido 1 archivo del tipo " . strtolower($tipofichero);
				$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $messeg);
				$funcionaseg = $conndb->query($query);
				$conndb->disconnect();

				if ($funcionaseg) {
					// Añadir al json la información
					saveSeg($idSerie, $numeroOrden, $idOperario, $messeg);

					echo json_encode(array('status'=>'OK', 'message'=>'Se ha subido 1 archivo al la carpeta '.$fileurl.' del FTP'));
			          return true;
				}else{
					echo json_encode(array('status'=>'OK', 'message'=>'NO SEG - Se ha subido 1 archivo al la carpeta '.$fileurl.' del FTP'));
			          return false;
				}
			}else{
				$conndb->disconnect();

				echo json_encode(array( 'status'=>'KO', 'error'=>'10', 'message'=>"Problemas a subir archivos al ftp: ". $message));
				return false;
			}
		}else{
			echo json_encode(array( 'status'=>'KO', 'error'=>'10', 'message'=>"No se han enviado ficheros"));
			return false;
		}

		break;

	case 'seguimiento':

	     $idSerie = '';
	     $numeroOrden = '';
	     $idOperario = '';

	     if(isset($_POST))
	     {
	          $idSerie = $_POST['idSerie'];
	          $numeroOrden = $_POST['numeroOrden'];
	          $idOperario = $_POST['idOperario'];
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
					AND	IdOperario LIKE '".$idOperario."'
				ORDER BY Fecha DESC";
				// ORDER BY Fecha ASC";

	     $getExpedientes = $conn->query($query);

	     if($getExpedientes === false)
	     {
			//Error 1 - Base de datos
	          echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode($conn->see_errors()) ));
	          return false;
	     }

		//$rows = mssql_fetch_array($getExpedientes);
		if($conn->num_rows($getExpedientes)>0){

			$output = [];

			while (($row = mssql_fetch_array($getExpedientes))){

				// $observaciones = rtftophp($row['Observaciones']);

				$date = new DateTime(trim($row['Fecha']));
				$fecha = $DIASEM[$date->format($DATE_DOW)] . ", " . $date->format(DATE_FOR_CLI);

				$output[] = array(
					'id'				=>trim($row['id']),
					'idSerie'			=>trim($row['idserie']),
					'numeroOrden'		=>trim($row['numeroorden']),
					'IdOperario'		=>trim($row['IdOperario']),
					'Fecha'			=>$fecha,
					'Observaciones'	=>$row['Observaciones']
					// 'Observaciones'	=>$observaciones
					// 'Observaciones'	=>"<span style='font-size: 11px;'>se creo para prueba test<\/span><p>"
				 );
			}

	          echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
	          return true;
	     }else{
			//Error 4 - La búsqueda no da resultados
	          echo json_encode(array('status'=>'KO', 'error'=>'4', 'message'=>"No hay seguimientos"));
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
		$conndb = new OV_SQLConnect();

		$observaciones = str_replace('+', ' ', $observaciones);

		$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $observaciones);
		$funcionaseg = $conndb->query($query);

		$conndb->disconnect();

		if ($funcionaseg) {
			// Añadir al json la información
			$flag = saveSeg($idSerie, $numeroOrden, $idOperario, $observaciones);

			echo json_encode(array(	'status'=>'OK','message'=>"Seguimiento registrado ". $flag));
		}else{
			//Error 1 - Base de datos
			echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al registrar seguimiento: ". utf8_encode($conn->see_errors()). " ". $query));
		}
	break;

	case 'setcita':
		$idSerie = '';
		$numeroOrden = '';
		$idOperario = '';
		$subc = '';

		if(isset($_POST))
		{
			$idSerie = $_POST['idSerie'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$subc = $_POST['subc'];
		}

		include("./data/mssql.php");
		$conn = new OV_SQLConnect();

		//TODO FIN Cita
		$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, "El operario ha solicitado un fin de cita");
		$funciona = $conn->query($query);

		$conn->disconnect();

		if ($funciona) {
			echo json_encode(array(	'status'=>'OK','message'=>"Cita finalizada"));
		}else{
			echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al al modificar cita: ". utf8_encode($conn->see_errors()) . " - " . $query));
		}
	break;

	// case 'testsend':
	//
	//      $idSerie = '';
	//      $numeroOrden = '';
	// 	$idOperario = '';
	// 	$fecha = '';
	// 	$observaciones = '';
	//
	//      if(isset($_POST))
	//      {
	//           $idSerie = $_POST['idSerie'];
	//           $numeroOrden = $_POST['numeroOrden'];
	// 		$idOperario = $_POST['idOperario'];
	// 		$fecha = $_POST['fecha'];
	// 		$observaciones = $_POST['observaciones'];
	//      }
	//
	//      include("./data/mssql.php");
	//
	// 	echo json_encode(array(	'status'=>'OK',
	// 						'idSerie'=>$idSerie,
	// 						'numeroOrden'=>$numeroOrden,
	// 						'idOperario'=>$idOperario,
	// 						'fecha'=>$fecha,
	// 						'observaciones'=>$observaciones,
	// 						'token'=>$token));
	// break;

	default:{
		//Error 0 - Opción no conocida
          echo json_encode(array( 'status'=>'KO', 'error'=>'0', 'message'=>"El servidor no procesa los datos enviados. Valores enviados -- " . $cadena));
		return false;
     }
}

function formatError($errors){
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

function check_authorization(){
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
		$fincita = "Fincita=0";
	}else if ("completado") {
		$fincita = "Fincita=1";
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
				AND 	exped.IdDireccion = dir.IdDireccion
				AND  ".$fincita."
				AND	expedest.FechaCita IS NOT NULL
				AND 	expedest.IdOperario LIKE '".$idoperario."'
			ORDER BY  expedest.FechaCita ASC";

	return $query;
}

function get_output_exp($tipo, $getExpedientes){
	$output = [];

	while (($row = mssql_fetch_array($getExpedientes))){

		$date = new DateTime(trim($row['FechaCita']));
		$fechaCita = $DIASEM[$date->format($DATE_DOW)] . ", " . $date->format(DATE_FOR_CLI);

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

function saveimg($folders, $img, $filename, $filext){
	$img=str_replace("\\/", "/", $img);
	if ($filext!='') {
		$img = str_replace(' ', '+', $img);
		$data = base64_decode($img);
		// $file = UPLOAD_DIR . uniqid() . '.png';
		$folder = UPLOAD_DIR;
		// $folder = UPLOAD_DIR;
		if (!is_dir($folder)) {
			mkdir($folder);
		}
		foreach ($folders as $value) {
			$folder .= $value . "/";
			if (!is_dir($value)) {
				mkdir($folder);
			}
		}

		$file = $folder . $filename . "." . $filext;
		$success = file_put_contents($file, $data);
		return $success ? $file : false;
	}else{
		return false;
	}
}

function savepdf($folders, $filedata, $filename){
	$folder = "";

	//$filedata=str_replace("\\/", "/", $img);
	$folder = UPLOAD_DIR;
	// $folder = UPLOAD_DIR;
	if (!is_dir($folder)) {
		mkdir($folder);
	}
	foreach ($folders as $value) {
		$folder .= $value . "/";
		if (!is_dir($value)) {
			mkdir($folder);
		}
	}

	$folder .= $filename . ".pdf";

     // instantiate and use the dompdf class
     $dompdf = new Dompdf();
     $dompdf->loadHtml($filedata);

     // (Optional) Setup the paper size and orientation
     $dompdf->setPaper('A4', 'portrait');

     // Render the HTML as PDF
     $dompdf->render();

	$output = $dompdf->output();

	file_put_contents($folder, $output);

	return $folder;
}

function makeQueryFich($idSerie, $NumOrden, $Desc, $ruta, $tipoFichero, $idOperario, $local_file){
	$query = "INSERT INTO TbExpedientesFicheros (
		Id,
		IdSerie,
		NumeroOrden,
		Descripcion,
		ruta,
		IdTipoFichero,
		fecharegistro,
		idoperario,
		idusuario,
		fecharegistrousuario
		)
	VALUES (
		NEWID(),
		'".$idSerie."',
		".intval($NumOrden).",
		'".$Desc."',
		'".$ruta."',
		(SELECT IdTipoFichero
			FROM TbTiposFichero
			WHERE TipoFichero LIKE '".$tipoFichero."'),
		GETDATE (),
		'".$idOperario."',
		null,
		null
	);";
	//SELECT @@IDENTITY FROM TbExpedientesSeguimiento;

	return $query;
}
// function ingresarFicheroDB($idSerie, $NumOrden, $Desc, $ruta, $tipoFichero, $idOperario){
// 	include("./data/mssql.php");
//
// 	$conn = new OV_SQLConnect();
//
// 	$query = "INSERT INTO TbExpedientesFicheros (
// 		Id,
// 		IdSerie,
// 		NumeroOrden,
// 		Descripcion,
// 		ruta,
// 		IdTipoFichero,
// 		fecharegistro,
// 		idoperario,
// 		idusuario,
// 		fecharegistrousuario
// 		)
// 	VALUES (
// 		NEWID(),
// 		'".$idSerie."',
// 		".intval($NumOrden).",
// 		'".$Desc."',
// 		'".$ruta."',
// 		(SELECT IdTipoFichero
// 			FROM TbTiposFichero
// 			WHERE TipoFichero LIKE '".$tipoFichero."'),
// 		GETDATE (),
// 		'".$idOperario."',
// 		null,
// 		null
// 	);";
//
// 	$funciona = $conn->query($query);
//
// 	$conn->disconnect();
//
// 	if ($funciona) {
// 		return array("flag"=>true, "message"=>"Fichero Registrado");
// 	}else{
// 		//Error 1 - Base de datos
// 		return array("flag"=>false, "message"=>"Error al registrar fichero: ". utf8_encode($conn->see_errors()));
// 	}
// }

function makeQuerySeg($idSerie, $numeroOrden, $idOperario, $observaciones){

	$query = "INSERT INTO TbExpedientesSeguimiento (
		id,
		idserie,
		numeroorden,
		IdOperario,
		Fecha,
		Observaciones,
		idusuario,
		FechaUsuarioRevisa,
		IdUsuarioRevision,
		IdAgenteemisor,
		IdAgenteDestinatario
		)
	VALUES (
		NEWID(),
		'".$idSerie."',
		".intval($numeroOrden).",
		'".$idOperario."',
		GETDATE (),
		'".$observaciones."',
		null,
		null,
		null,
		null,
		null
	);";
	//SELECT @@IDENTITY FROM TbExpedientesSeguimiento;

	return $query;
}

function saveSeg($idSerie, $numeroOrden, $idOperario, $observaciones){
	// Añadir al json la información
	$arr_seg = array(
		"id" => uniqid("seg-", true),
		"IdSerie" => $idSerie,
		"NumeroOrden" => $numeroOrden,
		"IdOperario" => $idOperario,
		"Fecha" => date(DATE_FOR_SQL),
		"Observaciones" => $observaciones,
		"Gestionado" => false,
		"Admin" => ""
	);

	return writeJSON($arr_seg, "seguimiento");
}

function saveFich($idSerie, $numeroOrden, $idOperario, $Descripcion, $rutaRemota, $rutaLocal, $tipoFichero){
	// Añadir al json la información
	$arr_fich = array(
		"id" => uniqid("fich-", true),
		"IdSerie" => $idSerie,
		"NumeroOrden" => $numeroOrden,
		"IdOperario" => $idOperario,
		"Descripcion" => $Descripcion,
		"rutaRemota" => $rutaRemota,
		"rutaLocal" => $rutaLocal,
		"tipoFichero" => $tipoFichero,
		"fecharegistro" => date(DATE_FOR_SQL),
		"Gestionado" => false ,
		"Admin" => ""
	);

	return writeJSON($arr_fich, "fichero");
}


function writeJSON($array, $tipo){
	$json_string = json_encode($array);
	$json_file = "" . JSON_FOLDER . $JSON[$tipo].".json";
	return file_put_contents($json_file, $json_string, FILE_APPEND);
}

?>
