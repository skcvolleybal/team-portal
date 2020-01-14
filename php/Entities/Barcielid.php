<?php

class Barcielid extends Persoon
{
    public int $aantalDiensten;

    public function __construct(int $userId, string $naam, int $aantalDiensten)
    {
        parent::__construct($userId, $naam);
        $this->aantalDiensten = $aantalDiensten;
    }
}