<?php

include 'IInteractorWithData.php';
include 'UserGateway.php';
include 'NevoboGateway.php';

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
            header("HTTP/1.1 500 Internal Server Error");
            exit("Vul alle gegevens in");
        }

        if ($this->userGateway->Login($username, $password)) {
            exit();
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            exit("Gebruikersnaam/wachtwoord combinatie klopt niet");
        }
    }
}
