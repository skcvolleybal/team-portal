<?php

namespace TeamPortal\Entities;

class Barlid extends Persoon
{
    public int $aantalDiensten;
    public ?bool $isBhv;

    public function __construct(Persoon $persoon, int $aantalDiensten = 0)
    {
        $this->aantalDiensten = $aantalDiensten;
        parent::__construct($persoon->id, $persoon->naam, $persoon->email);
    }

    public static function Compare(Barlid $barlid1, Barlid $barlid2)
    {
        return $barlid1->aantalDiensten > $barlid2->aantalDiensten;
    }
}
