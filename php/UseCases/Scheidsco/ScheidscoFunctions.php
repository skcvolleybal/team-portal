<?php

function GetWedstrijdenWithDate($wedstrijden, $date)
{
    $result = [];
    foreach ($wedstrijden as $wedstrijd) {
        $timestamp = $wedstrijd['timestamp'];
        if ($timestamp && $timestamp->format('Y-m-d') == $date->format('Y-m-d')) {
            $result[] = $wedstrijd;
        }
    }
    return $result;
}

function GetWedstrijdOfTeam($wedstrijden, $team)
{
    if ($team == null) {
        return null;
    }
    foreach ($wedstrijden as $wedstrijd) {
        $skcTeam = ToNevoboName($team);
        if ($wedstrijd['team1'] == $skcTeam || $wedstrijd['team1'] == $skcTeam) {
            return $wedstrijd;
        }
    }
    return null;
}
