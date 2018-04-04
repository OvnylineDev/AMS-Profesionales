<?php
/**
 *
 */
class OV_FTPConnect{

     protected $ov_ftpcon = array(
          "ftpserver" => "avilamultiasistencia.dyndns.org",
          "ftpport"=>51221,
          "ftptime"=>20,
          "ftpuser" => "fSjda9coyB",
          "ftppass" => "CB+R<isU3g"
     );

     protected $_connection;

     function __construct(){
          $this->_connection = $this->ov_create_ftp_connection();

          if ($this->_connection) {
               //echo "Conexión realizada<br />";
               if ($this->ov_ftp_login()) {
                    $this->ov_ftp_pasv();
               }else{

               }
          }else{
               //echo "Error en la conexión: <br />";
          }

     }

     protected function ov_create_ftp_connection(){
          //connection to the ftp
          return ftp_connect($this->ov_ftpcon['ftpserver'], $this->ov_ftpcon['ftpport'], $this->ov_ftpcon['ftptime']);
     }

     protected function ov_ftp_login(){
          return ftp_login($this->_connection, $this->ov_ftpcon['ftpuser'], $this->ov_ftpcon['ftppass']);
     }

     protected function ov_ftp_pasv(){
          return ftp_pasv($this->_connection, true);
     }

     protected function ov_ftp_activ(){
          return ftp_pasv($this->_connection, false);
     }

     function ov_ftp_list($folder){
          return ftp_nlist($this->_connection, $folder);
     }

     function ov_ftp_levelup(){
          return ftp_cdup($this->_connection);
     }

     function ov_ftp_loc(){
          return ftp_pwd($this->_connection);
     }

     function ov_ftp_changedir($folder){
          return @ftp_chdir($this->_connection, $folder);
     }

     function ov_ftp_createdir($folder){
          return ftp_mkdir($this->_connection, $folder);
     }

     function ov_ftp_changeperm($mode, $filename){
          return ftp_chmod($this->_connection, $mode, $filename);
     }

     function ov_ftp_delete($path){
          return ftp_delete($this->_connection, $path);
     }

     function ov_ftp_upload($remote_folder, $remote_file, $local_file, $binary){
          if ($this->ov_ftp_changedir($remote_folder)){
               if ($binary) {
                    return ftp_put($this->_connection, $remote_file, $local_file,  FTP_BINARY);
               }else{
                    return ftp_put($this->_connection, $remote_file, $local_file,  FTP_ASCII);
               }
          }else{
               return true;
          }
     }

     function ov_ftp_close(){
          return ftp_close($this->_connection);
     }

     /*
     ftp_​alloc
     ftp_​cdup
     ftp_​chdir
     ftp_​chmod
     ftp_​close
     ftp_​connect
     ftp_​delete
     ftp_​exec
     ftp_​fget
     ftp_​fput
     ftp_​get_​option
     ftp_​get
     ftp_​login
     ftp_​mdtm
     ftp_​mkdir
     ftp_​nb_​continue
     ftp_​nb_​fget
     ftp_​nb_​fput
     ftp_​nb_​get
     ftp_​nb_​put
     ftp_​nlist
     ftp_​pasv
     ftp_​put
     ftp_​pwd
     ftp_​quit
     ftp_​raw
     ftp_​rawlist
     ftp_​rename
     ftp_​rmdir
     ftp_​set_​option
     ftp_​site
     ftp_​size
     ftp_​ssl_​connect
     ftp_​systype

      */

     // function ftp_mksubdirs($ftpbasedir,$ftpath){
     //      @ftp_chdir($this->_connection, $ftpbasedir); // /var/www/uploads
     //      $parts = explode('/',$ftpath); // 2013/06/11/username
     //      foreach($parts as $part){
     //           if(!@ftp_chdir($this->_connection, $part)){
     //                ftp_mkdir($this->_connection, $part);
     //                ftp_chdir($this->_connection, $part);
     //                //ftp_chmod($this->_connection, 0777, $part);
     //           }
     //      }
     // }



}
?>
