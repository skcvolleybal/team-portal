<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Scheidsrechter;

class ScheidsrechterModel
{
    public int $id;
    public string $naam;
    public ?string $niveau;
    public int $gefloten;
    public string $team;
    public ?string $eigenTijd;
    public ?bool $isBeschikbaar;

    public function __construct(Scheidsrechter $scheidsrechter)
    {
        $this->id = $scheidsrechter->id;
        $this->naam = $scheidsrechter->naam;
        $this->niveau = $scheidsrechter->niveau;
        $this->gefloten = $scheidsrechter->aantalGeflotenWedstrijden;
        $this->team = $scheidsrechter->team != null ? $scheidsrechter->team->GetShortNotation() : "Geen Team";
    }
}
