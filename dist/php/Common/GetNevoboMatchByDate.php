<?php
abstract class GetNevoboMatchByDate
{
    public function GetMatchesByDate($wedstrijden, $date)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd['timestamp'] && $wedstrijd['timestamp']->format("Y-m-d") == $date) {
                $result[] = $wedstrijd;
            }
        }
        return $result;
    }
}
