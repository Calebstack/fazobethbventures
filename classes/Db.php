<?php

require_once __DIR__ . "/Config.php";

class Db{
    private $dbhost = DB_HOST;
    private $dbuser = DB_USER;
    private $dbpass = DB_PASS;
    private $dbname = DB_NAME;

    protected function connect(){
        $dsn = "mysql:host=$this->dbhost;dbname=$this->dbname;charset=utf8mb4";
        $option = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        try{
            $pdo = new PDO($dsn, $this->dbuser, $this->dbpass, $option);
            return $pdo;
        }catch(PDOException $e){
            error_log("Database connection error: " . $e->getMessage());
            return false;
        }
    }
}

?>