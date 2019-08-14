<?php
setlocale(LC_ALL, 'nl_NL');

function IsDateValid($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function GetPostValues()
{
    $postData = file_get_contents('php://input');
    if (empty($postData)) {
        return null;
    }

    return json_decode($postData);
}

function GetQueryParameters()
{
    parse_str($_SERVER['QUERY_STRING'], $query_array);
    return (object) $query_array;
}

function GetDutchDate($datetime)
{
    if ($datetime) {
        return trim(strftime('%e %B %Y', $datetime->getTimestamp()));
    }
}

function GetDutchDateLong($datetime)
{
    if ($datetime) {
        return trim(strftime('%A %e %B %Y', $datetime->getTimestamp()));
    }
}

function UnauthorizedResult()
{
    header('HTTP/1.1 401 Unauthorized');
    exit;
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
    if (substr($naam, 0, 4) == 'SKC ') {
        return $naam[4] . substr($naam, 7);
    } else {
        return $naam[0] . substr($naam, 6);
    }
}

function isNevoboFormat($naam)
{
    return preg_match('/^SKC [D|H]S \d+$/i', $naam);
}

function isSkcFormat($naam)
{
    return preg_match('/^(Heren|Dames) \d+$/i', $naam);
}

function ToSkcName($naam)
{
    if (isSkcFormat($naam)) {
        return $naam;
    }
    if (!isNevoboFormat($naam)) {
        throw new InvalidArgumentException("Iets fout met het team: '$naam'");
    }
    return ($naam[4] == 'D' ? 'Dames ' : 'Heren ') . substr($naam, 7);
}

function ToNevoboName($naam)
{
    if (isNevoboFormat($naam)) {
        return $naam;
    }
    if (!isSkcFormat($naam)) {
        throw new InvalidArgumentException("Iets fout met het team: '$naam'");
    }
    return substr($naam, 0, 6) == 'Dames ' ? 'SKC DS ' . substr($naam, 6) : 'SKC HS ' . substr($naam, 6);
}

function RemoveMatchesWithoutData($array)
{
    return array_filter($array, function ($match) {
        return $match->timestamp;
    });
}

function WedstrijdenSortFunction($w1, $w2)
{
    if (!$w1->timestamp) {
        return -1;
    }
    if (!$w2->timestamp) {
        return 1;
    }
    return $w1->timestamp > $w2->timestamp;
}

function GetShortLocatie($locatie)
{
    $firstPart = substr($locatie, 0, strpos($locatie, ','));
    $lastPart = substr($locatie, strripos($locatie, ' ') + 1);
    return $firstPart . ', ' . $lastPart;
}

function IsMogelijk($wedstrijd1, $wedstrijd2)
{
    if ($wedstrijd1 === null || $wedstrijd2 === null) {
        return 'Ja';
    }

    $timestamp1 = $wedstrijd1->timestamp;
    $timestamp2 = $wedstrijd2->timestamp;

    $difference = $timestamp1->diff($timestamp2, true);
    if ($difference->d > 0 || $difference->m > 0 || $difference->y > 0) {
        return 'Ja';
    }

    $hourDifference = $difference->h + ($difference->i / 60);

    if (IsThuis($wedstrijd1->locatie) && IsThuis($wedstrijd2->locatie)) {
        return $hourDifference >= 2 ? 'Ja' : 'Nee';
    } else {
        if ($hourDifference < 4) {
            return 'Nee';
        } else if ($hourDifference >= 6) {
            return 'Ja';
        } else {
            return 'Onbekend';
        }
    }
}

function IsThuis($locatie)
{
    return strpos($locatie, 'Universitair SC') !== false;
}

function SanitizeQueryString($url)
{
    $url = explode('?', $url);
    $parts = explode('&', $url[1]);
    $newParts = [];
    foreach ($parts as $part) {
        $params = explode('=', $part);
        $newParts[] = $params[0] . '=' . rawurlencode($params[1]);
    }
    return $url[0] . '?' . implode('&', $newParts);
}

function SendPost($url, $post_fields, $headers = null)
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

function GetConfigValue($key)
{
    $config = JFactory::getConfig();
    return $config->get($key);
}

function GetKlasse($poule)
{
    if (strlen($poule) !== 3) {
        throw new InvalidArgumentException('Poule $poule is niet valide');
    }
    if ($poule[1] == 'P') {
        return 'Promotieklasse';
    } else if (is_numeric($poule[1])) {
        return $poule[1] . 'e klasse';
    }
}
