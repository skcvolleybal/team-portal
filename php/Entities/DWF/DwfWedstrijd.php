<?php

class DwfWedstrijd
{
    public string $matchId;
    public Team $team1;
    public Team $team2;
    public int $setsTeam1;
    public int $setsTeam2;
    public array $sets = [];

    public function __construct(string $matchId, Team $team1, Team $team2, int $setsTeam1, int $setsTeam2)
    {
        $this->matchId = $matchId;
        $this->team1 = $team1;
        $this->team2 = $team2;
        $this->setsTeam1 = $setsTeam1;
        $this->setsTeam2 = $setsTeam2;
    }
}
