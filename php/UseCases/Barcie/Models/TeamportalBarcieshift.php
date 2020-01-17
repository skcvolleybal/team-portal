<?php

class TeamportalBarshift
{
    public int $shift;

    public function __construct(Barshift $shift)
    {
        $this->shift = $shift->shift;
    }
}
