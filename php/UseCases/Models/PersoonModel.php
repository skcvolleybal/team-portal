<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities;

class PersoonModel
{
    public function __construct(Entities\Persoon $persoon)
    {
        $this->id = $persoon->id;
        $this->naam = $persoon->naam;
    }
}
