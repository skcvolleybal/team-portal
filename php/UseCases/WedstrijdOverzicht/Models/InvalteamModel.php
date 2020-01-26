<?php

namespace TeamPortal\UseCases;

class InvalteamModel
{
    public ?WedstrijdModel $wedstrijd = null;
    public bool $isMogelijk;
    public string $naam;

    public function __construct(Invalteam $team)
    {
        if ($team->eigenWedstrijd) {
            $this->wedstrijd = new WedstrijdModel($team->eigenWedstrijd);
        }

        $this->naam = $team->GetSkcNaam();
        $this->teamgenoten = $team->teamgenoten;
        $this->isMogelijk = $team->isMogelijk;
    }
}
