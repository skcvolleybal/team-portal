<?php

namespace TeamPortal\Common;

use TeamPortal\Configuration;

class Database
{
    private $dbc;

    public function __construct(Configuration $config)
    {
        $this->host = $config->Database->Hostname;
        $this->database = $config->Database->Name;
        $this->username = $config->Database->Username;
        $this->password = $config->Database->Password;
        $this->options = $config->Database->Options;
    }

    private function getDbConnection()
    {
        if ($this->dbc) {
            return $this->dbc;
        }

        $this->dbc = new \PDO("mysql:host=$this->host;dbname=$this->database;charset=UTF8", $this->username, $this->password);
        return $this->dbc;
    }

    public function Execute(string $query, array $params = [])
    {
        if (empty($query)) {
            throw new \UnexpectedValueException('Query is empty');
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

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}
