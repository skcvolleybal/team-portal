<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\Utilities;
use TeamPortal\Entities\Wedstrijdpunt;

class PuntenModel
{
    public string $type;
    public int $gewonnenPunten = 0;
    public int $verlorenPunten = 0;
    public int $totaalPunten = 0;
    public int $percentage;
    public float $plusminus;
    public float $plusminusGenormaliseerd;
    public int $userId;
    public string $naam;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function AddPunt(Wedstrijdpunt $punt)
    {
        $this->totaalPunten++;
        if ($punt->isSkcPunt) {
            $this->gewonnenPunten++;
        } else {
            $this->verlorenPunten++;
        }
    }

    public function CalculatePercentages()
    {
        $this->percentage = $this->totaalPunten > 0 ? ($this->gewonnenPunten / $this->totaalPunten) * 100 : 0;
    }

    public function CalculatePlusminus()
    {
        $this->plusminus = $this->totaalPunten > 0 ? (($this->gewonnenPunten - $this->verlorenPunten) / $this->totaalPunten) * 50 : 0;
        $this->plusminus = Utilities::Round($this->plusminus);
    }

    public static function Normalize(array $spelers)
    {
        $sum = 0;
        $numberOfPlayers = 0;
        foreach ($spelers as $speler) {
            if ($speler->plusminus > 0) {
                $sum += $speler->plusminus;
                $numberOfPlayers++;
            }
        }

        $average = $numberOfPlayers > 0 ? $sum / $numberOfPlayers : 0;

        foreach ($spelers as $speler) {
            if ($speler->plusminus > 0) {
                $speler->plusminusGenormaliseerd = $speler->plusminus - $average;
                $speler->plusminusGenormaliseerd = Utilities::Round($speler->plusminusGenormaliseerd);
            }
        }
    }

    public static function Compare(PuntenModel $p1, PuntenModel $p2)
    {
        return $p1->percentage < $p2->percentage;
    }
}
