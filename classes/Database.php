<?php


class Database {

    const DB_USER = "root";
    const DB_PASSWORD = "";
    const DB_NAME = "testing";
    const DB_SERVER = "127.0.0.1";

    private $server;
    private $user;
    private $password;
    private $name;

    public function __construct() {
        $this->server = self::DB_SERVER;
        $this->user = self::DB_USER;
        $this->password = self::DB_PASSWORD;
        $this->name = self::DB_NAME;
    }

    public function connect() {
        // returns db connection object (PDO Object)
        $connection = new PDO("mysql:host=".$this->getServer().
                      ";dbname=".$this->getName(), 
                      $this->getUser(), 
                      $this->getPassword(),
                      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return $connection;
    }

    public function select($from, $column, $condition=1) {
        return $this->connect()->
                      query("SELECT $column FROM $from WHERE $condition")->
                      fetch(PDO::FETCH_LAZY)[$column];
    }

    public function selectAll($from, $column, $condition=1) {
        $rows = $this->connect()->query("SELECT $column FROM $from WHERE $condition")->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[] = $row[$column];
        }
        return $result;
    }

    public function selectAllSeveralCols($from, $columns, $condition=1) {
        return $this->connect()->query("SELECT $columns FROM $from WHERE $condition")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, $values) {
        return $this->connect()->
                      exec("INSERT INTO $table VALUES ($values)");
    }

    public function update($table, $column, $value, $condition) {
        $stmt = $this->connect()->prepare("UPDATE $table SET `$column` = :value WHERE $condition");
        return $stmt->execute(['value' => $value]);
    }


    public function isExists($table, $condition) {
        return (bool) $this->connect()->
                             query("SELECT COUNT(*) AS num FROM $table WHERE $condition")->
                             fetch(PDO::FETCH_LAZY)['num'];   
    }


    private function getServer() {
        return $this->server;
    }

    private function getUser() {
        return $this->user;
    }

    private function getPassword() {
        return $this->password;
    }

    private function getName() {
        return $this->name;
    }

}
