<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Barlid;

class BarlidModel
{
    public int $id;
    public string $naam;
    public bool $isBhv;

    public function __construct(Barlid $barlid)
    {
        $this->id = $barlid->id;
        $this->naam = $barlid->naam;
        $this->isBhv = $barlid->isBhv;
    }
}
