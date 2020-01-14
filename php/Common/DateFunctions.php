<?php

class DateFunctions
{
    private static $DATE_FORMAT = 'Y-m-d';
    private static $TIME_FORMAT = 'H:i:s';

    static function GetDutchDate($datetime): string
    {
        if ($datetime) {
            return trim(strftime('%e %B %Y', $datetime->getTimestamp()));
        }
    }

    static function GetDutchDateLong($datetime): string
    {
        if ($datetime) {
            return trim(strftime('%A %e %B %Y', $datetime->getTimestamp()));
        }
        return null;
    }

    static function GetYmdNotation(DateTime $timestamp): string
    {
        return $timestamp->format(DateFunctions::$DATE_FORMAT);
    }

    static function GetTime(DateTime $timestamp): string
    {
        return $timestamp->format('G:i');
    }

    static function CreateDateTime(string $date, $time = "00:00")
    {
        $format = DateFunctions::$DATE_FORMAT . " " . DateFunctions::$TIME_FORMAT . ".u";
        $timestring = $date . " " . $time . ":00.000000";
        return DateTime::createFromFormat($format, $timestring);
    }

    static function AreDatesEqual(DateTime $date1, DateTime $date2)
    {
        $dateFormat = DateFunctions::$DATE_FORMAT;
        return $date1->format($dateFormat) === $date2->format($dateFormat);
    }

    static function AreDateTimesEqual(DateTime $date1, DateTime $date2)
    {
        $dateFormat = DateFunctions::$DATE_FORMAT . " " . DateFunctions::$TIME_FORMAT;
        return $date1->format($dateFormat) === $date2->format($dateFormat);
    }
}
