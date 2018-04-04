<?php
include("mssql.php");

$conn = new OV_SQLConnect();

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
			'O',
			36802,
			'17bfc945-9b18-4fd7-82fa-4d95f04cbd51',
			GETDATE (),
			'Este es un posible texto',
			null,
			null,
			null,
			null,
			null
		);";

$funciona = $conn->query($query);

if ($funciona) {
     echo "Seguimiento registrado";
}else{
     //Error 1 - Base de datos
     echo "Error al registrar seguimiento: ". utf8_encode($conn->see_errors());
}
?>
