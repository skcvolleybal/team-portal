<?php

include_once 'Param.php';
include_once 'JoomlaGateway.php';

class Database
{
    private $dbc;

    public function __construct()
    {
        $this->joomlaGateway = new JoomlaGateway($this);
    }

    private function getDbConnection()
    {
        if ($this->dbc) {
            return $this->dbc;
        }

        $this->joomlaGateway->InitJoomla();

        $config = JFactory::getConfig();
        $host = $config->get('host');
        $db = $config->get('db');
        $user = $config->get('user');
        $password = $config->get('password');
        $this->dbc = new PDO("mysql:host=$host;dbname=$db", $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        return $this->dbc;
    }

    public function Execute($query, $params = array())
    {
        if (empty($query)) {
            $this->returnError("Query is empty");
        }

        $stmt = $this->getDbConnection()->prepare($query);
        foreach ($params as $param) {
            $stmt->bindValue($param->getName(), $param->getValue(), $param->getType());
        }

        if (!$stmt->execute()) {
            $message = "Fout bij het uitvoeren van query ( query:\n" .
            print_r($query, true) .
            "\n\nparams:\n" .
            print_r($params, true) .
            ") " .
            print_r($stmt->errorInfo(), true) .
            " om " .
            date('H:i:s:(u) d-m-Y');

            InternalServerError($message);
        }

        return $stmt->fetchAll();
    }
}
