<?php

class Persoon
{
    public int $id;
    public string $naam;
    public ?string $email;
    public ?Team $team;

    function __construct(int $id, string $naam, $email)
    {
        $this->id = $id;
        $this->naam = $naam;
        $this->email = $email;
    }

    function Equals(?Persoon $user)
    {
        if ($user === null) {
            return false;
        }
        return $this->id === $user->id;
    }
}
