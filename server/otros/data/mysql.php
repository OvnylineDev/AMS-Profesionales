<?php
class mysqldb{
    private $_connection;
    private $_dbselect;
    private $_result;

    public function  __construct()
    {
        require_once('dbmy.inc.php');
    }

    public function connect()
    {
        $this->_connection = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD);
        if(!$this->_connection)
        {
            die('<font color="red">Error: Unable to connect to database host.</font>');
        }
        $this->_dbselect = @mysql_select_db(MYSQL_DATABASE, $this->_connection);
        if(!$this->_dbselect)
        {
            die('<font color="red">Error: Unable to select database.</font>');
        }
    }

    public function query($sql)
    {
        $this->_result = @mysql_query($sql);
        if(!$this->_result)
        {
            die('<font color="red">Error: Could not run query. </font>'.mysql_error());
        }
        return $this->_result;
    }

    public function free()
    {
        mysql_free_result($this->_result);
    }

    public function disconnect()
    {
        mysql_close($this->_connection);
    }
}
?>