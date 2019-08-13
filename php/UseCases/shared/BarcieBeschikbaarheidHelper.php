<?php
class BarcieBeschikbaarheidHelper
{
    function IsMogelijk($wedstrijden)
    {
        if (count($wedstrijden) == 0) {
            return "Onbekend";
        }

        $bestResult = "Ja";
        foreach ($wedstrijden as $wedstrijd) {
            if (!IsThuis($wedstrijd->locatie)) {
                return "Nee";
            }
            if ($wedstrijd->timestamp) {
                $time = $wedstrijd->timestamp->format('H:i');
                if ($time == "19:30" || $time == "16:00") {
                    $bestResult = $bestResult == "Ja" ? "Ja" : "Onbekend";
                } else {
                    $bestResult = "Onbekend";
                }
            }
        }

        return $bestResult;
    }
}
