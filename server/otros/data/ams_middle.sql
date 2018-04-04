DROP DATABASE IF EXISTS ams_middle;
CREATE DATABASE ams_middle;

DROP TABLE IF EXISTS ams_middle.TbUsuarios;
CREATE TABLE ams_middle.TbUsuarios(
    c1  VARCHAR(36)  NOT NULL,         /*IdUsuario*/              /*PK*/
	c2  VARCHAR(50)  NOT NULL,         /*Usuario*/
	c3  VARCHAR(10)      NULL,         /*password*/
	c4  VARCHAR(500)      NULL,         /*foto*/
	c5  VARCHAR(5)   NOT NULL,         /*inactivo*/
	c6  VARCHAR(100)     NULL,         /*fuente*/
	c7  VARCHAR(5)              NULL,         /*tamaño*/
	c8  VARCHAR(5)              NULL,         /*color*/
	c9  VARCHAR(36)     NOT NULL,         /*IdPerfil*/               /*FK-TbPerfiles(IdPerfil)*/
	c10 VARCHAR(36)         NULL,         /*IdSerieDefecto*/         /*FK-TbSeries(IdSerie)*/
	c11 VARCHAR(100)     NULL,         /*email*/
	c12 VARCHAR(5)     NOT NULL,         /*MesesMuestro*/
	c13 VARCHAR(20)      NULL,         /*superclavemovil*/
	c14 VARCHAR(50)      NULL,         /*skin*/
    
    CONSTRAINT  pk_usu                 PRIMARY KEY (c1)
);

DROP TABLE IF EXISTS ams_middle.TbTiposFichero;
CREATE TABLE ams_middle.TbTiposFichero(
    c1 VARCHAR(36)      NOT NULL,
    c2 VARCHAR(50)   NOT NULL,
    
    CONSTRAINT  pk_tipfich             PRIMARY KEY (c1)
);

DROP TABLE IF EXISTS ams_middle.TbOperarios;
CREATE TABLE ams_middle.TbOperarios(
    c1  VARCHAR(36)      NOT NULL,  /*IdOperario*/                         /*PK*/
    c2  VARCHAR(50)   NOT NULL,  /*Operario*/
    c3  VARCHAR(50)       NULL,  /*Gremio*/
    c4  VARCHAR(300)      NULL,  /*observaciones*/
    c5  VARCHAR(1)       NOT NULL,  /*inactivo*/
    c6  VARCHAR(5)      NOT NULL,  /*colorcalendario*/
    c7  VARCHAR(10) NOT NULL,  /*CosteHora*/
    c8  VARCHAR(20)       NULL,  /*Movil*/
    c9  VARCHAR(20)       NULL,  /*Telefono*/
    c10 VARCHAR(100)      NULL,  /*email*/
    c11 VARCHAR(36)          NULL,  /*IdTipoServicio*/                      /*FK-TbTipoServicio(IdTipoServicio)*/
    c12 VARCHAR(1)       NOT NULL,  /*Subcontrata*/
    c13 VARCHAR(100)      NULL,  /*Usuario*/
    c14 VARCHAR(15)       NULL,  /*PassWord*/
    c15 VARCHAR(50)      NOT NULL,  /*fechaalta*/
    c16 VARCHAR(36)      NOT NULL,  /*idusuario*/                           /*FK+TbUsuarios(IdUsuario)*/
    c17 VARCHAR(36)          NULL,  /*IdCentroCoste*/                       /*FK-TbCentrosCoste(IdCentroCoste)*/
    c18 VARCHAR(36)          NULL,  /*idagentecomunicado*/                  /*FK-TbAgentesComunicado(IdAgenteComunicado)*/
    
    CONSTRAINT pk_op             PRIMARY KEY (c1),
    CONSTRAINT fk_op_usu         FOREIGN KEY (c16)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT
);

