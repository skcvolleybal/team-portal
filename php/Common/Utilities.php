<?php

function GetSkcTeam($team)
{
    return ($team[4] == 'D' ? "Dames " : "Heren ") . substr($team, 7);
}

function ConvertToNevoboName($teamnaam)
{
    if (substr($teamnaam, 0, 6) == "Dames ") {
        return "SKC DS " . substr($teamnaam, 6);
    } else if (substr($teamnaam, 0, 6) == "Heren ") {
        return "SKC HS " . substr($teamnaam, 6);
    }

    throw new Exception("unknown team: " . $teamnaam);
}

function GetShortLocatie($locatie)
{
    $firstPart = substr($locatie, 0, strpos($locatie, ","));
    $lastPart = substr($locatie, strripos($locatie, " ") + 1);
    return $firstPart . ", " . $lastPart;
}

function CheckIfPossible($wedstrijd1, $wedstrijd2)
{
    if ($wedstrijd1 == null || $wedstrijd2 == null) {
        return true;
    }

    $timestamp1 = $wedstrijd1['timestamp'];
    $timestamp2 = $wedstrijd2['timestamp'];

    $difference = $timestamp1->diff($timestamp2, true);
    if ($difference->d > 0 || $difference->m > 0 || $difference->y > 0) {
        return true;
    }

    $hourDifference = $difference->h;

    if (IsThuis($wedstrijd1['locatie'])) {
        if (IsThuis($wedstrijd2['locatie'])) {
            return $hourDifference >= 2;
        } else {
            return $hourDifference >= 4;
        }
    } else {
        return $hourDifference >= 4;
    }
}

function IsThuis($locatie)
{
    return strpos($locatie, "Universitair SC") >= 0;
}
