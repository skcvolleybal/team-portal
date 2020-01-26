<?php

namespace TeamPortal\Entities;

class Uitslag
{
    public Team $team1;
    public Team $team2;
    public string $uitslag;
    public string $setstanden;

    public function __construct(Team $team1, Team $team2, string $uitslag, string $setstanden)
    {
        $this->team1 = $team1;
        $this->team2 = $team2;
        $this->uitslag = $uitslag;
        $this->setstanden = $setstanden;
    }
}
