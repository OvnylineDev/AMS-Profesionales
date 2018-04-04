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
define('FTPURG', 'TEMP');
// $FTPDEF = "ORDENES ";
define('JSON_FOLDER', './data/json/');
// $JSONFOLDER = "./data/json/";
//2017-12-14 16:47:54.243
// $DATEFORSQL='Y-m-d H:i:s';
define('DATE_FOR_SQL', 'Y-m-d H:i:s');
// $DATEFORCLI='d-m-y | H:i:s';
define('DATE_FOR_CLI', 'd-m-Y | H:i');
// $DATEDOW='N';
define('DATE_DOW', 'N');

define('UNKNOWN', 'unknown');

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
		$mobincube = '';

		if(isset($_POST))
		{
		     $usernameop = strtolower($_POST['usuario']);
			$passop = $_POST['clave'];
			$mobincube = $_POST['mobincube'];
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

			if($row['Usuario']==$usernameop && strtolower($row['PassWord'])==$passop){
				$token = bin2hex(openssl_random_pseudo_bytes(10));

				// Añadir al json la información
				saveOp($token, $mobincube, $row['IdOperario'], $row['Usuario'], $row['Gremio'], $row['Subcontrata']);

				echo json_encode(array(
					'status'=>'OK',
					'usuario'=>$row['Usuario'],
					'id'=>$row['IdOperario'],
					'gremio' =>$row['Gremio'],
					'subcont' =>$row['Subcontrata'],
					'token'=>$token,
					'admin' => false
				));
				// return true
			}else{
				//Error 3 - Datos de acceso erróneos
				echo json_encode(array( 'status'=>'KO', 'error'=>'3', 'message'=>'No coinciden los datos de acceso' ));
				return false;
			}
		}else{
			//Error 4 - No existe el usuario
			echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No existe el usuario '));
			return false;
		}

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
	          echo json_encode(array('status'=>'KO', 'error'=>'4', 'message'=>"No hay expedientes en pendientes"));
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

		$fechaCita = getFechaCitaEstado($idExpEst);

		if ($fechaCita == "") {
			$fechaCita = $date->format(DATE_FOR_CLI);
		}

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


	case 'getParte':

		$idExpEst = '';
		$token = '';

		if(isset($_POST))
		{
			$idExpEst = $_POST['idExpEst'];
			$token = $_POST['token'];
		}

		include("./data/mssql.php");

		$conn = new OV_SQLConnect();

	     $query = "SELECT 	IdEstadoOrden
	               FROM 	TbExpedientesEstados
	               WHERE 	id LIKE '".$idExpEst."'";

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

	     $tipoParte = trim($row['IdEstadoOrden']);

		$arrDatosTipo = getSearchJSON("partes", array('id' => $tipoParte));

		$output = array(
 	          'id'				=>$arrDatosTipo[0]["id"],
 	          'ficheroAMS'		=>$arrDatosTipo[0]["ficheroAMS"],
 	          'html'			=>file_get_contents(JSON_FOLDER . "partes/" . $arrDatosTipo[0]["fichero"])
 	     );

		$conn->disconnect();

	     echo json_encode(array('status'=>'OK', 'items'=>$output, 'token'=>$token));
	     return true;

	break;

	case 'getTiposParteBase':

		$token = '';

		if(isset($_POST))
		{
			$token = $_POST['token'];
		}

		$arrDatosTipo = getFieldsJSON("partes_base", array("id", "nombre", "ficheroAMS"));

		echo json_encode(array('status'=>'OK', 'items'=>$arrDatosTipo, 'token'=>$token));
		return true;

	break;

	case 'getParteBase':

		$idParte = '';
		$token = '';

		if(isset($_POST))
		{
			$idParte = $_POST['idParte'];
			$token = $_POST['token'];
		}

		$arrDatosTipo = getSearchJSON("partes_base", array('id' => $idParte));

		$output = array(
 	          'id'				=>$arrDatosTipo[0]["id"],
 	          'ficheroAMS'		=>$arrDatosTipo[0]["ficheroAMS"],
 	          'html'			=>file_get_contents(JSON_FOLDER . "partes/" . $arrDatosTipo[0]["fichero"])
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
		$fechaCita = $date->format(DATE_FOR_CLI);

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
		$nameOperario = '';
		$filenames = '';
		$filedescs = '';
		$datephoto = '';
		$filedata = '';
		$filext = '';
		$tipofichero = '';
		$datetxt = '';

		$jsonIdSeg = uniqid("seg-", true);

		if(isset($_POST))
		{
			$idSerie = $_POST['idSerie'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$nameOperario = $_POST['nameOperario'];
			$filenames = $_POST['filenames'];
			$filedescs = $_POST['filedescs'];
			$datephoto = $_POST['datephoto'];
			$filedata = $_POST['filedata'];
			$filext = $_POST['filext'];
			$tipofichero = $_POST['tipofichero'];
			$datetxt = $_POST['datetxt'];
		}


		$fileurls = array();
		$filetxts = array();
		$datestxt = array();

		for ($i=0; $i < count($filedata); $i++) {
			$folders = array($idOperario, FTPDEF . " " . $numeroOrden);
			$nameftp = $filenames[$i] . "." . $filext[$i];
			$fileurls[$nameftp] = saveimg($folders, $filedata[$i], $filenames[$i], $filext[$i]);
			$filetxts[$nameftp] = $filedescs[$i];
			$datestxt[$nameftp] = str_replace('+', ' ', $datephoto[$i]);
			//str_replace('+', ' ', $_POST['datephoto']);
		}

		$message = "";

		if (count($fileurls)>0) {
			include("./data/ftp.php");
			include("./data/mssql.php");
			$ftpbasedir = "/" . FTPDEF . " " . $numeroOrden . "/";
			$ftpath = $numeroOrden . "O " . $tipofichero . " " . $nameOperario . " " . $datetxt . "/";
			$remote_folder = $ftpbasedir . $ftpath;
			$connftp = new OV_FTPConnect();
			$conndb = new OV_SQLConnect();

			//Creamos los directorios si no están creados
			if(!$connftp->ov_ftp_changedir($remote_folder)){
				$connftp->ov_ftp_changedir($ftpbasedir);
				$connftp->ov_ftp_createdir($ftpath);
			}

			foreach ($fileurls as $remote_file => $local_file) {
				if($local_file){

					// $fp = fopen($file, 'r');
					//$remote_folder, $remote_file, $local_file, $binary
					if(!$connftp->ov_ftp_upload($remote_folder, $remote_file, $local_file, true)){
						$message .= "No se ha conseguido subir el archivo " . $remote_folder . ". " . $remote_file . ". " . $local_file . ". ";
					}else{
						// ingresarFichero($idSerie, $NumOrden, "Fotografía ", $ruta, $tipoFichero, $idOperario);
						$winfolder = "Z:" . str_replace('/', '\\', $remote_folder);

						$descr = mb_strtoupper($tipofichero.": ".$filetxts[$remote_file],'utf-8');
						$date = $datestxt[$remote_file];

						$query = makeQueryFich($idSerie, $numeroOrden, $descr, $winfolder.$remote_file, $tipofichero, $idOperario, $date);

						$funciona = $conndb->query($query);

						if (!$funciona) {
							$message .= "No se ha conseguido registrar el fichero ". utf8_encode($conndb->see_errors());
						}else{
							// Añadir al json la información
							$rutaRemota = $winfolder . $remote_file;

						}
					}
					// fclose($fp);
				}else{
					$message .= "Ha ocurrido un error en el servidor - No se detectan archvios subidos";
				}
			}

			$connftp->ov_ftp_close();
			borrarSubidas($idOperario . "/" . FTPDEF . " " . $numeroOrden);
			// $conndb->disconnect();

			if ($message=="") {
				$message = "";

				if (count($fileurls)>1) {
					$message = "Subidos ". count($fileurls) . " archivos del tipo " . strtolower($tipofichero);
				}else{
					$message = "Subido ". count($fileurls) . " archivo del tipo " . strtolower($tipofichero);
				}

				$message = mb_strtoupper($message,'utf-8');

				$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $message);
				$funcionaseg = $conndb->query($query);

				// $funcionaseg = false;
				if ($funcionaseg) {
					$conndb->disconnect();

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

	/*
	 * Operation: subirimgurg
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
	case 'subirimgurg':
		$idIncidencia = '';
		$idOperario = '';
		$nameOperario = '';
		$filenames = '';
		$filedescs = '';
		$datephoto = '';
		$filedata = '';
		$filext = '';
		$tipofichero = '';
		$datetxt = '';

		if(isset($_POST))
		{
			$idIncidencia = $_POST['idIncidencia'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$nameOperario = $_POST['nameOperario'];
			$filenames = $_POST['filenames'];
			$filedescs = $_POST['filedescs'];
			$datephoto = $_POST['datephoto'];
			$filedata = $_POST['filedata'];
			$filext = $_POST['filext'];
			$tipofichero = $_POST['tipofichero'];
			$datetxt = $_POST['datetxt'];
		}


		$fileurls = array();
		$filetxts = array();
		$datestxt = array();

		for ($i=0; $i < count($filedata); $i++) {
			$folders = array($idOperario, FTPDEF . " " . $idIncidencia);
			$nameftp = $filenames[$i] . "." . $filext[$i];
			$fileurls[$nameftp] = saveimg($folders, $filedata[$i], $filenames[$i], $filext[$i]);
			$filetxts[$nameftp] = $filedescs[$i];
			$datestxt[$nameftp] = str_replace('+', ' ', $datephoto[$i]);
			//str_replace('+', ' ', $_POST['datephoto']);
		}

		$message = "";

		if (count($fileurls)>0) {
			include("./data/ftp.php");
			include("./data/mssql.php");
			$ftpbasedir = "/" . FTPURG . "/". FTPDEF . " " . $idIncidencia . "/";
			$ftpath = $tipofichero . " " . $nameOperario . " " . $datetxt . "/";
			$remote_folder = $ftpbasedir . $ftpath;
			$connftp = new OV_FTPConnect();
			$conndb = new OV_SQLConnect();

			//Creamos los directorios si no están creados
			if(!$connftp->ov_ftp_changedir("/" . FTPURG . "/". FTPDEF . " " . $idIncidencia . "/")){
				$connftp->ov_ftp_changedir("/" . FTPURG . "/");
				$connftp->ov_ftp_createdir(FTPDEF . " " . $idIncidencia . "/");
			}

			if(!$connftp->ov_ftp_changedir($remote_folder)){
				$connftp->ov_ftp_changedir($ftpbasedir);
				$connftp->ov_ftp_createdir($ftpath);
			}

			foreach ($fileurls as $remote_file => $local_file) {
				if($local_file){

					// $fp = fopen($file, 'r');
					//$remote_folder, $remote_file, $local_file, $binary
					if(!$connftp->ov_ftp_upload($remote_folder, $remote_file, $local_file, true)){
						$message .= "No se ha conseguido subir el archivo " . $remote_folder . ". " . $remote_file . ". " . $local_file . ". ";
					}else{
						// ingresarFichero($idSerie, $NumOrden, "Fotografía ", $ruta, $tipoFichero, $idOperario);
						$winfolder = "Z:" . str_replace('/', '\\', $remote_folder);

						$descr = mb_strtoupper($tipofichero.": ".$filetxts[$remote_file],'utf-8');
						$date = $datestxt[$remote_file];

						// $query = makeQueryFich($idSerie, $numeroOrden, $descr, $winfolder.$remote_file, $tipofichero, $idOperario, $date);
						$query = makeQueryFichIncid($idIncidencia, $date, $idOperario, $tipofichero, $winfolder.$remote_file, $descr);

						$funciona = $conndb->query($query);
						// $funciona = false;

						if (!$funciona) {
							$message .= "No se ha conseguido registrar el fichero ". utf8_encode($conndb->see_errors());
						}else{
							// Añadir al json la información
							$rutaRemota = $winfolder . $remote_file;

						}
					}
					// fclose($fp);
				}else{
					$message .= "Ha ocurrido un error en el servidor - No se detectan archvios subidos";
				}
			}

			$connftp->ov_ftp_close();
			$conndb->disconnect();

			borrarSubidas($idOperario . "/" . FTPDEF . " " . $idIncidencia);

			if ($message=="") {
				$message = "";

				if (count($fileurls)>1) {
					$message = "Subidos ". count($fileurls) . " archivos del tipo " . strtolower($tipofichero);
				}else{
					$message = "Subido ". count($fileurls) . " archivo del tipo " . strtolower($tipofichero);
				}

				// $funcionaseg = false;
				echo json_encode(array('status'=>'OK', 'message'=>'Se han subido ' . count($fileurls) . ' archivos al la carpeta '.$folder.' del FTP'));
		          return true;

			}else{
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
		$nameOperario = '';
		$gremOperario = '';
		$pdfname = '';
		$pdfdata = '';
		$firmopdata = '';
		$firmopname = '';
		$firmocldata = '';
		$firmclname = '';
		$tipofichero = '';

		$jsonIdSeg = uniqid("seg-", true);

		if(isset($_POST))
		{
			$idSerie = $_POST['idSerie'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$nameOperario = $_POST['nameOperario'];
			$gremOperario = $_POST['gremOperario'];
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

		$nameftp = $numeroOrden . "O " . $pdfname . ".pdf";

		$pdfdata = str_replace('<img id="dtfirmaR" src=""','<img id="dtfirmaR" src="'.$fopurl.'"', $pdfdata);
		$pdfdata = str_replace('<img id="dtfirmaC" src=""','<img id="dtfirmaC" src="'.$fclurl.'"', $pdfdata);

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
				$descr = mb_strtoupper("Parte realizado por el operario",'utf-8');
				$query = makeQueryFich($idSerie, $numeroOrden, $descr, $winfolder.$remote_file, $tipofichero, $idOperario, date(DATE_FOR_SQL));

				$funciona = $conndb->query($query);

				if (!$funciona) {
					$message .= "No se ha conseguido registrar el fichero ". utf8_encode($conndb->see_errors());
				}else{
					// Añadir al json la información
					$rutaRemota = "" . $winfolder . $remote_file;

				}
			}

			$connftp->ov_ftp_close();
			borrarSubidas($idOperario . "/" . FTPDEF . " " . $numeroOrden);

			if ($message=="") {
				$messeg = mb_strtoupper("Subido 1 archivo del tipo " . $tipofichero,'utf-8');
				$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $messeg);
				$funcionaseg = $conndb->query($query);
				$conndb->disconnect();

				if ($funcionaseg) {
					// Añadir al json la información
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


	case 'crearparteurg':
		$idIncidencia = '';
		$idOperario = '';
		$nameOperario = '';
		$gremOperario = '';
		$pdfname = '';
		$pdfdata = '';
		$firmopdata = '';
		$firmopname = '';
		$firmocldata = '';
		$firmclname = '';
		$tipofichero = '';

		$jsonIdSeg = uniqid("seg-", true);

		if(isset($_POST))
		{
			$idIncidencia = $_POST['idIncidencia'];
			$idOperario = $_POST['idOperario'];
			$nameOperario = $_POST['nameOperario'];
			$gremOperario = $_POST['gremOperario'];
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

		$folders = array($idOperario, FTPDEF . " " . $idIncidencia);

		$fopurl = saveimg($folders, $firmopdata, $firmopname, "png");
		$fclurl = saveimg($folders, $firmocldata, $firmclname, "png");

		$nameftp = $idIncidencia . "O " . $pdfname . ".pdf";

		$pdfdata = str_replace('<img id="dtfirmaR" src=""','<img id="dtfirmaR" src="'.$fopurl.'"', $pdfdata);
		$pdfdata = str_replace('<img id="dtfirmaC" src=""','<img id="dtfirmaC" src="'.$fclurl.'"', $pdfdata);

		$pdfurl = savepdf($folders, $pdfdata, $pdfname);

		$message = "";

		if ($pdfurl!="") {
			include("./data/ftp.php");
			include("./data/mssql.php");
			$remote_folder = "/" . FTPURG . "/". FTPDEF . " " . $idIncidencia . "/";
			$remote_file = $nameftp;
			$local_file = $pdfurl;
			$connftp = new OV_FTPConnect();
			$conndb = new OV_SQLConnect();

			if(!$connftp->ov_ftp_upload($remote_folder, $remote_file, $local_file, true)){
				$message .= "No se ha conseguido subir el archivo " . $remote_folder . ". " . $remote_file . ". " . $local_file . ". ";
			}else{
				$winfolder = "Z:" . str_replace('/', '\\', $remote_folder);
				$descr = mb_strtoupper("Parte urgente creado por el operario",'utf-8');
				$query = makeQueryFichIncid($idIncidencia, date(DATE_FOR_SQL), $idOperario, $tipofichero, $winfolder.$remote_file, $descr);

				$funciona = $conndb->query($query);

				if (!$funciona) {
					$message .= "No se ha conseguido registrar el fichero ". utf8_encode($conndb->see_errors());
				}else{
					// Añadir al json la información
					$rutaRemota = "" . $winfolder . $remote_file;

				}
			}

			$connftp->ov_ftp_close();

			$conndb->disconnect();

			borrarSubidas($idOperario . "/" . FTPDEF . " " . $idIncidencia);

			if ($message=="") {
				// Añadir al json la información
				echo json_encode(array('status'=>'OK', 'message'=>'Se ha subido 1 archivo al la carpeta '.$fileurl.' del FTP'));
		          return true;

			}else{
				echo json_encode(array( 'status'=>'KO', 'error'=>'10', 'message'=>"Problemas a subir archivos al ftp: ". $message));
				return false;
			}
		}else{
			echo json_encode(array( 'status'=>'KO', 'error'=>'10', 'message'=>"No se han enviado ficheros"));
			return false;
		}

		break;

	case 'sendval':
		$idSerie = '';
		$numeroOrden = '';
		$idOperario = '';
		$nameOperario = '';
		$gremOperario = '';
		$pdfname = '';
		$pdfdata = '';
		$tipofichero = '';

		$jsonIdSeg = uniqid("seg-", true);

		if(isset($_POST))
		{
			$idSerie = $_POST['idSerie'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$nameOperario = $_POST['nameOperario'];
			$gremOperario = $_POST['gremOperario'];
			$pdfname = $_POST['pdfname'];
			$pdfdata = str_replace('+', ' ', $_POST['pdfdata']);
			$tipofichero = $_POST['tipofichero'];
		}

		$pdfurl = "";

		$folders = array($idOperario, FTPDEF . " " . $numeroOrden);

		$nameftp = $numeroOrden . "O " . $pdfname . ".pdf";

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
				$descr = mb_strtoupper("Valoración enviada por el operario",'utf-8');
				$query = makeQueryFich($idSerie, $numeroOrden, $descr, $winfolder.$remote_file, $tipofichero, $idOperario, date(DATE_FOR_SQL));

				$funciona = $conndb->query($query);

				if (!$funciona) {
					$message .= "No se ha conseguido registrar el fichero ". utf8_encode($conndb->see_errors());
				}else{
					// Añadir al json la información
					$rutaRemota = "" . $winfolder . $remote_file;

				}
			}

			$connftp->ov_ftp_close();

			if ($message=="") {
				$messeg = mb_strtoupper("Subido 1 archivo del tipo " . $tipofichero,'utf-8');
				$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $messeg);
				$funcionaseg = $conndb->query($query);
				$conndb->disconnect();

				if ($funcionaseg) {
					// Añadir al json la información
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
				$fecha = $date->format(DATE_FOR_CLI);

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

	case 'llamadasubc':

	     $idExpEst = '';
	     $idSerie = '';
	     $numeroOrden = '';
		$idOperario = '';
		$observaciones = '';
		$fechaCitaOld = '';
		$fechaCitaNew = '';
		$cambiosubc = false;
		$subc = false;

		$jsonIdSeg = uniqid("seg-", true);

	     if(isset($_POST))
	     {
	          $idExpEst = $_POST['idExpEst'];
	          $idSerie = $_POST['idSerie'];
	          $numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$observaciones = $_POST['observaciones'];
			$fechaCitaOld = $_POST['fechaCitaOld'];
			$fechaCitaNew = $_POST['fechaCitaNew'];
			$cambiosubc = ($_POST['cambiosubc'] == "true");
			$subc = ($_POST['subc'] == "true");
	     }

		if ($subc && $cambiosubc) {

			include("./data/mssql.php");
			$conndb = new OV_SQLConnect();

			$observaciones = mb_strtoupper(str_replace('+', ' ', $observaciones),'utf-8');
			$fechaCitaOld = gmdate(DATE_FOR_SQL, $fechaCitaOld);
			$fechaCitaNew = gmdate(DATE_FOR_SQL, $fechaCitaNew);

			$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $observaciones);
			$funcionaseg = $conndb->query($query);

			$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, mb_strtoupper("El operario subcontrata ha introducido un cambio en la fecha de cita, de " . $fechaCitaOld . " a " . $fechaCitaNew,'utf-8'));
			$funcionaseg = $conndb->query($query);

			//Realizar el cambio de fecha
			$query = makeQueryUpdDate($idExpEst, $fechaCitaNew);
			$funcionaseg = $conndb->query($query);

			$conndb->disconnect();

			if ($funcionaseg) {
				echo json_encode(array(	'status'=>'OK','message'=>"Seguimiento registrado y fecha actualizada"));
			}else{
				//Error 1 - Base de datos
				echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al registrar seguimiento: ". utf8_encode($conndb->see_errors()). " ". $query));
			}

		}else{
			echo json_encode(array( 'status'=>'KO', 'error'=>'6', 'message'=>"Error el operario no es una subcontrata"));
		}
	break;


	case 'nuevoseguimiento':

	     $idSerie = '';
	     $numeroOrden = '';
		$idOperario = '';
		$observaciones = '';

		$jsonIdSeg = uniqid("seg-", true);

	     if(isset($_POST))
	     {
	          $idSerie = $_POST['idSerie'];
	          $numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$observaciones = $_POST['observaciones'];
	     }

		include("./data/mssql.php");
		$conndb = new OV_SQLConnect();

		$observaciones = mb_strtoupper(str_replace('+', ' ', $observaciones),'utf-8');

		$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $observaciones);
		$funcionaseg = $conndb->query($query);

		$conndb->disconnect();

		if ($funcionaseg) {
			// Añadir al json la información
			echo json_encode(array(	'status'=>'OK','message'=>"Seguimiento registrado "));
		}else{
			//Error 1 - Base de datos
			echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al registrar seguimiento: ". utf8_encode($conn->see_errors()). " ". $query));
		}
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
	                    AND	expedest.id LIKE '".$idExpEst."'";

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
	     $fechaCita = $date->format(DATE_FOR_CLI);

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

	case 'fincita':
		$idSerie = '';
		$numeroOrden = '';
		$idOperario = '';
		$idExpEst = '';

		$jsonIdSeg = uniqid("seg-", true);

		if(isset($_POST))
		{
			$idSerie = $_POST['idSerie'];
			$numeroOrden = $_POST['numeroOrden'];
			$idOperario = $_POST['idOperario'];
			$idExpEst = $_POST['idExpEst'];
		}

		include("./data/mssql.php");
		$conndb = new OV_SQLConnect();

		$observaciones = mb_strtoupper("Fin de cita confirmado por el operario",'utf-8');

		$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $observaciones);
		$funcionaseg = $conndb->query($query);

		//realizar el UPDATE
		$query = makeQueryUpdFinCita($idExpEst);

		$funcionaseg = $conndb->query($query);

		$conndb->disconnect();

		if ($funcionaseg) {
			echo json_encode(array(	'status'=>'OK','message'=>"Seguimiento registrado "));
		}else{
			//Error 1 - Base de datos
			echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al registrar seguimiento: ". utf8_encode($conn->see_errors()). " ". $query));
		}
	break;

	case 'idIncidencia':

		$idIncidencia = uniqid("INCI_", true);

		if ($idIncidencia != NULL || $idIncidencia != "") {
			echo json_encode(array(	'status'=>'OK','id'=>$idIncidencia));
		}else{
			//Error 1 - Base de datos
			echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al generar el identificador"));
		}
	break;

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
		$fechaCita = $date->format(DATE_FOR_CLI);

		$output[] = array(
			'id'				=>trim($row['id']),
			'idSerie'			=>trim($row['IdSerie']),
			'numeroOrden'		=>trim($row['NumeroOrden']),
			'numeroExpediente'	=>trim($row['NumeroExpediente']),
			'fechaCita'		=>$fechaCita,
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

function borrarSubidas($directorio){
	deleteDir(UPLOAD_DIR.$directorio);
}

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function makeQueryFich($idSerie, $NumOrden, $Desc, $ruta, $tipoFichero, $idOperario, $date){
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
		fecharegistrousuario,
		FechaTomaFoto
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
		null,
		CONVERT(datetime, '".$date."', 121)
	);";
	//SELECT @@IDENTITY FROM TbExpedientesSeguimiento;

	return $query;
}

function makeQueryFichIncid($idIncidencia, $date, $idOperario, $tipofichero, $ruta, $descr){
	$query = "INSERT INTO TbFicheros_Incidencias_Festivos (
		IdRegistro,
		ReferenciaExpediente,
		FechaRegistro,
		IdOperario,
		IdTipoFichero,
		RutaFichero,
		DescripcionFichero,
		IdUsuarioRevisa,
		FechaRevision
		)
	VALUES (
		NEWID(),
		'".$idIncidencia."',
		CONVERT(datetime, '".$date."', 121),
		'".$idOperario."',
		(SELECT IdTipoFichero
			FROM TbTiposFichero
			WHERE TipoFichero LIKE '".$tipofichero."'),
		'".$ruta."',
		'".$descr."',
		null,
		null
	);";
	//SELECT @@IDENTITY FROM TbExpedientesSeguimiento;

	return $query;
}

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

function setCteSQL(){
	return "SET CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON;";
}

// CONVERT(datetime, '".$date."')
function makeQueryUpdDate($idExpEst, $fechaCitaNew){
	$query = setCteSQL();
	$query .= "UPDATE 	TbExpedientesEstados
			SET		FechaCita = CONVERT(datetime, '".$fechaCitaNew."', 121),
					citaestablecidaporoperario = 1
			WHERE 	id LIKE '".$idExpEst."'";

	return $query;
}

/*
SET CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON;

Línea obligatoria para que pueda hacer la actualización
 */
function makeQueryUpdFinCita($idExpEst){
	$query = setCteSQL();
	$query .= "UPDATE 	TbExpedientesEstados
			SET		Fincita = 1
			WHERE 	id LIKE '".$idExpEst."'";

	return $query;
}

function saveOp($token, $mobincube, $IdOperario, $nombre, $gremio, $subcont){
	// Añadir al json la información
	$arr_seg = array(
          $id => array(
			"token" => $token,
			"mobincube" => $mobincube,
	          "IdOperario" => $IdOperario,
	          "nombre" => $nombre,
	          "gremio" => $gremio,
			"subcont" => $subcont,
	          "Fecha" => date(DATE_FOR_SQL)
		)
	);

	return writeJSON($arr_seg, "operario");
}

function saveExpCita($id, $jsonIdSeg, $idSerie, $numeroOrden, $idOperario, $FechaCitaOld, $FechaCitaNew, $FinCita){
	// Añadir al json la información
	$arr_seg = array(
      $id => array(
			"id" => $id,
			"jsonIdSeg" => $jsonIdSeg,
			"IdSerie" => $idSerie,
			"NumeroOrden" => $numeroOrden,
			"IdOperario" => $idOperario,
			"FechaCitaOld"=> $FechaCitaOld,
			"FechaCitaNew"=> $FechaCitaNew,
			"FinCita"=> $FinCita,
			"FechaMod" => date(DATE_FOR_SQL),
			"Gestionado" => false,
			"Admin" => ""
		)
	);

	return writeJSON($arr_seg, "estadocita");
}

function saveFinCita($id, $idSerie, $numeroOrden, $idOperario, $observaciones){
	// Añadir al json la información
	$arr_seg = array(
          $id => array(
			"id" => $id,
			"IdSerie" => $idSerie,
			"NumeroOrden" => $numeroOrden,
			"IdOperario" => $idOperario,
			"Fecha" => date(DATE_FOR_SQL),
			"Observaciones" => $observaciones,
			"Gestionado" => false,
			"Admin" => ""
		)
	);

	return writeJSON($arr_seg, "seguimiento");
}

function writeJSON($array, $tipo){
	if ($tipo != UNKNOWN) {
		$flag = true;
		$tempArray = null;
		$json_string = "";
		$json_file = JSON_FOLDER . json_type($tipo) . ".json";

		$fp = fopen($json_file, "r+");

		if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock

			$str_datos = "";

			while (!feof($fp)) {
				$str_datos .= fread($fp, 8192);
			}

			rewind($fp);
			ftruncate($fp, 0);

			if ($str_datos === FALSE || $str_datos == "") {
	               $tempArray = array();
			}else{
				$tempArray = json_decode($str_datos, true);
			}

			array_push($tempArray, $array);

			$json_string = json_encode($tempArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

			$flag = fwrite($fp, $json_string);

			flock($fp, LOCK_UN);    // release the lock
		} else {
			$flag = false;
		}

		fclose($fp);

		return $flag;
	}

     return null;
}

function writeFullJSON($array, $tipo){
	if ($tipo != UNKNOWN) {
		$flag = true;
		$json_string = "";
		$json_file = JSON_FOLDER . json_type($tipo) . ".json";

		$fp = fopen($json_file, "r+");

		if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock

			ftruncate($fp, 0);

			$json_string = json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

			$flag = fwrite($fp, $json_string);

			flock($fp, LOCK_UN);    // release the lock
		} else {
			$flag = false;
		}

		fclose($fp);

		return $flag;
	}

     return null;
}

function getFechaCitaEstado($idExpEst){
	// $array = getEstados();
	$array = getFullJSON("estadocita");
	$fechaCita = "";

	if ($array != null) {
	     foreach ($array as $iter => $subarray) {
	          foreach ($subarray as $id => $estcita) {
	               if ($id == $idExpEst) {
					foreach ($estcita as $fieldname => $value) {
		                    if ($fieldname == "FechaCitaNew") {
		                    	$fechaCita = $value;
							return $fechaCita;
		                    }
		               }
	               }
	          }
	     }
	}

	return $fechaCita;
}

function getFullJSON($JSON){
	$arrayJSON = array_reverse(readJSON($JSON));
	$array = array();

	if ($arrayJSON != null) {
	     foreach ($arrayJSON as $iter => $subarray) {
	          foreach ($subarray as $id => $elemento) {
	              	array_push($array, $elemento);
	          }
	     }
	}else{
		$array = null;
	}

	return $array;
}

function getSearchJSON($JSON, $search){
	$arrayJSON = array_reverse(readJSON($JSON));
	$array = array();

	if ($arrayJSON != null) {
	     foreach ($arrayJSON as $iter => $subarray) {
	          foreach ($subarray as $id => $elemento) {
				$flag = true;

				foreach ($search as $key => $value) {
					if ($elemento[$key] != $value) {
						$flag = false;
						break;
					}
				}

				if ($flag) {
					array_push($array, $elemento);
				}
	          }
	     }
	}else{
		$array = null;
	}

	if (count($array)==0) {
		$array = null;
	}

	return $array;
}

function getFieldsJSON($JSON, $fieldsarray){
	$arrayJSON = array_reverse(readJSON($JSON));
	$array = array();

	if ($arrayJSON != null) {
	     foreach ($arrayJSON as $iter => $subarray) {
	          foreach ($subarray as $id => $elemento) {
				$tempArray = array();

				foreach ($fieldsarray as $value) {
					$partArray = array($value => $elemento[$value]);
					$tempArray = array_merge((array)$tempArray, (array)$partArray);
				}

	              	array_push($array, $tempArray);
	          }
	     }
	}else{
		$array = null;
	}

	return array_unique($array, SORT_REGULAR);
}

function readJSON($tipo){
	if ($tipo != UNKNOWN) {
		$data = NULL;
		$str_datos = "";
		$json_file = JSON_FOLDER . json_type($tipo) . ".json";

		$fp = fopen($json_file, "r+");

		if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock

			while (!feof($fp)) {
				$str_datos .= fread($fp, 8192);
			}

			$data = json_decode($str_datos,true);

			flock($fp, LOCK_UN);    // release the lock
		} else {
			$data = null;
		}

		fclose($fp);

		return $data;
	}

	return NULL;
}

function decrypt ($string) {
    return base64_decode($string);
}

function eliminarfincita($expedientes){
	$expsAlm = getSearchJSON("estadocita", array('FinCita' => true));
	$expedfin = array();

	foreach ($expedientes as $expserv) {
		$flag = false;

		foreach ($expsAlm as $exploc) {
			if ($exploc["id"] == $expserv["id"]) {
				$flag = true;
			}
		}

		if (!$flag) {
			array_push($expedfin, $expserv);
		}
	}

	return $expedfin;
}

// Funciones que buscan en arrays

function tipo_usuario($number){
	// $TIPO_USUARIOS=array(0=>"Administradores", 1=>"Operarios AMS", 2=>"Subcontratas");

	$tipo_usuario = "";

	switch ($number) {
		case 0:
			$tipo_usuario = "Administradores";
			break;
		case 1:
			$tipo_usuario = "Operarios AMS";
			break;
		case 2:
			$tipo_usuario = "Subcontratas";
			break;

		default:
			$tipo_usuario = UNKNOWN;
			break;
	}

	return $tipo_usuario;
}

function estado($number){
	// $ESTADO=array(0=>"NO", 1=>"SI");

	$estado = "";

	switch ($number) {
		case 0:
			$estado = "NO";
			break;
		case 1:
			$estado = "SI";
			break;

		default:
			$estado = UNKNOWN;
			break;
	}

	return $estado;
}

function img_type($tipo){
	// $IMGTYPES=array("gif"=>"image/gif",
	// 					"jpeg"=>"image/jpeg",
	// 					"pjpeg"=>"image/pjpeg",
	// 					"png"=>"image/png",
	// 					"svg+xml"=>"image/svg+xml",
	// 					"tiff"=>"image/tiff",
	// 					"vnd.microsoft.icon"=>"image/vnd.microsoft.icon");

	$img_type = "";

	switch ($tipo) {
		case "gif":
		$img_type = "image/gif";
		break;
		case "jpeg":
		$img_type = "image/jpeg";
		break;
		case "pjpeg":
		$img_type = "image/pjpeg";
		break;
		case "png":
		$img_type = "image/png";
		break;
		case "svg+xml":
		$img_type = "image/svg+xml";
		break;
		case "tiff":
		$img_type = "image/tiff";
		break;
		case "vnd.microsoft.icon":
		$img_type = "image/vnd.microsoft.icon";
		break;

		default:
		$img_type = UNKNOWN;
		break;
	}

	return $img_type;
}

function dia_sem($number){
	// $DIASEM=array(1=>"Lunes", 2=>"Martes", 3=>"Miércoles", 4=>"Jueves", 5=>"Viernes", 6=>"Sábado", 7=>"Domingo");

	$dia_sem = "";

	switch ($number) {
		case 1:
		$dia_sem = "Lunes";
		break;
		case 2:
		$dia_sem = "Martes";
		break;
		case 3:
		$dia_sem = "Miércoles";
		break;
		case 4:
		$dia_sem = "Jueves";
		break;
		case 5:
		$dia_sem = "Viernes";
		break;
		case 6:
		$dia_sem = "Sábado";
		break;
		case 7:
		$dia_sem = "Domingo";
		break;

		default:
		$estado = UNKNOWN;
		break;
	}

	return $dia_sem;
}

function json_type($tipo){
	//  $JSON=array("admin"=>"272dd9f801", "operario"=>"5db655c116", "seguimiento"=>"41f72cba96", "fichero"=>"68f03a0308");

	$json_type = "";

	switch ($tipo) {
		case "admin":
		$json_type = "272dd9f801";
		break;
		case "operario":
		$json_type = "5db655c116";
		break;
		case "seguimiento":
		$json_type = "41f72cba96";
		break;
		case "fichero":
		$json_type = "68f03a0308";
		break;
		case "estadocita":
		$json_type = "d9c9a8e683";
		break;
		case "partes":
		$json_type = "partes";
		break;
		case "partes_base":
		$json_type = "partes_base";
		break;

		default:
		$json_type = UNKNOWN;
		break;
	}

	return $json_type;
}

?>
