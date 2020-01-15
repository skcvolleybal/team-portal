<?php

class TeamportalBarcieshift
{
    public int $shift;

    public function __construct(Barcieshift $shift)
    {
        $this->shift = $shift->shift;
    }
}
