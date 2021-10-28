<?php

namespace TeamPortal\Entities;

use DateTime;
use TeamPortal\Common\DateFunctions;

class Wedstrijd
{
    public ?int $id;
    public string $matchId;
    public Team $team1;
    public Team $team2;
    public string $poule;
    public ?DateTime $timestamp;
    public bool $isVeranderd;
    public ?string $locatie = null;
    public array $tellers = [null, null];
    public ?Persoon $scheidsrechter = null;
    public ?string $uitslag = null;
    public ?string $setstanden = null;

    public function __construct(string $matchId, $id = null)
    {
        $this->id = $id;
        $this->matchId = $matchId;
    }

    static function Compare(Wedstrijd $wedstrijd1, Wedstrijd $wedstrijd2): bool
    {
        if (!$wedstrijd1->timestamp) {
            return -1;
        }
        if (!$wedstrijd2->timestamp) {
            return 1;
        }
        return $wedstrijd1->timestamp > $wedstrijd2->timestamp;
    }

    static function CreateFromNevoboWedstrijd(
        string $matchId,
        Team $team1,
        Team $team2,
        string $poule,
        ?DateTime $timestamp,
        string $locatie
    ): Wedstrijd {
        $newWedstrijd = new Wedstrijd($matchId);
        $newWedstrijd->team1 = $team1;
        $newWedstrijd->team2 = $team2;
        $newWedstrijd->poule = $poule;
        $newWedstrijd->timestamp = $timestamp;
        $newWedstrijd->locatie = $locatie;
        return $newWedstrijd;
    }

    function GetShortLocatie(): string
    {
        if (!$this->locatie) {
            return null;
        }

        $firstPart = substr($this->locatie, 0, strpos($this->locatie, ','));
        $lastPart = substr($this->locatie, strripos($this->locatie, ' ') + 1);
        return $firstPart . ', ' . $lastPart;
    }

    function IsMogelijk(?Wedstrijd $wedstrijd): bool
    {
        if (!$wedstrijd) {
            return true;
        }

        $difference = $this->timestamp->diff($wedstrijd->timestamp, true);
        if ($difference->days > 0) {
            return true;
        }

        $isMogelijk = null;
        $hourDifference = $difference->h + ($difference->i / 60);
        if ($this->IsThuis($this->locatie) && $this->IsThuis($wedstrijd->locatie)) {
            $isMogelijk = $hourDifference >= 2;
        } else {
            $isMogelijk = $hourDifference >= 4;
        }

        return $isMogelijk;
    }

    function IsThuis(): bool
    {
        return strpos($this->locatie, 'Universitair SC') !== false;
    }

    function IsEigenWedstrijd(Persoon $user): bool
    {
        return
            $user !== null &&
            $user->team !== null &&
            ($this->team1->naam === $user->team->naam || $this->team2->naam === $user->team->naam);
    }

    public function GetTeams(): string
    {
        return $this->team1->naam . " - " . $this->team2->naam;
    }

    static public function GetWedstrijdWithDate(array $programma, DateTime $date): ?Wedstrijd
    {
        foreach ($programma as $wedstrijd) {
            if ($wedstrijd->timestamp && DateFunctions::GetYmdNotation($wedstrijd->timestamp) === DateFunctions::GetYmdNotation($date)) {
                return $wedstrijd;
            }
        }
        return null;
    }

    public function AppendInformation(?Wedstrijd $wedstrijd): void
    {
        if ($wedstrijd === null) {
            return;
        }

        $this->id = $this->id ?? $wedstrijd->id;
        $this->timestamp = $this->timestamp ?? $wedstrijd->timestamp;
        $this->locatie = $this->locatie ?? $wedstrijd->locatie;
        $this->poule = $this->poule ?? $wedstrijd->poule;
        $this->team1 = $this->team1 ?? $wedstrijd->team1;
        $this->team2 = $this->team2 ?? $wedstrijd->team2;
        $this->tellers = $this->tellers ?? $wedstrijd->tellers;
        $this->scheidsrechter = $this->scheidsrechter ?? $wedstrijd->scheidsrechter;
    }

    public static function GetWedstrijdWithMatchId(array $wedstrijden, string $matchId): ?Wedstrijd
    {
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd->matchId === $matchId) {
                return $wedstrijd;
            }
        }
        return null;
    }
}
