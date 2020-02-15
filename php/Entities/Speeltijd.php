<?php

namespace TeamPortal\Entities;

use DateTime;

class Speeltijd
{
    private DateTime $time;
    public array $wedstrijden = [];

    public function __get(string $property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __construct(DateTime $time)
    {
        $this->time = $time;
    }

    public static function Compare(Speeltijd $speeltijd1, Speeltijd $speeltijd2)
    {
        return $speeltijd1->time < $speeltijd2->time;
    }
}
