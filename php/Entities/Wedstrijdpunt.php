<?php

class Wedstrijdpunt
{
    public int $id;
    public string $matchId;
    public Team $skcTeam;
    public int $set;
    public bool $isSkcService;
    public bool $isSkcPunt;
    public int $puntenSkcTeam;
    public int  $puntenOtherTeam;
    public ?int $rechtsAchter;
    public ?int $rechtsVoor;
    public ?int $midVoor;
    public ?int $linksVoor;
    public ?int $linksAchter;
    public ?int $midAchter;

    public function __construct(
        int $id,
        string $matchId,
        Team $skcTeam,
        int $set,
        bool $isSkcService,
        bool $isSkcPunt,
        int $puntenSkcTeam,
        int  $puntenOtherTeam,
        ?int $rechtsAchter,
        ?int $rechtsVoor,
        ?int $midVoor,
        ?int $linksVoor,
        ?int $linksAchter,
        ?int $midAchter
    ) {
        $this->id = $id;
        $this->matchId = $matchId;
        $this->skcTeam = $skcTeam;
        $this->set = $set;
        $this->isSkcService = $isSkcService;
        $this->isSkcPunt = $isSkcPunt;
        $this->puntenSkcTeam = $puntenSkcTeam;
        $this->puntenOtherTeam =  $puntenOtherTeam;
        $this->rechtsAchter = $rechtsAchter;
        $this->rechtsVoor = $rechtsVoor;
        $this->midVoor = $midVoor;
        $this->linksVoor = $linksVoor;
        $this->linksAchter = $linksAchter;
        $this->midAchter = $midAchter;
    }
}
