<?php

namespace TeamPortal\Entities;

class DwfSpelophoud extends DwfActie
{
    public function __construct(int $puntenThuisTeam, int $puntenUitTeam, string $toelichting)
    {
        $this->puntenThuisTeam = $puntenThuisTeam;
        $this->puntenUitTeam = $puntenUitTeam;
        $this->toelichting = $toelichting;
    }
}
