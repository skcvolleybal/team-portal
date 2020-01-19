<?php

class DwfWedstrijd
{
    public string $matchId;
    public Team $skcTeam;
    public Team $otherTeam;
    public int $setsSkcTeam;
    public int $setsOtherTeam;

    public function __construct(string $matchId, Team $skcTeam, Team $otherTeam, int $setsSkcTeam, int $setsOtherTeam)
    {
        $this->matchId = $matchId;
        $this->skcTeam = $skcTeam;
        $this->otherTeam = $otherTeam;
        $this->setsSkcTeam = $setsSkcTeam;
        $this->setsOtherTeam = $setsOtherTeam;
    }
}
