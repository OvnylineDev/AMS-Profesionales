<?php
header('Access-Control-Allow-Headers: Authorization,Content-type,X-ApiKey');

$xApiKey='c3a2016d-f2f3-5fc7-8f8f-6ad7697c61cd';

$extern_url="http://avilamultiasistencia.es/";

//TIPO_USUARIOS=array(0=>"Administradores", 1=>"Operarios AMS", 2=>"Subcontratas");

$ESTADO=array(0=>"NO", 1=>"SI");

//Recogida de parámetros
$operation=$_REQUEST["op"];

$bbdd="GestionExpedientes";

//Conexión con la base de datos
$serverName = "OVNYLINE04\SQLEXPRESS";  
$connectionOptions = array("Database"=>$bbdd, "UID"=>"Ovnyline", "PWD"=>"AMS2016ovnyline");  
  
switch($operation)
{
	
	/*case "send_mail": 	include("./sendmail.php");
	
						$val=explode("&",urldecode($values));
						$socio=explode("=",$val[0]);
						$email=explode("=",$val[1]);
						$tipo_consulta=explode("=",$val[2]);
						$consulta=explode("=",$val[3]);
						
						$send_mail_ams="ams@avilamultiasistencia.com";
						switch($tipo_consulta[1])
						{
							case "1": $send_mail_anpier="ams@avilamultiasistencia.com"; break;							
							case "2":
							case "3": 
							case "4": $send_mail_anpier="ams@avilamultiasistencia.com"; break;

						}
												
						//ENVIO EMAIL
						$message="Se ha recibido la siguiente consulta ".$tipo_consulta[1]." desde la aplicación móvil de AMS Profesionales:<br>";
						$message.="<p><b>Operario:</b> ".$socio[1]."</p>";
						$message.="<p><b>Mensaje:</b></p>";
						$message.=$consulta[1];
						$message.="<br><br><br>";
						$message.="Puede contactar con esta persona a través de su correo electrónico: ".$email[1]."<br><br>";	
						$geekMail = new geekMail(); 
						$geekMail->setMailType('html');


						$geekMail->from($email[1]); 
						$geekMail->to($send_mail_anpier);
						//$geekMail->cc($email[1]);
						$geekMail->bcc("desarrollo1@somosidea.com"); 
						$geekMail->subject("Mensaje ".$tipo_consulta[1]." enviado desde la app de AMS");   
						$geekMail->message($message);
						if(!$geekMail->send())
						{
							echo json_encode(array('status'=>'KO', 'error'=>'Error al enviar la consulta'));
							return false; 
						}
					
						echo json_encode(array('status'=>'OK', 'result'=>'Consulta enviada correctamente'));
							
						break;*/
		
	case 'login':   $idoperario = $_REQUEST['usuario'];  
					$passwoperario = $_REQUEST['clave'];  
					
					$conn=connectDB();
	
					$query = "SELECT * FROM ".$bbdd.".dbo.TbOperarios WHERE inactivo='0' AND Usuario = '".$idoperario."'";
					
					$getUser = sqlsrv_query($conn, $query);  
					if($getUser === false)  
					{
						echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
						return false;
					}
					  
					if(sqlsrv_has_rows($getUser))  
					{  
						$rowCount = sqlsrv_num_rows($getUser);  
						if($rowCount>1)
						{
							echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'Más de un usuario con el mismo identificador' ));
							return false;
						}
						
						$row = sqlsrv_fetch_array($getUser, SQLSRV_FETCH_ASSOC);
						if($row['Usuario']==$idoperario && $row['PassWord']==$passwoperario)
						{
							echo json_encode(array('status'=>'OK', 'usuario'=>$row['Usuario'], 'token'=>''));						
						}
						else 
						{
							echo json_encode(array( 'status'=>'KO', 'error'=>'3', 'message'=>'No coinciden los datos de acceso' ));
							return false;
						}
					}  
					else  
					{  
						echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No existe el usuario' ));
						return false;
					}  
					  
					/* Free the statement and connection resources. */  
					sqlsrv_free_stmt( $getUser );  
					sqlsrv_close( $conn );  
					break;  
					
	case 'inicio':  $autorizacion=check_authorization();
										
					if($autorizacion[0]==0)
					{
						$conn=connectDB();
	
						$query = "SELECT * FROM ".$bbdd.".dbo.TbOperarios WHERE inactivo='0' AND Usuario='".$autorizacion[1]."'";
						
						$getUser = sqlsrv_query($conn, $query);  
						if($getUser === false)  
						{
							echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
							return false;
						}
						  
						if(sqlsrv_has_rows($getUser))  
						{  
							$row = sqlsrv_fetch_array($getUser, SQLSRV_FETCH_ASSOC);
		
							$query2 = "SELECT * FROM ".$bbdd.".dbo.TbAgentesComunicado WHERE idAgenteComunicado='".$row['idagentecomunicado']."'";
							$getAgente = sqlsrv_query($conn, $query2);  
							if($getAgente === false)  
							{
								echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
								return false;
							}
							if(sqlsrv_has_rows($getAgente))  
							{ 
								$row2 = sqlsrv_fetch_array($getAgente, SQLSRV_FETCH_ASSOC);
								
								echo json_encode(array('status'=>'OK', 'result'=>'', 'usuario'=>$row['Usuario'], 'email'=>$row['email'],  'subcontrata'=>$row['Subcontrata'], 'operario'=>$row['Operario'], 'gremio'=> utf8_encode($row['Gremio']), 'agenteComunicado'=>$row2['AgenteComunicado'], 'tipoAgenteComunicado'=>$row2['tipo'], 'token'=>$row['IdOperario'], ));	
							}	
							else  
							{  
								echo json_encode(array( 'status'=>'KO', 'error'=>'3', 'message'=>'No existe el estado' ));
								return false;
							}  			
						}  
						else  
						{  
							echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>'No existe el usuario' ));
							return false;
						}  
						  
						/* Free the statement and connection resources. */  
						sqlsrv_free_stmt( $getUser );  
						sqlsrv_free_stmt( $getEstado );  
						sqlsrv_close( $conn );  
					}	
					else  
					{  
						echo json_encode(array( 'status'=>'KO', 'error'=>'5', 'message'=>$autorizacion[1] ));
						return false;
					}  					
					
					break;  
					
	case 'urgencias':  $autorizacion=check_authorization();
										
					if($autorizacion[0]==0)
					{
						$conn=connectDB();

						$query = "INSERT INTO ".$bbdd.".dbo.TbExpedientesEstados (id, IdOperario, Fecha, Observaciones, IdAgenteDestinatario) VALUES (NEWID(),'operario','2016-12-26 13:00:26.887', 'Observaciones+en+el+paso','idagente')";	
							  
						$setParte = sqlsrv_query($conn, $query);  
						if($setParte === false)  
						{
							echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
							return false;
						}
						 						
						/* Free the statement and connection resources. */  
						sqlsrv_free_stmt( $setParte );  
						sqlsrv_close( $conn );  
					}	
					else  
					{  
						echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>$autorizacion[1] ));
						return false;
					}  		
						
					break; 
					
	case 'partes':  $autorizacion=check_authorization();
										
					if($autorizacion[0]==0)
					{
						$conn=connectDB();
						
						$fecha1="CONVERT(datetime2,CONVERT(CHAR(10), GETDATE(), 120)+' 00:00:00')";
						$fecha2="CONVERT(datetime2,CONVERT(CHAR(10), GETDATE(), 120)+' 23:59:59')";
						
						//$query = "SELECT * FROM ".$bbdd.".dbo.VistaExpedientes WHERE IdOperario = '".$autorizacion[1]."' AND FinCita='0' AND FechaApunte BETWEEN ".$fecha1." AND ".$fecha2." ORDER BY FechaCita DESC";	
						
						$query = "SELECT * FROM ".$bbdd.".dbo.TbExpedientesEstados WHERE IdOperario = '".$autorizacion[1]."' AND FinCita='0' AND datalength(FechaApunte)!=0 ORDER BY FechaApunte DESC";
								
						$getPartesUsuario = sqlsrv_query($conn, $query);  
						if($getPartesUsuario === false)  
						{
							echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
							return false;
						}
						  
						if(sqlsrv_has_rows($getPartesUsuario))  
						{  
							$codigo='';							
							$result=array();
							while($row = sqlsrv_fetch_array($getPartesUsuario, SQLSRV_FETCH_ASSOC))
							{		
								$query2 = "SELECT * FROM ".$bbdd.".dbo.VistaExpedientes WHERE NumeroOrden = '".$row["NumeroOrden"]."'";
								$getVistaExpedientes = sqlsrv_query($conn, $query2);  
								if($getVistaExpedientes === false)  
								{
									echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
									return false;
								}
								if(sqlsrv_has_rows($getVistaExpedientes))  
								{  				
									$result2=array();
									$row2 = sqlsrv_fetch_array($getVistaExpedientes, SQLSRV_FETCH_ASSOC);
								
									$result[]=$row2;
									$codigo.='<tr onclick="window.location.href=\'parte.html?id='.$row["NumeroOrden"].'\'">
													<td>'.$row["NumeroOrden"].'</td>
													<td>'.$row2["Estado"].'</td>
													<td>'.$row2["direccion"]." ".$row["localidad"]." - ".$row2["codigopostal"]." ".$row2["provincia"].'</td>
													<td>'.date_format($row["FechaApunte"], 'Y-m-d h:m').'</td>
											  </tr>';
								}
							}
												
							echo json_encode(array('status'=>'OK', 'result'=>$result, 'codigo'=>utf8_encode($codigo) ));						
						}  
						else  
						{  
							echo json_encode(array( 'status'=>'KO', 'error'=>'3', 'message'=>utf8_encode('No hay ningún parte') ));
							return false;
						}  
												  
						/* Free the statement and connection resources. */  
						sqlsrv_free_stmt( $getPartesUsuario );  
						sqlsrv_close( $conn );  
					}	
					else  
					{  
						echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>$autorizacion[1] ));
						return false;
					}  		
						
					break; 					
					
	case 'parte':  $autorizacion=check_authorization();
	
					$datos = $_REQUEST['data'];  
					foreach($datos as $dat)
					{
						if($dat[0]=="id")
						{
							$num_orden=$dat[1];
						}
					}
										
					if($autorizacion[0]==0)
					{
						$conn=connectDB();
						
						$query = "SELECT * FROM ".$bbdd.".dbo.VistaExpedientes WHERE NumeroOrden='".$num_orden."'";	
						
						$getParteUsuario = sqlsrv_query($conn, $query);  
						if($getParteUsuario === false)  
						{
							echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
							return false;
						}
						  
						if(sqlsrv_has_rows($getParteUsuario))  
						{  
							$rowCount = sqlsrv_num_rows($getParteUsuario);  
							
							$codigo='';							
							$result=array();
							while($row = sqlsrv_fetch_array($getParteUsuario, SQLSRV_FETCH_ASSOC))
							{
								$query = "SELECT * FROM ".$bbdd.".dbo.TbExpedientesEstados WHERE IdOperario = '".$autorizacion[1]."' AND FinCita='0' AND FechaApunte BETWEEN ".$fecha1." AND ".$fecha2." ORDER BY FechaApunte DESC";	
								if($getEstado === false)  
								{
									echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
									return false;
								}
								
								/*$query2 = "SELECT * FROM ".$bbdd.".dbo.TbExpedientes WHERE idAgenteComunicado='".$row['idagentecomunicado']."'";
								$getAgente = sqlsrv_query($conn, $query2);  
								if($getAgente === false)  
								{
									echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
									return false;
								}*/
							
								$result[]=$row;
								$codigo.='<div class="etiqueta_01">
												<label>Serie</label>
												<span>'.$row["Serie"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Nº de orden</label>
												<span>'.$row["NumeroOrden"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Aseguradora</label>
												<span>'.$row["Aseguradora"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Nº Póliza</label>
												<span>'.$row["NumeroPoliza"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Nº Siniestro</label>
												<span>'.$row["NumeroSiniestro"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Nº Expediente</label>
												<span>'.$row["NumeroExpediente"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Supervisada</label>
												<span>'.$ESTADO[$row["supervisada"]].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Urgente</label>
												<span>'.$ESTADO[$row["Urgente"]].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Visitada</label>
												<span>'.$ESTADO[$row["visitada"]].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Cliente</label>
												<span>'.$row["Nombre"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Teléfono</label>
												<span>'.$row["TelefonoContacto"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Fecha inicio</label>
												<span>'.date_format($row["FechaInicio"], 'Y-m-d').'</span>
										  </div>
										   <div class="etiqueta_01">
												<label>Fecha inicio</label>
												<span>'.date_format($row["FechaFin"], 'Y-m-d').'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Dirección</label>
												<span>'.$row["direccion"]." ".$row["localidad"]."<br>".$row["codigopostal"]." ".$row["provincia"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Estado</label>
												<span>'.$row["Estado"].'</span>
										  </div>
										  <div class="etiqueta_01">
												<label>Observaciones</label>
												<span>'.$row["ObservacionesPerjudicado"].'</span>
										  </div>';
							}
												
							echo json_encode(array('status'=>'OK', 'result'=>$result, 'codigo'=>utf8_encode($codigo) ));						
						}  
						else  
						{  
							echo json_encode(array( 'status'=>'KO', 'error'=>'3', 'message'=>utf8_encode('No hay ningún parte') ));
							return false;
						}  
												  
						/* Free the statement and connection resources. */  
						sqlsrv_free_stmt( $getPartesUsuario );  
						sqlsrv_close( $conn );  
					}	
					else  
					{  
						echo json_encode(array( 'status'=>'KO', 'error'=>'4', 'message'=>$autorizacion[1] ));
						return false;
					}  		
						
					break; 
					
	case 'citas':   $numeroorden = $_REQUEST['orden'];  
					
					$conn=connectDB();
	
					$query = "SELECT * FROM ordenes.dbo.TBLDETALLEORDENESTADOS WHERE numeroorden='".$numeroorden."'";					
					$getCitasOrden = sqlsrv_query($conn, $query);  
					if($getCitasOrden === false)  
					{
						echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>utf8_encode(formatError(sqlsrv_errors())) ));
						return false;
					}
					  
					if(sqlsrv_has_rows($getCitasOrden))  
					{  
						$rowCount = sqlsrv_num_rows($getCitasOrden);  
												
						$result=array();
						while($row = sqlsrv_fetch_array($getCitasOrden, SQLSRV_FETCH_ASSOC))
						{
							$result[]=$row;
						}
						echo json_encode(array('status'=>'OK', 'result'=>$result ));						
						
					}  
					else  
					{  
						echo json_encode(array( 'status'=>'KO', 'error'=>'2', 'message'=>'No hay ninguna cita' ));
						return false;
					}  
					  
					/* Free the statement and connection resources. */  
					sqlsrv_free_stmt( $getCitasOrden );  
					sqlsrv_close( $conn );  
					break; 


}

function connectDB()
{
	/* Connect*/  
	$conn = sqlsrv_connect($GLOBALS["serverName"], $GLOBALS["connectionOptions"]);  
	if (!$conn)
	{
		echo json_encode(array( 'status'=>'KO', 'error'=>'1', 'message'=>'Error con. '.utf8_encode(formatError(sqlsrv_errors())) ));
		return false;
	}
	return $conn;
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

?>