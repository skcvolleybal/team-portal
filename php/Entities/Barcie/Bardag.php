<?php

namespace TeamPortal\Entities;

use \DateTime;

class Bardag
{
    public ?int $id;
    public \DateTime $date;
    public array $shifts = [];

    public function __construct(?int $id, \DateTime $date)
    {
        $this->id = $id;
        $this->date = $date;
    }
}
