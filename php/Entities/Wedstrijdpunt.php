<?php

namespace TeamPortal\Entities;

class Wedstrijdpunt
{
    public int $id;
    public string $matchId;
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
        int $id,
        string $matchId,
        int $set,
        bool $isSkcService,
        bool $isSkcPunt,
        int $puntenSkcTeam,
        int  $puntenOtherTeam
    ) {
        $this->id = $id;
        $this->matchId = $matchId;
        $this->set = $set;
        $this->isSkcService = $isSkcService;
        $this->isSkcPunt = $isSkcPunt;
        $this->puntenSkcTeam = $puntenSkcTeam;
        $this->puntenOtherTeam =  $puntenOtherTeam;
    }

    public function GetSpelsysteem(array $spelverdelerIds)
    {
        $aantalSpelverdelers = 0;
        $opstelling = $this->GetOpstelling();
        $aantalSpelverdelers = count(array_intersect($spelverdelerIds, $opstelling));

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
        $opstelling = $this->GetOpstelling();
        foreach ($opstelling as $i => $positie) {
            if (in_array($positie, $spelverdelerIds)) {
                return $i;
            }
        }

        return null;
    }

    public function GetSpelerIds()
    {
        $opstelling = $this->GetOpstelling();
        return array_values(array_filter($opstelling, function ($positie) {
            return $positie != null;
        }));
    }

    public function SetOpstelling(?int $rechtsachter, ?int $rechtsvoor, ?int $midvoor, ?int $linksvoor, ?int $linksachter, ?int $midachter)
    {
        $this->rechtsachter = $rechtsachter;
        $this->rechtsvoor = $rechtsvoor;
        $this->midvoor = $midvoor;
        $this->linksvoor = $linksvoor;
        $this->linksachter = $linksachter;
        $this->midachter = $midachter;
    }

    private function GetOpstelling()
    {
        return [$this->rechtsachter, $this->rechtsvoor, $this->midvoor, $this->linksvoor, $this->linksachter, $this->midachter];
    }
}
