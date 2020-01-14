<?php

class Zaalwacht
{
    public ?Team $team;
    public DateTime $date;
    public ?int $id;

    public function __construct(DateTime $date, int $id = null, Team $team = null)
    {
        $this->id = $id;
        $this->team = $team;
        $this->date = $date;
    }
}
