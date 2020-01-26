<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\DateFunctions;

class Barbeschikbaarheid extends Beschikbaarheid
{
    public Bardag $bardag;

    public function  __construct(Bardag $bardag, Persoon $persoon, ?bool $isBeschikbaar, int $id = null)
    {
        $this->bardag = $bardag;
        parent::__construct($id, $persoon, $bardag->date, $isBeschikbaar);
    }

    public static function IsMogelijk(array $wedstrijden): ?bool
    {
        if (count($wedstrijden) == 0) {
            return null;
        }

        $bestResult = true;
        foreach ($wedstrijden as $wedstrijd) {
            if (!$wedstrijd->IsThuis()) {
                return false;
            }

            $time = DateFunctions::GetTime($wedstrijd->timestamp);
            if ($time == "19:30" || $time == "16:00") {
                $bestResult = $bestResult ? true : null;
            } else {
                $bestResult = null;
            }
        }

        return $bestResult;
    }
}
