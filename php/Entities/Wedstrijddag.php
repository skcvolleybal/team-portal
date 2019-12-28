<?php

class Wedstrijddag
{
    private $date;
    public $wedstrijden;
    public $barcieleden;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    function __construct($timestamp)
    {
        $this->date = $timestamp;
    }
}
