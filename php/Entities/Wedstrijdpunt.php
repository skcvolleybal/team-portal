<?php

namespace TeamPortal\Entities;

class Wedstrijdpunt
{
    public ?int $id;
    public string $matchId;
    public Team $skcTeam;
    public int $set;
    public bool $isSkcService;
    public bool $isSkcPunt;
    public int $puntenSkcTeam;
    public int  $puntenOtherTeam;
    public ?int $rechtsachter;
    public ?int $rechtsvoor;
    public ?int $midvoor;
    public ?int $linksvoor;
    public ?int $linksachter;
    public ?int $midachter;

    public function __construct(
        ?int $id,
        string $matchId,
        Team $skcTeam,
        int $set,
        bool $isSkcService,
        bool $isSkcPunt,
        int $puntenSkcTeam,
        int  $puntenOtherTeam,
        ?int $rechtsachter,
        ?int $rechtsvoor,
        ?int $midvoor,
        ?int $linksvoor,
        ?int $linksachter,
        ?int $midachter
    ) {
        $this->id = $id;
        $this->matchId = $matchId;
        $this->skcTeam = $skcTeam;
        $this->set = $set;
        $this->isSkcService = $isSkcService;
        $this->isSkcPunt = $isSkcPunt;
        $this->puntenSkcTeam = $puntenSkcTeam;
        $this->puntenOtherTeam =  $puntenOtherTeam;
        $this->rechtsachter = $rechtsachter;
        $this->rechtsvoor = $rechtsvoor;
        $this->midvoor = $midvoor;
        $this->linksvoor = $linksvoor;
        $this->linksachter = $linksachter;
        $this->midachter = $midachter;
    }
}
