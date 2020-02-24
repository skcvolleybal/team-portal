<?php

namespace TeamPortal\Entities;

class DwfSet
{
    public DwfOpstelling $thuisopstelling;
    public DwfOpstelling $uitopstelling;
    public array $thuiswissels = [];
    public array $uitwissels = [];
    public array $punten = [];

    public function WisselTeams()
    {
        $tmp = $this->thuisopstelling;
        $this->thuisopstelling = $this->uitopstelling;
        $this->uitopstelling = $tmp;

        $tmp = $this->thuiswissels;
        $this->thuiswissels = $this->uitwissels;
        $this->uitwissels = $tmp;

        foreach ($this->punten as $punt) {
            $punt->WisselTeams();
        }
    }
}
