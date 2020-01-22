<?php

class WedstrijddagModel
{
    public string $date;
    public string $datum;
    public array $speeltijden = [];
    public array $bardiensten = [];
    public array $eigenWedstrijden = [];
    public string $zaalwacht;
    public string $zaalwachtShortNotation;

    public function __construct(Wedstrijddag $dag)
    {
        $this->date = DateFunctions::GetYmdNotation($dag->date);
        $this->datum = DateFunctions::GetDutchDate($dag->date);
        if ($dag->zaalwacht !== null) {
            $this->zaalwacht = $dag->zaalwacht->team->naam;
            $this->zaalwachtShortNotation = $dag->zaalwacht->team->GetShortNotation();
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
