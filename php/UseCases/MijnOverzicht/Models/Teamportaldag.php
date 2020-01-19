<?php

class Teamportaldag
{
    public ZaalwachtModel $zaalwacht;
    public array $bardiensten = [];
    public string $datum;
    public string $date;
    public array $speeltijden = [];

    public function __construct(Wedstrijddag $dag, ?Team $team, ?Team $coachteam, Persoon $persoon)
    {
        $this->datum = DateFunctions::GetDutchDate($dag->date);
        $this->date = DateFunctions::GetYmdNotation($dag->date);

        if ($dag->zaalwacht) {
            $this->zaalwacht = new ZaalwachtModel($dag->zaalwacht);
        }

        foreach ($dag->speeltijden as $speeltijd) {
            $this->speeltijden[] = new SpeeltijdModel($speeltijd, $team, $coachteam, $persoon);
        }
    }
}
