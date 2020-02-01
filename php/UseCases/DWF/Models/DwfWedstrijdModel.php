<?php

use TeamPortal\Entities\DwfWedstrijd;

class DwfWedstrijdModel
{
    public string $matchId;
    public string $team1;
    public string $team2;
    public int $setsTeam1;
    public int $setsTeam2;
    public array $sets = [];

    public function __construct(DwfWedstrijd $wedstrijd)
    {
        $this->matchId = $wedstrijd->matchId;
        $this->team1 = $wedstrijd->team1->naam;
        $this->team2 = $wedstrijd->team2->naam;
        $this->setsTeam1 = $wedstrijd->setsTeam1;
        $this->setsTeam2 = $wedstrijd->setsTeam2;
    }
}
