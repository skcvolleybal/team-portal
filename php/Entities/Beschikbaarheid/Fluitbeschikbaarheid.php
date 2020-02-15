<?php

namespace TeamPortal\Entities;

use DateTime;
use TeamPortal\Common\DateFunctions;

class Fluitbeschikbaarheid extends Beschikbaarheid
{
    public Speeltijd $speeltijd;

    public static function isFluitenMogelijk(array $wedstrijden, DateTime $tijd): ?bool
    {
        $bestResult = true;
        foreach ($wedstrijden as $wedstrijd) {
            $format = 'Y-m-d H:i';
            $timestring = DateFunctions::GetYmdNotation($tijd) . " " . DateFunctions::GetTime($tijd);

            $fluitwedstrijd = new Wedstrijd("fluitwedstrijd");
            $fluitwedstrijd->timestamp =  DateTime::createFromFormat($format, $timestring);
            $fluitwedstrijd->locatie = "Universitair SC";

            $isMogelijk = $wedstrijd->isMogelijk($fluitwedstrijd);
            if ($isMogelijk === false) {
                return false;
            }
            $bestResult = $isMogelijk === null ? null : $bestResult;
        }

        return $bestResult;
    }
}
