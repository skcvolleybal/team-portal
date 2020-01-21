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

    static function CreateDateTime(string $date, string $time = "00:00:00")
    {
        if (preg_match("/^\d{2}:\d{2}$/", $time)) {
            $time .= ":00";
        }
        $format = DateFunctions::$DATE_FORMAT . " " . DateFunctions::$TIME_FORMAT;
        $timestring = $date . " " . $time;
        $date = DateTime::createFromFormat($format, $timestring);
        return $date !== false ? $date : null;
    }

    static function AreDatesEqual(DateTime $date1, DateTime $date2)
    {
        $dateFormat = DateFunctions::$DATE_FORMAT;
        return $date1->format($dateFormat) === $date2->format($dateFormat);
    }

    static function GetMySqlTimestamp(DateTime $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    static function AddMinutes(DateTime $date, int $minutes, bool $returnString = false)
    {
        $newDate = DateTimeImmutable::createFromMutable ($date);
        $interval = new DateInterval("PT" . abs($minutes) . "M");
        if ($minutes < 0) {
            $interval->invert = 1;
        }
        $newDate = $newDate->add($interval);
        return $returnString ? $newDate->format("H:i") : $newDate;
    }
}
