<?php

class Invalteam extends Team
{
    public ?Wedstrijd $eigenWedstrijd;
    public bool $isMogelijk;

    public function __construct(Team $team, ?Wedstrijd $eigenWedstrijd)
    {
        $this->eigenWedstrijd = $eigenWedstrijd;
        parent::__construct($team->naam, $team->id, $team->teamgenoten);
    }
}
