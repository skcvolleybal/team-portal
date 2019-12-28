<?php


class Inloggen implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $username = $data->username ?? null;
        $password = $data->password ?? null;

        if (empty($username) || empty($password)) {
            throw new InvalidArgumentException("Vul alle gegevens in");
        }

        if ($this->joomlaGateway->Login($username, $password)) {
            exit();
        } else {
            throw new UnexpectedValueException("Gebruikersnaam/wachtwoord combinatie klopt niet");
        }
    }
}
