<?php

class Database
{
    private $dbc;

    public function __construct(stdClass $config)
    {
        $this->host = $config->database->host;
        $this->database = $config->database->database;
        $this->username = $config->database->username;
        $this->password = $config->database->password;
        $this->options = $config->database->options;
    }

    private function getDbConnection()
    {
        if ($this->dbc) {
            return $this->dbc;
        }

        $this->dbc = new PDO("mysql:host=$this->host;dbname=$this->database", $this->username, $this->password, $this->options);
        return $this->dbc;
    }

    public function Execute($query, $params = array())
    {
        if (empty($query)) {
            throw new UnexpectedValueException('Query is empty');
        }

        $stmt = $this->getDbConnection()->prepare($query);
        foreach ($params as $param) {
            $stmt->bindValue($param->getName(), $param->getValue(), $param->getType());
        }

        if (!$stmt->execute()) {
            $message = 'Fout bij het uitvoeren van query ( query:\n' .
                print_r($query, true) .
                '\n\nparams:\n' .
                print_r($params, true) .
                ') ' .
                print_r($stmt->errorInfo(), true) .
                ' om ' .
                date('H:i:s:(u) d-m-Y');

            throw new mysqli_sql_exception($message);
        }

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function Execute2($query, $params = null)
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
