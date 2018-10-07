<?php

function UnauthorizedResult()
{
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

function InternalServerError($message)
{
    header("HTTP/1.1 500 Internal Server Error");
    exit($message);
}

function GetShortTeam($naam)
{
    return $naam[0] . substr($naam, 6);
}

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

function IsMogelijk($wedstrijd1, $wedstrijd2)
{
    if ($wedstrijd1 === null || $wedstrijd2 === null) {
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
            if ($hourDifference <= 2) {
                return false;
            } else if ($hourDifference >= 6) {
                return false;
            } else {
                return null;
            }
        }
    } else {
        if ($hourDifference <= 2) {
            return false;
        } else if ($hourDifference >= 6) {
            return false;
        } else {
            return null;
        }
    }
}

function IsThuis($locatie)
{
    return strpos($locatie, "Universitair SC") !== false;
}
