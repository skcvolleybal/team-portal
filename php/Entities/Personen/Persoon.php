<?php

class Persoon
{
    public int $id;
    public string $naam;
    public ?string $email;
    public ?Team $team;

    function __construct(int $id, string $naam, string $email)
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

    function GetAfkorting()
    {
        $namen = explode(" ", $this->naam);
        $eersteLetters = array_map(function ($item) {
            return $item[0];
        }, $namen);
        return  implode("", $eersteLetters);
    }
}
