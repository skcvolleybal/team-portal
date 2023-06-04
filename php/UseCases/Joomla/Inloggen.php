<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Gateways\WordPressGateway;
use UnexpectedValueException;

class Inloggen implements Interactor
{
    public function __construct(WordPressGateway $wordPressGateway)
    {
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null)
    {
        if (empty($data->username) || empty($data->password)) {
            throw new InvalidArgumentException("Vul alle gegevens in");
        }

        if (!$this->wordPressGateway->Login($data->username, $data->password)) {
            throw new UnexpectedValueException("Gebruikersnaam/wachtwoord combinatie klopt niet");
        }
    }
}
