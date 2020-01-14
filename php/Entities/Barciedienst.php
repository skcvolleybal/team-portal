<?php

class Barciedienst
{
    public DateTime $date;
    public ?Persoon $persoon;
    public ?int $shift;
    public ?bool $isBhv;

    public function __construct(DateTime $date, ?Persoon $persoon, ?int $shift, ?bool $isBhv, $id = null)
    {
        $this->id = $id;
        $this->date = $date;
        $this->persoon = $persoon;
        $this->shift = $shift;
        $this->isBhv = $isBhv;
    }
}
