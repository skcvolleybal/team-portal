<?php

namespace TeamPortal\Entities;

use DateTime;

class Zaalwacht
{
    public ?Team $eersteZaalwacht;
    public ?Team $tweedeZaalwacht;
    public DateTime $date;
    public ?int $id;

    public function __construct(?int $id, DateTime $date, Team $eersteZaalwacht = null, Team $tweedeZaalwacht = null)
    {
        $this->id = $id;
        $this->date = $date;
        $this->eersteZaalwacht = $eersteZaalwacht;
        $this->tweedeZaalwacht = $tweedeZaalwacht;
    }
}
