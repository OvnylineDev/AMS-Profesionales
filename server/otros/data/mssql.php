<?php
class mssqldb{
    private $_connection;
    private $_dbselect;
    private $_result;

    public function  __construct()
    {
        require_once('dbms.inc.php');
    }

    public function connect()
    {
        //$this->_connection = @mssql_connect(MSSQL_HOST, MSSQL_USER, MSSQL_PASSWORD) or die('Could not connect to the server!');;
        
        $this->_connection = @sqlsrv_connect(MSSQL_HOST, array("Database"=>MSSQL_DATABASE, "UID"=>MSSQL_USER, "PWD"=>MSSQL_PASSWORD)) or die('Could not connect to the server!');

        if(!$this->_connection)
        {
            die('<font color="red">Error: Unable to connect to database host.</font>');
        }
        /*
        $this->_dbselect = @mssql_select_db(MSSQL_DATABASE, $this->_connection);
        if(!$this->_dbselect)
        {
            die('<font color="red">Error: Unable to select database.</font>');
        }*/
    }

    public function query($sql)

    {
        $this->_result = @sqlsrv_query($this->_connection, $sql);
        if(!$this->_result)
        {
            die('<font color="red">Error: Could not run query.</font>');
        }
        return $this->_result;
    }
    
    public function num_rows($query){
        return sqlsrv_num_rows($query);
    }

    public function free()
    {
        mssql_free_result($this->_result);
    }

    public function disconnect()
    {
        sqlsrv_close($this->_connection);
    }
}
?>