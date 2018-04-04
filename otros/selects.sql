SELECT expedest.id,
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
FROM TbExpedientesEstados expedest, TbExpedientes exped,TbClientesDirecciones dir
WHERE exped.IdSerie = expedest.IdSerie AND exped.NumeroOrden = expedest.NumeroOrden AND exped.IdDireccion=dir.IdDireccion AND Fincita=0 AND expedest.IdOperario LIKE '6345a0aa-5abb-4cd1-98a7-2e6da0ce7757';

SELECT 	expedest.id,
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
     AND  Fincita=0
     AND 	expedest.IdOperario LIKE '6345a0aa-5abb-4cd1-98a7-2e6da0ce7757';

SELECT 	expedest.id,
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
     AND  Fincita=0
     AND 	expedest.IdOperario LIKE '17bfc945-9b18-4fd7-82fa-4d95f04cbd51';

     17bfc945-9b18-4fd7-82fa-4d95f04cbd51

SELECT 	expedest.id,
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
     AND	expedest.Fincita=0
	 AND	expedest.FechaCita IS NOT NULL
     AND 	expedest.IdOperario LIKE '6345a0aa-5abb-4cd1-98a7-2e6da0ce7757';

     SELECT 	  expedest.FechaCita,
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
     	 AND	exped.IdSerie LIKE 'O' AND exped.NumeroOrden LIKE '36802';

SELECT [IdDireccion], COUNT(*) FROM [TbExpedientes] GROUP BY [IdDireccion];


SELECT *
FROM		TbOperarios
WHERE		IdOperario LIKE '17bfc945-9b18-4fd7-82fa-4d95f04cbd51';

SELECT 	  *
     FROM 	TbExpedientesEstados
     WHERE 	Id LIKE '44b39fa6-c597-4f20-83b2-625061cfd7a7'

SELECT *
FROM		TbExpedientes
WHERE		IdSerie LIKE 'O' AND NumeroOrden LIKE '36802';


UPDATE         TbExpedientesEstados
     SET       Fincita = 0
     WHERE 	Id LIKE '44b39fa6-c597-4f20-83b2-625061cfd7a7'

SET CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON;
UPDATE 	TbExpedientesEstados
SET 	     Fincita = 0
WHERE 	Id LIKE '44b39fa6-c597-4f20-83b2-625061cfd7a7'

SET CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON;
UPDATE 	TbExpedientesEstados
SET 	     citaestablecidaporoperario = true,
          FechaCita =
WHERE 	Id LIKE '44b39fa6-c597-4f20-83b2-625061cfd7a7'



INSERT INTO TbExpedientesSeguimiento (
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
			NEWID(),'O',36802,'17bfc945-9b18-4fd7-82fa-4d95f04cbd51',GETDATE (),'Este es un posible texto',null,null,null,null,null
		);
