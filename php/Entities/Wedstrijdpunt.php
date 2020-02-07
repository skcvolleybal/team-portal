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
    public array $posities = ["rechtsachter", "rechtsvoor", "midvoor", "linksvoor", "linksachter", "midachter"];

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

    public function GetSpelsysteem(array $spelverdelerIds)
    {
        $aantalSpelverdelers = 0;
        foreach ($this->posities as $positie) {
            if (in_array($this->{$positie}, $spelverdelerIds)) {
                $aantalSpelverdelers++;
            }
        }

        switch ($aantalSpelverdelers) {
            case 1:
                return Spelsysteem::VIJF_EEN;
            case 2:
                return Spelsysteem::VIER_TWEE;
            default:
                return null;
        }
    }

    public function GetRotatie(array $spelverdelerIds)
    {
        foreach ($this->posities as $i => $positie) {
            if (in_array($this->{$positie}, $spelverdelerIds)) {
                return $i;
            }
        }

        return null;
    }

    public function GetRugnummers()
    {
        $result = [];
        foreach ($this->posities as $positie) {
            if ($this->{$positie}) {
                $result[] = $this->{$positie};
            }
        }
        return $result;
    }
}