DROP TABLE IF EXISTS ams_middle.TbExpedientes;
CREATE TABLE ams_middle.TbExpedientes(
    c1  CHAR(36)        NOT NULL,  	/*IdSerie*/                         /*PK*/
    c2  VARCHAR(10)             NOT NULL,   /*NumeroOrden*/                     /*PK*/
    c3  VARCHAR(50)        NOT NULL,   /*FechaRegistro*/
    c4  VARCHAR(36)        NOT NULL,   /*IdUsuario*/                       /*FK+TbUsuarios(IdUsuario)*/
    c5  VARCHAR(36)        NOT NULL,   /*IdDireccion*/                     /*FK-TbClientesDirecciones(IdDireccion)*/
    c6  VARCHAR(36)            NULL,	/*IdAseguradora*/                   /*FK-TbAseguradoras(IdAseguradora)*/
    c7  VARCHAR(100)        NULL,	/*Aseguradora*/
    c8  VARCHAR(25)         NULL,   /*NumeroSiniestro*/
    c9  VARCHAR(25)         NULL,	/*NumeroExpediente*/
    c10 VARCHAR(50)                NULL,	/*FechaInicio*/
    c11 VARCHAR(50)                NULL,	/*FechaFin*/
    c12 VARCHAR(50)         NULL,	/*NumeroPoliza*/
    c13 VARCHAR(1)         NOT NULL,   /*supervisada*/
    c14 VARCHAR(1)         NOT NULL,	/*Urgente*/
    c15 VARCHAR(1)         NOT NULL,	/*visitada*/
    c16 VARCHAR(300)        NULL,	/*RutaExpediente*/
    c17 VARCHAR(50)         NULL,	/*Contacto*/
    c18 VARCHAR(50)         NULL,	/*TelefonoContacto*/
    c19 VARCHAR(36)            NULL,	/*IdAdministrador*/                  /*FK-TbAdministadoresFincas(IdAdministrador)*/
    c20 VARCHAR(36)            NULL,	/*IdPerito*/                         /*FK-TbPeritos(IdPerito)*/
    c21 VARCHAR(15)  NOT NULL,	/*ImporteFranquicia*/
    c22 VARCHAR(36)            NULL,	/*IdLocalizacion*/                   /*FK-TbLocalizacion(IdLocalizacion)*/
    c23 VARCHAR(36)            NULL,   /*IdTipoIncidencia*/                 /*FK-TbTiposIncidencia(IdTipoIncidencia)*/
    c24 VARCHAR(15)  NOT NULL,	/*TotalManoObra*/
    c25 VARCHAR(15)  NOT NULL,	/*TotalPintura*/
    c26 VARCHAR(15)  NOT NULL,	/*TotalVarios*/
    c27 VARCHAR(15)  NOT NULL,	/*TotalBaseFactura*/
    c28 VARCHAR(15)  NOT NULL,	/*TotalBeneficio*/
    c29 VARCHAR(5)         NOT NULL,	/*NumeroFacturas*/
    c30 VARCHAR(50)         NULL,   /*MaquinaUso*/
    c31 VARCHAR(36)        NOT NULL,	/*idestadoorden*/                    /*FK-TbEstadosOrden(IdEstadoOrden)*/
    c32 VARCHAR(400)        NULL,   /*Observaciones*/
    c33 VARCHAR(200)        NULL,   /*perjudicado*/
    c34 VARCHAR(200)        NULL,   /*DireccionPerjudicado*/
    c35 VARCHAR(50)         NULL,   /*TelefonosPerjudicado*/
    c36 VARCHAR(400)        NULL,   /*ObservacionesPerjudicado*/
    c37 VARCHAR(36)        NOT NULL,	/*iddiseño*/                         /*FK-TbSeriesDiseños(IdDiseño)*/
    c38 VARCHAR(36)            NULL,   /*IdAgenteSeguro*/                   /*FK-TbAgentesSeguros(IdAgenteSeguro)*/
    c39 VARCHAR(30)         NULL,   /*ReferenciaAgenteSeguro*/
    
    CONSTRAINT pk_exp               PRIMARY KEY (c1,c2),
    CONSTRAINT fk_exp_usu           FOREIGN KEY (c4)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT
    /*La tabla tiene FK pero no van a ser importadas*/
);

