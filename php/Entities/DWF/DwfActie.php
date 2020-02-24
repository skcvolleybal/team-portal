<?php

namespace TeamPortal\Entities;

class DwfActie
{
    public function WisselPunten()
    {
        $tmp = $this->puntenThuisTeam;
        $this->puntenThuisTeam = $this->puntenUitTeam;
        $this->puntenUitTeam = $tmp;
    }
}
