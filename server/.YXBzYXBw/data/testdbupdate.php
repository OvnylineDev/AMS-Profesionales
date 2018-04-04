<?php
     $myServer = "AMSServer";
     $myUser = "Qo2iPC4Dt8";
     $myPass = "p_5NM?|YFpN";
     $myDB = "GestionExpedientes";
     $idExpEst = "44b39fa6-c597-4f20-83b2-625061cfd7a7";

     //connection to the database
     echo "Entra en mssql_connect<br />";
     $dbhandle = mssql_connect($myServer, $myUser, $myPass)
       or die("Couldn't connect to SQL Server on $myServer");

     //select a database to work with
     echo "Entra en mssql_select_db<br />";
     $selected = mssql_select_db($myDB, $dbhandle)
       or die("Couldn't open database $myDB");

     //declare the SQL statement that will query the database
     // echo "Hace la query<br />";
     $query = "SELECT 	Id, Fincita
               FROM 	TbExpedientesEstados
               WHERE 	Id LIKE '".$idExpEst."'";

     // echo "Hace la query update<br />";
     // $query = "UPDATE 	TbExpedientesEstados
     //           SET 	     Fincita = 1
     //           WHERE 	Id LIKE '".$idExpEst."'";

     // echo "Hace la query update<br />";
     // $query = "SET CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON;
     //           UPDATE 	TbExpedientesEstados
     //           SET 	     Fincita = 0
     //           WHERE 	Id LIKE '".$idExpEst."'";

     /*
     SET CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON

     SET ANSI_NULLS, \
	QUOTED_IDENTIFIER, \
	CONCAT_NULL_YIELDS_NULL, \
	ANSI_WARNINGS, \
	ANSI_PADDING, \
	ARITHABORT ON; \
      */

     echo $query."<br />";

     /*
     UPDATE TbExpedientesEstados
SET Fincita = 0
WHERE Id LIKE '44b39fa6-c597-4f20-83b2-625061cfd7a7'
     */

     //execute the SQL query and return records
     echo "Entra en mssql_query<br />";
     $result = mssql_query($query);

     echo "Entra en mssql_num_rows<br />";
     $numRows = mssql_num_rows($result);
     echo $numRows . " Row" . ($numRows == 1 ? "" : "s") . " Returned<br />";
     echo mssql_get_last_message()."<br />";
     // $row = mssql_fetch_array($result);
     //
     // echo "Id Expediente: ".$row['Id']." Fin cita establecida: ".$row['Fincita']."<br /><br />";

     //display the results
     while($row = mssql_fetch_array($result))
     {
       echo "<li>" . $row["Id"] . " " . $row["ObservacionesCita"] . " " . $row["Fincita"] . "</li>";
     }
     //close the connection
     echo "Entra en mssql_close<br />";
     mssql_close($dbhandle);
?>
