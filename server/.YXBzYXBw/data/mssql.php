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
          $this->_connection = $this->ov_create_db_connection();

          if ($this->_connection) {
               //echo "Conexión realizada<br />";
               $this->ov_select_database();
          }else{
               //echo "Error en la conexión: ".mssql_get_last_message()."<br />";
          }

     }

     protected function ov_create_db_connection(){
          //connection to the database
          return mssql_connect($this->ov_dbcon['sqlserver'], $this->ov_dbcon['sqluser'], $this->ov_dbcon['sqlpass']);
     }

     public function ov_select_database(){
          //select a database to work with
          $this->_dbselect = mssql_select_db($this->ov_database, $this->_connection);
     }

     public function query($sql){
          $this->_result = mssql_query($sql, $this->_connection);
          return $this->_result;
     }

     public function queryarray($sql){
          $this->_result = mssql_query($sql, $this->_connection);

          if(is_bool($this->_result)){
               return $this->_result;
          }else{
               return mssql_fetch_array($this->_result);
          }
     }

     public function fetcharray(){
          return mssql_fetch_array($this->_result);
     }

     public function num_rows($result){
          return mssql_num_rows($result);
     }

     public function free(){
          if (func_num_args()==1) {
               $arg_list = func_get_args();
               mssql_free_result($arg_list[0]);
          }else{
               mssql_free_result($this->_result);
          }
     }

     public function disconnect(){
          $this->free();
          mssql_close($this->_connection);
     }

     public function see_errors(){
          return mssql_get_last_message();
     }

     function mssql_begin_transaction() {
          mssql_query("BEGIN TRANSACTION");
     }

     function mssql_commit() {
          mssql_query("COMMIT");
     }

     function mssql_rollback() {
          mssql_query("ROLLBACK");
     }

     function mssql_insert_id() {
          $id = "";

          $rs = mssql_query("SELECT @@identity AS id");

          if ($row = mssql_fetch_row($rs)) {
               $id = trim($row[0]);
          }

          $this->free($rs);

          return $id;
     }

     //Controlar cuando se puede usar dicha función
     // private public function free_statement($statement)
     // {
     //      mssql_free_statement($statement);
     // }

}
?>
