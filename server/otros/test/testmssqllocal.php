<?php
     $myServer = "AMSServer";
     $myUser = "Qo2iPC4Dt8";
     $myPass = "p_5NM?|YFpN";
     $myDB = "GestionExpedientes";

     //connection to the database
     sqlsrv_connect($myServer, array("Database"=>$myDB, "UID"=>$myUser, "PWD"=>$myPass)) or die('Could not connect to the server!');

     //declare the SQL statement that will query the database
     $query = "SELECT * ";
     $query .= "FROM TbOperarios";

     //execute the SQL query and return records
     $result = sqlsrv_query($dbhandle, $query);

     $numRows = mssql_num_rows($result);
     echo $numRows . " Row" . ($numRows == 1 ? "" : "s") . " Returned";

     //display the results
     /*while($row = mssql_fetch_array($result))
     {
       echo "<li>" . $row["id"] . $row["name"] . $row["year"] . "</li>";
     }*/
     //close the connection
     mssql_close($dbhandle);
?>
