<?php

namespace TeamPortal\Entities;

class DwfWissel extends DwfActie
{
    public function __construct(int $veldspeler, int $bankspeler, string $team)
    {
        $this->veldspeler = $veldspeler;
        $this->bankspeler = $bankspeler;
        $this->team = $team;
    }

    public function WisselTeams()
    {
        $this->team = ThuisUit::WisselTeam($this->team);
    }
}
