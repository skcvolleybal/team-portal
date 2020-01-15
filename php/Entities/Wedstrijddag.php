<?php

class Wedstrijddag
{
    private $date;
    public array $speeltijden = [];
    public array $barshifts = [];
    public ?Team $zaalwacht = null;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    function __construct($date)
    {
        $this->date = $date;
    }

    function AddWedstrijd(Wedstrijd $wedstrijd)
    {
        if ($wedstrijd === null) {
            return;
        }
        foreach ($this->speeltijden as $speeltijd) {
            if ($speeltijd->time == $wedstrijd->timestamp) {
                $speeltijd->wedstrijden[] = $wedstrijd;
                return;
            }
        }
        $this->speeltijden[] = new Speeltijd($wedstrijd->timestamp);
        $this->speeltijden[0]->wedstrijden[] = $wedstrijd;
    }
}
