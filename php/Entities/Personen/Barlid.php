<?php

class Barlid extends Persoon
{
    public int $aantalDiensten;
    public ?bool $isBhv;

    public function __construct(Persoon $persoon, int $aantalDiensten = 0)
    {
        $this->aantalDiensten = $aantalDiensten;
        parent::__construct($persoon->id, $persoon->naam, $persoon->email);
    }
}
