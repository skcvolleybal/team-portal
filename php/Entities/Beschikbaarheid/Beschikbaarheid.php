<?php

namespace TeamPortal\Entities;

use DateTime;

class Beschikbaarheid
{
    public ?int $id;
    public Persoon $persoon;
    public DateTime $date;
    public ?bool $isBeschikbaar;

    public function __construct(?int $id, Persoon $persoon, DateTime $date, ?bool $isBeschikbaar)
    {
        $this->id = $id;
        $this->persoon = $persoon;
        $this->date = $date;
        $this->isBeschikbaar = $isBeschikbaar;
    }

    public static function IsBeschikbaar(array $beschikbaarheden, DateTime $date): ?bool
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $date) {
                return $beschikbaarheid->isBeschikbaar;
            }
        }
        return null;
    }
}
