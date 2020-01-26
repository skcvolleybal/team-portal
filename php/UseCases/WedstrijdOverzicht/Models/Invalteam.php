<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities;

class Invalteam extends Entities\Team
{
    public ?Entities\Wedstrijd $eigenWedstrijd;
    public bool $isMogelijk;

    public function __construct(Entities\Team $team, ?Entities\Wedstrijd $eigenWedstrijd)
    {
        $this->eigenWedstrijd = $eigenWedstrijd;
        parent::__construct($team->naam, $team->id, $team->teamgenoten);
    }
}
