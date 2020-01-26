<?php

namespace TeamPortal\Entities;

class Wedstrijduitslag
{
    public string $team1;
    public string $team2;
    public string $uitslag;
    public string $setstanden;

    public function __construct(string $team1, string $team2, string $uitslag, string $setstanden)
    {
        $this->team1 = $team1;
        $this->team2 = $team2;
        $this->uitslag = $uitslag;
        $this->setstanden = $setstanden;
    }
}
