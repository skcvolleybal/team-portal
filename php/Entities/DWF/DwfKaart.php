<?php

namespace TeamPortal\Entities;

class DwfKaart extends DwfActie
{
    public function __construct(int $puntenThuisTeam, int $puntenUitTeam, string $team, string $toelichting)
    {
        $this->puntenThuisTeam = $puntenThuisTeam;
        $this->puntenUitTeam = $puntenUitTeam;
        $this->team = $team;
        $this->toelichting = $toelichting;
    }
}
