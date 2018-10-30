<?php
setlocale(LC_ALL, 'nl_NL');

function IsDateValid($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}

function GetPostValues()
{
    $postData = file_get_contents("php://input");
    if (empty($postData)) {
        return null;
    }

    return json_decode($postData);
}

function GetDutchDate($datetime)
{
    if ($datetime) {
        return strftime("%e %B %Y", $datetime->getTimestamp());
    }
}

function GetDutchDateLong($datetime)
{
    if ($datetime) {
        return strftime("%A %e %B %Y", $datetime->getTimestamp());
    }
}

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

function GetQueryStringParamater($name)
{
    $queryString = $_SERVER['QUERY_STRING'];
    if (empty($queryString)) {
        return null;
    }

    parse_str($queryString, $parsedQueryString);
    if (isset($parsedQueryString[$name])) {
        return $parsedQueryString[$name];
    }

    return null;
}

function GetShortTeam($naam)
{
    if ($naam == null) {
        return null;
    }
    if (substr($naam, 0, 4) == "SKC ") {
        return $naam[4] . substr($naam, 7);
    } else {
        return $naam[0] . substr($naam, 6);
    }

}

function ToSkcName($team)
{
    if ($team == null) {
        return null;
    }
    return ($team[4] == 'D' ? "Dames " : "Heren ") . substr($team, 7);
}

function ToNevoboName($teamnaam)
{
    return substr($teamnaam, 0, 6) == "Dames " ? "SKC DS " . substr($teamnaam, 6) : "SKC HS " . substr($teamnaam, 6);
}

function RemoveMatchesWithoutData($array)
{
    return array_filter($array, function ($match) {return $match['timestamp'];});
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

function SendPost($url, $post_fields = null, $headers = null)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($post_fields && !empty($post_fields)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    }
    if ($headers && !empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return $data;
}
