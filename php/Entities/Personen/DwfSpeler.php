<?php

namespace TeamPortal\Entities;

class DwfSpeler extends Persoon
{
    public int $aantalGespeeldePunten;

    public function __construct(Persoon $persoon, int $aantalGespeeldePunten = 0)
    {
        $this->aantalGespeeldePunten = $aantalGespeeldePunten;
        parent::__construct($persoon->id, $persoon->naam, $persoon->email);
    }
}
