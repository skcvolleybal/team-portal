<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Persoon;

class PersoonModel
{
    public function __construct(Persoon $persoon)
    {
        $this->id = $persoon->id;
        $this->naam = $persoon->naam;
    }
}
