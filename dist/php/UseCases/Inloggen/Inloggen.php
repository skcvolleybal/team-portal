<?php

include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';

class Inloggen implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    private $joomlaGateway;

    public function Execute($data)
    {
        $username = $data->username ?? null;
        $password = $data->password ?? null;

        if (empty($username) || empty($password)) {
            InternalServerError("Vul alle gegevens in");
        }

        if ($this->joomlaGateway->Login($username, $password)) {
            exit();
        } else {
            InternalServerError("Gebruikersnaam/wachtwoord combinatie klopt niet");
        }
    }
}
