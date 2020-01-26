<?php

namespace TeamPortal\Entities;

class Wedstrijddag
{
    private $date;
    public array $speeltijden = [];
    public array $barshifts = [];
    public array $bardiensten = [];
    public array $eigenWedstrijden = [];
    public ?Zaalwacht $zaalwacht = null;

    public function __get(string $property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    function __construct($date)
    {
        $this->date = $date;
    }

    function AddWedstrijd(Wedstrijd $wedstrijd): void
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
        $this->speeltijden[] = new Entities\Speeltijd($wedstrijd->timestamp);
        $this->speeltijden[0]->wedstrijden[] = $wedstrijd;
    }

    public static function Compare(Wedstrijddag $dag1, Wedstrijddag $dag2): bool
    {
        return $dag1->date > $dag2->date;
    }
}
