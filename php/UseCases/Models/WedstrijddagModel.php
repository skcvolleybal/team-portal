<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Speeltijd;
use TeamPortal\Entities\Wedstrijddag;

class WedstrijddagModel
{
    public string $date;
    public string $datum;
    public array $speeltijden = [];
    public array $bardiensten = [];
    public array $eigenWedstrijden = [];
    public ?string $eersteZaalwacht = null;
    public ?string $eersteZaalwachtShortNotation = null;
    public ?string $tweedeZaalwacht = null;
    public ?string $tweedeZaalwachtShortNotation = null;

    public function __construct(Wedstrijddag $dag)
    {
        $this->date = DateFunctions::GetYmdNotation($dag->date);
        $this->datum = DateFunctions::GetDutchDate($dag->date);
        if ($dag->eersteZaalwacht !== null) {
            $this->eersteZaalwacht = $dag->eersteZaalwacht->naam;
            $this->eersteZaalwachtShortNotation = $dag->eersteZaalwacht->GetShortNotation();
        }
        if ($dag->tweedeZaalwacht !== null) {
            $this->tweedeZaalwacht = $dag->tweedeZaalwacht->naam;
            $this->tweedeZaalwachtShortNotation = $dag->tweedeZaalwacht->GetShortNotation();
        }

        foreach ($dag->bardiensten as $bardienst) {
            $this->bardiensten[] = new BardienstModel($bardienst);
        }

        foreach ($dag->speeltijden as $speeltijd) {
            $this->speeltijden[] = new SpeeltijdModel($speeltijd);
        }

        foreach ($dag->eigenWedstrijden as $wedstrijd) {
            $this->eigenWedstrijden[] = new WedstrijdModel($wedstrijd);
        }
    }

    public function AddSpeeltijd(Speeltijd $speeltijd)
    {
        $tijd = new SpeeltijdModel($speeltijd);
        $tijd->isBeschikbaar = $speeltijd->isBeschikbaar;
        $tijd->isMogelijk = $speeltijd->isMogelijk;
        $tijd->tijd = DateFunctions::GetTime($speeltijd->time);
        $this->speeltijden[] = $tijd;
    }
}
