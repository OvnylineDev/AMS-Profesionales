<?php
/**
 *
 */
class OV_SQLConnect{

     protected $ov_dbcon = array("sqlserver" => "AMSServer", "sqluser" => "Qo2iPC4Dt8", "sqlpass" => "p_5NM?|YFpN");
     protected $ov_database = "GestionExpedientes";
     protected $_connection;
     protected $_dbselect;
     protected $_result;

     function __construct(){
          $this->$_connection = mssql_connect("AMSServer", "Qo2iPC4Dt8", "p_5NM?|YFpN");
          echo "Pasa conexión";

          if ($this->$_connection) {
               echo "Conexión realizada<br />";
          }else{
               echo "Error en la conexión: ".mssql_get_last_message()."<br />";
          }

     }

     // protected function ov_create_db_connection(){
     //      //connection to the database
     //      // return mssql_connect($this->$ov_dbcon['sqlserver'], $this->$ov_dbcon['sqluser'], $this->$ov_dbcon['sqlpass']);
     //      echo "Entra conexión";
     //      // $conn =
     //      return $conn;
     // }

     public function ov_select_database(){
          //select a database to work with
          $this->$_dbselect = mssql_select_db($this->$ov_database, $this->$ov_dbcon);
     }

     public function query($sql){
          $this->_result = mssql_query($sql);
          return $this->_result;
     }

     public function queryarray($sql){
          $this->_result = mssql_query($sql);

          if(is_bool($this->_result)){
               return $this->_result;
          }else{
               return mssql_fetch_array($this->_result);
          }
     }

     public function num_rows($result){
          return mssql_num_rows($result);
     }

     public function free(){
          mssql_free_result($this->_result);
     }

     public function disconnect(){
          mssql_close($this->_connection);
     }

     public function free_statement($statement)
     {
          mssql_free_statement($statement);
     }

}
?>