DROP TABLE IF EXISTS ams_middle.TbExpedientesEstados;
CREATE TABLE ams_middle.TbExpedientesEstados(
    c1  VARCHAR(36)    NOT NULL,       /*Id*/                              /*PK*/
    c2  VARCHAR(36)    NOT NULL,       /*IdSerie*/                         /*FK+TbExpedientes(IdSerie)*/
    c3  VARCHAR(10)         NOT NULL,       /*NumeroOrden*/                     /*FK+TbExpedientes(NumeroOrden)*/
    c4  VARCHAR(36)    NOT NULL,       /*IdEstadoOrden*/                   /*FK-TbEstadosOrden(IdEstadoOrden)*/
    c5  VARCHAR(50)    NOT NULL,       /*FechaApunte*/
    c6  VARCHAR(500)    NULL,       /*ObservacionesCita*/
    c7  VARCHAR(36)        NULL,       /*IdOperario*/                      /*FK+TbOperarios(IdOperario)*/
    c8  VARCHAR(5)    NOT NULL,       /*DuracionCita*/
    c9  VARCHAR(1)     NOT NULL,       /*Fincita*/
    c10 VARCHAR(50)        NULL,       /*FechaCita*/
    c11 CHAR(36)        NULL,       /*IdUsuario*/                       /*FK+TbUsuarios(IdUsuario)*/                   
    c12 VARCHAR(1)     NOT NULL,	    /*CheckEstableceCitaSubcontrata*/
    c13 CHAR(36)        NULL,       /*IdUsuarioRevision*/               /*FK+TbUsuarios(IdUsuario)*/                   
    c14 VARCHAR(500)    NULL,       /*observacionestrabajo*/
    c15 VARCHAR(50)        NULL,       /*FechaUsuarioRevisa*/
    
    CONSTRAINT  pk_expes            PRIMARY KEY (c1),
    CONSTRAINT  fk_expes_exp        FOREIGN KEY (c2,c3)
        REFERENCES ams_middle.TbExpedientes (c1,c2)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT  fk_expes_op         FOREIGN KEY (c7)
        REFERENCES ams_middle.TbOperarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_expes_usu         FOREIGN KEY (c11)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_expes_usurev      FOREIGN KEY (c13)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT
    /*La tabla tiene más FK pero no van a ser importadas*/
);

DROP TABLE IF EXISTS ams_middle.TbExpedientesSeguimiento;
CREATE TABLE ams_middle.TbExpedientesSeguimiento(
    c1  VARCHAR(36)     NOT NULL,        /*id*/                       /*PK*/
	c2  VARCHAR(36)     NOT NULL,        /*idserie*/                  /*FK+TbExpedientes(IdSerie)*/
	c3  VARCHAR(10)          NOT NULL,        /*numeroorden*/              /*FK+TbExpedientes(NumeroOrden)*/
	c4  VARCHAR(36)         NULL,        /*IdOperario*/               /*FK+TbOperarios(IdOperario)*/
	c5  VARCHAR(50)     NOT NULL,        /*Fecha*/
	c6  VARCHAR(500) NOT NULL,        /*Observaciones*/
	c7  VARCHAR(36)         NULL,        /*idusuario*/                 /*FK+TbUsuarios(IdUsuario)*/
	c8  VARCHAR(50)         NULL,        /*FechaUsuarioRevisa*/        
	c9  VARCHAR(36)         NULL,        /*IdUsuarioRevision*/         /*FK+TbUsuarios(IdUsuario)*/
	c10 VARCHAR(36)         NULL,        /*IdAgenteemisor*/            /*FK-TbAgentesComunicado(IdAgenteComunicado)*/
	c11 VARCHAR(36)         NULL,        /*IdAgenteDestinatario*/      /*FK-TbAgentesComunicado(IdAgenteComunicado)*/
    
    CONSTRAINT  pk_expeseg            PRIMARY KEY (c1),
    CONSTRAINT  fk_expseg_exp         FOREIGN KEY (c2,c3)
        REFERENCES ams_middle.TbExpedientes (c1,c2)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT  fk_expeseg_op         FOREIGN KEY (c4)
        REFERENCES ams_middle.TbOperarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_expeseg_usu         FOREIGN KEY (c7)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_expeseg_usurev      FOREIGN KEY (c9)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT
);

