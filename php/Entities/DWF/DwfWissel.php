<?php

namespace TeamPortal\Entities;

class DwfWissel extends DwfActie
{
    public function __construct(int $spelerIn, int $spelerUit, string $team)
    {
        $this->spelerIn = $spelerIn;
        $this->spelerUit = $spelerUit;
        $this->team = $team;
    }
}
