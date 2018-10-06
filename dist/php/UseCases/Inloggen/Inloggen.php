<?php

include 'IInteractorWithData.php';
include 'UserGateway.php';

class Inloggen implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
    }

    private $userGateway;

    public function Execute($data)
    {
        $username = $data->username ?? null;
        $password = $data->password ?? null;

        if (empty($username) || empty($password)) {
            InternalServerError("Vul alle gegevens in");
        }

        if ($this->userGateway->Login($username, $password)) {
            exit();
        } else {
            InternalServerError("Gebruikersnaam/wachtwoord combinatie klopt niet");
        }
    }
}
