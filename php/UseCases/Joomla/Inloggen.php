<?php

class InloggenInteractor
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $request)
    {
        if (empty($request->username) || empty($request->password)) {
            throw new InvalidArgumentException("Vul alle gegevens in");
        }

        if (!$this->joomlaGateway->Login($request->username, $request->password)) {
            throw new UnexpectedValueException("Gebruikersnaam/wachtwoord combinatie klopt niet");
        }
    }
}