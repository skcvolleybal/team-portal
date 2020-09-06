<?php

namespace TeamPortal\Entities;

class Persoon
{
    public int $id;
    public string $naam;
    public ?string $email;
    public ?Team $team;
    public ?Team $coachteam = null;
    public ?string $positie = null;
    public ?int $rugnummer = null;
    public int $aantalKeerGeteld = 0;

    function __construct(int $id, string $naam, string $email)
    {
        $this->id = $id;
        $this->email = $email;

        $this->naam = $naam;
        $this->voornaam = $this->GetEersteNaam();
        $this->afkorting = $this->GetAfkorting();
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
            return strlen($item) > 0 ? $item[0] : "";
        }, $namen);
        return implode("", $eersteLetters);
    }

    function GetEersteNaam()
    {
        return substr($this->naam, 0, strpos($this->naam, ' '));
    }

    function IsSpelverdeler()
    {
        return $this->positie === "Spelverdeler";
    }
}
