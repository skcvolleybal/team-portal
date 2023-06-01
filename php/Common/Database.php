<?php

namespace TeamPortal\Common;

use mysqli_sql_exception;
use PDO;
use UnexpectedValueException;

class Database
{
    private $dbc;
    public $host;
    public $database;
    public $username;
    public $password;
    public $options;

    public function __construct()
    {
        $this->host = $_ENV['DBHOSTNAME'];
        $this->database = $_ENV['DBNAME'];
        $this->username = $_ENV['DBUSERNAME'];
        $this->password = $_ENV['DBPASSWORD'];
        $this->options = [
            "PDO::MYSQL_ATTR_INIT_COMMAND" => "SET NAMES utf8"
        ];
    }

    private function getDbConnection()
    {
        if ($this->dbc) {
            return $this->dbc;
        }
        
        $this->dbc = new PDO(
            "mysql:host=$this->host;dbname=$this->database",
            $this->username,
            $this->password,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
        return $this->dbc;
    }

    public function Execute(string $query, array $params = [])
    {
        if (empty($query)) {
            throw new UnexpectedValueException('Query is empty');
        }

        $stmt = $this->getDbConnection()->prepare($query);

        if (!$stmt->execute($params)) {
            $message = 'Fout bij het uitvoeren van query ( query:\\n' .
                print_r($query, true) .
                '\\n\\nparams:\n' .
                print_r($params, true) .
                ') ' .
                print_r($stmt->errorInfo(), true) .
                ' om ' .
                date('H:i:s:(u) d-m-Y');

            throw new mysqli_sql_exception($message);
        }

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
