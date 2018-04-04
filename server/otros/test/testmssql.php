<?php
     $myServer = "AMSServer";
     $myUser = "Qo2iPC4Dt8";
     $myPass = "p_5NM?|YFpN";
     $myDB = "GestionExpedientes";

     //connection to the database
     $dbhandle = mssql_connect($myServer, $myUser, $myPass)
       or die("Couldn't connect to SQL Server on $myServer");

     //select a database to work with
     $selected = mssql_select_db($myDB, $dbhandle)
       or die("Couldn't open database $myDB");

     //declare the SQL statement that will query the database
     $query = "SELECT * ";
     $query .= "FROM TbOperarios";

     //execute the SQL query and return records
     $result = mssql_query($query);

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
