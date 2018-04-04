<?php
     include("./ftp.php");
     echo "Pasa Include<br />";
     $connftp = new OV_FTPConnect();
     echo "Pasa Conectar<br />";

     foreach ($connftp->ov_ftp_list("/") as $val) {
          echo $val . "<br />";
     }

     // $remote_folder = "/ORDENES 36802/";
     // $remote_file = "2017-11-3-1509699936888-FOTOS_0.jpeg";
     // $local_file = "2017-11-3-1509699936888-FOTOS_0.jpeg";
     // $binary = true;
     //
     //
     // if($connftp->ov_ftp_upload($remote_folder, $remote_file, $local_file, $binary)){
     //      echo "Archivo Subido";
     // }else{
     //      echo "Archivo NO Subido";
     // }

     $connftp->ov_ftp_close($connftp);
 ?>
