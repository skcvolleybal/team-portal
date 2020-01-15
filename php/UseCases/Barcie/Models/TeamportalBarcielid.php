<?php

class TeamportalBarcielid
{
    public int $id;
    public string $naam;
    public bool $isBhv;

    public function __construct(Barcielid $barcielid)
    {
        $this->id = $barcielid->id;
        $this->naam = $barcielid->naam;
        $this->isBhv = $barcielid->isBhv;
    }
}
