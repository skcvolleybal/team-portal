<?php

class Barciedienst
{
    public $date;
    public $persoon;
    public $shift;
    public $isBhv;

    public function __construct($date, $persoon, $shift, $isBhv)
    {
        $this->date = $date;
        $this->persoon = $persoon;
        $this->shift = $shift;
        $this->isBhv = $isBhv;
    }
}
