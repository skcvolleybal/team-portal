<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\JoomlaGateway;

class Inloggen implements Interactor
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        if (empty($data->username) || empty($data->password)) {
            throw new InvalidArgumentException("Vul alle gegevens in");
        }

        if (!$this->joomlaGateway->Login($data->username, $data->password)) {
            throw new \UnexpectedValueException("Gebruikersnaam/wachtwoord combinatie klopt niet");
        }
    }
}