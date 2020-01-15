<?php

class  Barciedag
{
    public DateTime $date;
    public array $shifts = [];

    public function __construct(DateTime $date)
    {
        $this->date = $date;
    }
}
