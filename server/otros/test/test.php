<?php
     include("./mssql.php");
     echo "Pasa Include<br />";
     $conn = new OV_SQLConnect();
     echo "Pasa Conectar<br />";
     $query = "SELECT IdOperario, Usuario, PassWord FROM TbOperarios";
     echo "Pasa Query<br />";
     $getUsers = $conn->query($query);
     echo "Pasa getUsers<br />";
     $numRows =  $conn->num_rows($getUsers);
     echo "Pasa Num Rows<br />";
     echo $numRows . " Row" . ($numRows == 1 ? "" : "s") . " Returned";
?>
