<?php

namespace TeamPortal\Entities;

class Stand
{
    public ?int $nummer;
    public Team $team;
    public int $aantalWedstrijden;
    public int $punten;
    public int $setsVoor;
    public int $setsTegen;
    public int $puntenVoor;
    public int $puntenTegen;

    public function __construct(
        ?int $nummer,
        Team $team,
        int $aantalWedstrijden,
        int $punten,
        int $setsVoor,
        int $setsTegen,
        int $puntenVoor,
        int $puntenTegen
    ) {
        $this->nummer = $nummer;
        $this->team = $team;
        $this->aantalWedstrijden = $aantalWedstrijden;
        $this->punten = $punten;
        $this->setsVoor = $setsVoor;
        $this->setsTegen = $setsTegen;
        $this->puntenVoor = $puntenVoor;
        $this->puntenTegen = $puntenTegen;
    }
}
