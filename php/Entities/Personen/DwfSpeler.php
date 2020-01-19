<?php

class DwfSpeler extends Persoon
{
    public int $aantalGespeeldePunten;

    public function __construct(Persoon $persoon, int $aantalGespeeldePunten)
    {
        $this->aantalGespeeldePunten = $aantalGespeeldePunten;
        parent::__construct($persoon->id, $persoon->naam, $persoon->email);
    }
}
