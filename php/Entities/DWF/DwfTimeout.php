<?php

class DwfTimeout extends DwfActie
{
    public function __construct(int $puntenThuisTeam, int $puntenUitTeam, string $team)
    {
        $this->puntenThuisTeam = $puntenThuisTeam;
        $this->puntenUitTeam = $puntenUitTeam;
        $this->team = $team;
    }
}