DROP TABLE IF EXISTS ams_middle.TbExpedientesFicheros;
CREATE TABLE ams_middle.TbExpedientesFicheros(
    c1  VARCHAR(36)     NOT NULL,        /*Id*/                       /*PK*/
	c2  VARCHAR(36)     NOT NULL,        /*IdSerie*/                  /*FK+TbExpedientes(IdSerie)*/
	c3  VARCHAR(10)          NOT NULL,        /*NumeroOrden*/              /*FK+TbExpedientes(NumeroOrden)*/
	c4  VARCHAR(50)  NOT NULL,        /*Descripcion*/
	c5  VARCHAR(300) NOT NULL,        /*ruta*/
	c6  VARCHAR(36)         NULL,        /*IdTipoFichero*/            /*FK+TbTiposFichero(IdTipoFichero)*/
	c7  VARCHAR(50)     NOT NULL,        /*fecharegistro*/
	c8  VARCHAR(36)         NULL,        /*idoperario*/               /*FK+TbOperarios(IdOperario)*/
	c9  VARCHAR(36)         NULL,        /*idusuario*/                /*FK+TbUsuarios(IdUsuario)*/
	c10 VARCHAR(50)         NULL,        /*fecharegistrousuario*/
    
    CONSTRAINT  pk_expfich            PRIMARY KEY (c1),
    CONSTRAINT  fk_expfich_exp        FOREIGN KEY (c2,c3)
        REFERENCES ams_middle.TbExpedientes (c1,c2)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_expfich_tipfich     FOREIGN KEY (c6)
        REFERENCES ams_middle.TbTiposFichero (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT  fk_expfich_op         FOREIGN KEY (c8)
        REFERENCES ams_middle.TbOperarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_expfich_usu         FOREIGN KEY (c9)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT
);

/*Esta tabla se edita por completo*/
DROP TABLE IF EXISTS ams_middle.TbIncidenciasFestivos;
CREATE TABLE ams_middle.TbIncidenciasFestivos(
    c1  VARCHAR(36)     NOT NULL,     /*Id*/                       /*PK*/
	c2  VARCHAR(15)      NULL,        /*Cif*/
	c3  VARCHAR(75)  NOT NULL,        /*Nombre*/
	c4  VARCHAR(100) NOT NULL,        /*Direccion*/
	c5  VARCHAR(50)  NOT NULL,        /*localidad*/
	c6  VARCHAR(50)  NOT NULL,        /*provincia*/
	c7  VARCHAR(20)      NULL,        /*telefono*/
	c8  VARCHAR(20)      NULL,        /*movil*/
	c9  VARCHAR(36)     NOT NULL,        /*idoperario*/               /*FK+TbOperarios(IdOperario)*/
	c10 VARCHAR(36)     NOT NULL,        /*idaseguradora*/            /*FK-TbAseguradoras(IdAseguradora)*/
	c11 VARCHAR(400)     NULL,        /*observaciones*/
	c12 VARCHAR(50)     NOT NULL,        /*fecharegistro*/
	c13 VARCHAR(36)         NULL,        /*idusuariorevisa*/          /*FK+TbUsuarios(IdUsuario)*/
	c14 VARCHAR(50)         NULL,        /*fecharevision*/
	c15 VARCHAR(25)      NULL,        /*numeroexpediente*/
	c16 VARCHAR(25)      NULL,        /*numerosiniestro*/
	c17 VARCHAR(50)      NULL,        /*numeropoliza*/
	c18 VARCHAR(50)      NULL,        /*contacto*/
	c19 VARCHAR(50)      NULL,        /*telefonocontacto*/
	c20 VARCHAR(200)     NULL,        /*perjudicado*/
	c21 VARCHAR(200)     NULL,        /*direccionperjudicado*/
	c22 VARCHAR(50)      NULL,        /*telefonoperjudicado*/
	c23 VARCHAR(400)     NULL,        /*observacionesperjudicado*/
    
    CONSTRAINT  pk_incifest           PRIMARY KEY (c1),
    CONSTRAINT  fk_incifest_op        FOREIGN KEY (c9)
        REFERENCES ams_middle.TbOperarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_incifest_usu        FOREIGN KEY (c13)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT
);

DROP TABLE IF EXISTS ams_middle.TbIncidenciasFestivos_Ficheros;
CREATE TABLE ams_middle.TbIncidenciasFestivos_Ficheros(
    c1 VARCHAR(36)     NOT NULL,        /*Id*/                       /*PK*/
	c2 VARCHAR(36)     NOT NULL,        /*IdIncidencia*/             /*FK+TbIncidenciasFestivos(Id)*/
	c3 VARCHAR(50)  NOT NULL,        /*Descripcion*/
	c4 VARCHAR(300) NOT NULL,        /*ruta*/
	c5 VARCHAR(36)     NOT NULL,        /*IdTipoFichero*/            /*FK+TbTiposFichero(IdTipoFichero)*/
	c6 VARCHAR(36)         NULL,        /*IdUsuarioRevisa*/          /*FK+TbUsuarios(IdUsuario)*/
	c7 VARCHAR(50)         NULL,        /*FechaRevision*/
	c8 VARCHAR(50)     NOT NULL,        /*Fecharegistro*/
    
    CONSTRAINT  pk_incifestfich             PRIMARY KEY (c1),
    CONSTRAINT  fk_incifestfich_incifest    FOREIGN KEY (c2)
        REFERENCES ams_middle.TbIncidenciasFestivos (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_incifestfich_tipfich     FOREIGN KEY (c5)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT,
    CONSTRAINT fk_incifestfich_usu          FOREIGN KEY (c6)
        REFERENCES ams_middle.TbUsuarios (c1)
        ON DELETE RESTRICT 
        ON UPDATE RESTRICT
);