<?php


// # define constant, serialize array
// define ("FRUITS", serialize (array ("apple", "cherry", "banana")));
//
// # use it
// $my_fruits = unserialize (FRUITS);
//
//TODO seguimientollamada
	// case 'segllamada':
     //
	// 	$idSerie = '';
	// 	$numeroOrden = '';
	// 	$idOperario = '';
	// 	$observaciones = '';
     //
	// 	$jsonIdSeg = uniqid("seg-", true);
     //
	// 	if(isset($_POST))
	// 	{
	// 		$idSerie = $_POST['idSerie'];
	// 		$numeroOrden = $_POST['numeroOrden'];
	// 		$idOperario = $_POST['idOperario'];
	// 		$observaciones = $_POST['observaciones'];
	// 	}
     //
	// 	include("./data/mssql.php");
	// 	$conndb = new OV_SQLConnect();
     //
	// 	$observaciones = str_replace('+', ' ', $observaciones);
     //
	// 	$query = makeQuerySeg($idSerie, $numeroOrden, $idOperario, $observaciones);
	// 	$funcionaseg = $conndb->query($query);
     //
	// 	$conndb->disconnect();
     //
	// 	if ($funcionaseg) {
	// 		// Añadir al json la información
	// 		saveSeg($jsonIdSeg, $idSerie, $numeroOrden, $idOperario, $observaciones);
     //
	// 		echo json_encode(array(	'status'=>'OK','message'=>"Seguimiento registrado "));
	// 	}else{
	// 		//Error 1 - Base de datos
	// 		echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>"Error al registrar seguimiento: ". utf8_encode($conn->see_errors()). " ". $query));
	// 	}
	// break;
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
//
case 'setfincita':
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

	case 'adminseg':

	     $token = '';

	     if(isset($_POST))
	     {

	     }

	     $seguimientos = getFieldsJSON('seguimiento', array('IdSerie', 'NumeroOrden'));

	     if($seguimientos == null)
	     {
			//Error 1 - Base de datos
	          echo json_encode(array( 'status'=>'KO', 'error'=>'7', 'message'=>'El acceso al JSON no funcionó' ));
	          return false;
	     }

		if(count($seguimientos) != 0){
	          echo json_encode(array('status'=>'OK', 'items'=>$seguimientos, 'token'=>$token));
	          return true;
	     }else{
			//Error 5 - No hay expedientes que mostrar
	          echo json_encode(array('status'=>'KO', 'error'=>'5', 'message'=>"No hay seguimientos almacenados"));
	          return false;
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
	//
	// // function ingresarFicheroDB($idSerie, $NumOrden, $Desc, $ruta, $tipoFichero, $idOperario){
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
//
// // function saveExpCita($id, $jsonIdSeg, $idSerie, $numeroOrden, $idOperario, $FechaCitaOld, $FechaCitaNew){
// 	// Añadir al json la información
// 	$arr_seg = array(
//       $id => array(
// 			"id" => $id,
// 			"jsonIdSeg" => $jsonIdSeg,
// 			"IdSerie" => $idSerie,
// 			"NumeroOrden" => $numeroOrden,
// 			"IdOperario" => $idOperario,
// 			"FechaCitaOld"=> $FechaCitaOld,
// 			"FechaCitaNew"=> $FechaCitaNew,
// 			"FechaMod" => date(DATE_FOR_SQL),
// 			"Gestionado" => false,
// 			"Admin" => ""
// 		)
// 	);
//
// 	return writeJSON($arr_seg, "estadocita");
// }
//
// // function getSegOrd(){
// 	$arrayJSON = getSeguimientos();
// 	$array = array();
//
// 	if ($arrayJSON != null) {
// 	     foreach ($arrayJSON as $iter => $subarray) {
// 	          foreach ($subarray as $id => $seguimiento) {
// 				array_push($array, array('IdSerie' => $seguimiento['IdSerie'], 'NumeroOrden' => $seguimiento['NumeroOrden']));
// 	          }
// 	     }
// 	}else{
// 		$array = null;
// 	}
//
// 	return array_unique($array, SORT_REGULAR);
// }
//
?>
