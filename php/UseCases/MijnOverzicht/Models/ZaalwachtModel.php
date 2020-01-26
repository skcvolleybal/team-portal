<?php

namespace TeamPortal\UseCases;

class ZaalwachtModel extends Overzichtsitem
{
    public string $team;

    public function __construct(Zaalwacht $zaalwacht)
    {
        $this->team = $zaalwacht->team->GetSkcNaam();
        parent::__construct("zaalwacht", $zaalwacht->date);
    }
}
