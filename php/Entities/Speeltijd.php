<?php

class Speeltijd
{
    private DateTime $time;
    public array $wedstrijden = [];

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __construct(DateTime $time)
    {
        $this->time = $time;
    }
}
