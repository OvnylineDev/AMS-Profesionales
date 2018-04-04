<?php
     $server = "avilamultiasistencia.dyndns.org";
     $port = 51221;
     $timeout = 20;
     $username = "fSjda9coyB";
     $password = "CB+R<isU3g";
     echo "Entra en conexión FTP<br /><br />";
     $con = ftp_connect($server, $port, $timeout);
     if($con){
          echo "El conector tiene contenido<br /><br />";
     }else{
          echo "El conector ha devuelto false<br /><br />";
     }
     $flag = ftp_login($con,  $username,  $password);
     if($flag){
          echo "ftp_login devuelve true<br /><br />";
     }else{
          echo "ftp_login devuelve fase<br /><br />";
     }
     $flag2 = ftp_pasv($con, true);
     if($flag2){
          echo "ftp_pasv devuelve true<br /><br />";
     }else{
          echo "ftp_pasv devuelve fase<br /><br />";
     }
     // $directory = ftp_nlist($con,'');
     //echo is_array(ftp_nlist($con, "")) ? "Conectado!<br /><br />" : "No Conectado! :(<br /><br />";
     // echo '<pre>'.print_r($directory,true).'</pre>';

     foreach (ftp_nlist($con, "") as $val) {
          echo $val . "<br />";
     }
     
     ftp_close($con);
 ?>
