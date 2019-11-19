<?php

class Database {
 
    private $host = "localhost";
    private $dbname = "students_db";
    private $username = "root";
    private $password = "AXScUDegrG11u1Do";
    private $conn;
 
    public function getConnection(){
 
        $this->conn = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname, $this->username, $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->exec("set names utf8");
 
        return $this->conn;
    }
    
    public function closeConnection() {
        
        $this->conn = null;
    }
}

?>