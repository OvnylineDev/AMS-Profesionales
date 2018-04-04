<?php
    require_once('../data/db.inc.php'); 

    require_once('../data/mssql.php');
    require_once('../data/mysql.php');

    $mssqlconn = new mssqldb();
    $mysqlconn = new mysqldb();

    $mssqlconn->connect();
    $mysqlconn->connect();

    set_time_limit(0);
    function addQuote($string)
    {
        return "'".$string."'";
    }
    function addTilde($string)
    {
        return "`".$string."`";
    }

    // Get Table Structures
    $temp = array(  'TbUsuarios',
                    'TbTiposFichero',
                    'TbOperarios',
                    'TbExpedientes',
                    'TbExpedientesEstados',
                    'TbExpedientesSeguimiento',
                    'TbExpedientesFicheros',
                    'TbIncidenciasFestivos',
                    'TbIncidenciasFestivos_Ficheros');
    $usucol = array('IdUsuario');
    
    if (!empty($temp)){
        $i = 1;
        echo $temp[1];
        foreach ($temp as $table)
        {
            //echo '<p>====> '.$i.'. '.$table."\n</p>";
            //echo "<p>=====> Getting info table ".$table." from SQL Server\n</p>";
            $sql = "SELECT COLUMN_NAME FROM ".MSSQL_DATABASE.".information_schema.columns where table_name = '".$table."'";
            echo "<p> ".$sql." </p>";
            $fields = $mssqlconn->query($sql);
            //if ($res) 
            //{
            
                echo "<p>=====> Getting data from table ".$table." on SQL Server\n</p>";
                $sql = "SELECT * FROM ".MSSQL_DATABASE.".dbo.".$table;
                echo "<p>".$sql."</p>";
                $qres = $mssqlconn->query($sql);
                //$numrow = $mssqlconn->num_rows($qres);
                //echo "======> Found ".number_format($numrow,0,',','.')." rows\n";
                if ($qres)
                {
                    echo "=====> Inserting to table ".$table." on MySQL\n";
                    $numdata = 0;
                    while ($qrow = sqlsrv_fetch_array ($qres, SQLSRV_FETCH_ASSOC))
                    {
                        
                        $fieldsarr=sqlsrv_fetch_array($fields);
                        foreach($qrow as $r){
                            //echo $r."<br/>";
                            if(is_null($r)){
                                echo "pasa";
                                $r=null;
                            }else{
                                echo "pasa2";
                            }
                            
                            if(gettype($r)=='object'){
                                echo "<br>".get_class($r)."<br>";
                                switch (get_class($r)) {
                                    case 'DateTime':
                                        $r=$r->format('Y-m-d H:i:s');
                                        break;
                                    default: echo "<h1>objeto desconocido</h1>";
                                }
                            }else{
                                $r=strval($r);
                            }
                        }
                        
                        if (!empty($qrow))
                        {
                            //$datas = array_map('addQuote', $datas);
                            //$fields = 
                            $mysql = "INSERT INTO ".$table." VALUES (";
                            //('".implode('\',\'',$qrow).
                            foreach($qrow as $r){
                                if(gettype($r)=='object'){
                                    echo "<br>".get_class($r)."<br>";
                                    switch (get_class($r)) {
                                        case 'DateTime':
                                            $mysql.="'".$r->format('Y-m-d H:i:s')."',";
                                            break;
                                        default: echo "<h1>objeto desconocido</h1>";
                                    }
                                }else{
                                    $mysql.="'".strval($r)."',";
                                }
                            }
                            $mysql = substr ( $mysql, 0, strlen($mysql)-1 );
                            $mysql.=");";
                            
                            echo "<p>".$mysql."</p>";
                            //$mysql = mysql_real_escape_string($mysql);
                            echo $mysql."\n";
                            $q = $mysqlconn->query($mysql);
                            $numdata += ($q ? 1 : 0 );
                        }
                    }
                }
                    echo "======> ".number_format($numdata,0,',','.')." data inserted\n\n";
            }
            //}
            $i++;
        }
    echo "Done!\n";

    $mssqlconn->disconnect($mssqlconn);
    $mysqlconn->disconnect($mysqlconn);
?>