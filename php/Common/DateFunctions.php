<?php

namespace TeamPortal\Common;

use DateInterval;
use DateTime;
use DateTimeImmutable;

setlocale(LC_ALL, "nl_NL");

class DateFunctions
{
    private const DAYS = [
        "zondag",
        "maandag",
        "dinsdag",
        "woensdag",
        "donderdag",
        "vrijdag",
        "zaterdag",
    ];
    private const MONTHS = [
        "januari",
        "februari",
        "maart",
        "april",
        "mei",
        "juni",
        "juli",
        "augustus",
        "september",
        "oktober",
        "november",
        "december",
    ];

    private static $DATE_FORMAT = "Y-m-d";
    private static $TIME_FORMAT = "H:i:s";

    static function GetDutchDate(DateTime $date): string
    {
        return trim(
            self::FormatDutchDay($date) .
                " " .
                self::MONTHS[(int) $date->format("n") - 1] .
                " " .
                $date->format("Y"),
        );
    }

    static function GetDutchDateLong(DateTime $date): string
    {
        // ucfirst because in Dutch, day name is not capitalized
        return ucfirst(
            self::DAYS[(int) $date->format("w")] .
                " " .
                self::FormatDutchDay($date) .
                " " .
                self::MONTHS[(int) $date->format("n") - 1] .
                " " .
                $date->format("Y"),
        );
    }

    /**
     * Formats the day of the month space-padded to two characters, like the
     * (removed) strftime('%e') did.
     */
    private static function FormatDutchDay(DateTime $date): string
    {
        return sprintf("%2d", (int) $date->format("j"));
    }

    static function GetYmdNotation(DateTime $timestamp): string
    {
        return $timestamp->format(self::$DATE_FORMAT);
    }

    static function GetTime(DateTime $timestamp): string
    {
        return $timestamp->format("H:i");
    }

    static function CreateDateTime(string $date, string $time = "00:00:00")
    {
        if (preg_match("/^\d{2}:\d{2}$/", $time)) {
            $time .= ":00";
        }
        $format = DateFunctions::$DATE_FORMAT . " " . self::$TIME_FORMAT;
        $timestring = $date . " " . $time;
        $date = DateTime::createFromFormat($format, $timestring);
        return $date !== false ? $date : null;
    }

    static function AreDatesEqual(?DateTime $date1, ?DateTime $date2)
    {
        if ($date1 === null || $date2 === null) {
            return false;
        }
        $dateFormat = DateFunctions::$DATE_FORMAT;
        return $date1->format($dateFormat) === $date2->format($dateFormat);
    }

    static function GetMySqlTimestamp(DateTime $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    static function AddMinutes(
        DateTime $date,
        int $minutes,
        bool $returnString = false,
    ) {
        $newDate = DateTimeImmutable::createFromMutable($date);
        $interval = new DateInterval("PT" . abs($minutes) . "M");
        if ($minutes < 0) {
            $interval->invert = 1;
        }
        $newDate = $newDate->add($interval);
        return $returnString
            ? $newDate->format("H:i")
            : DateTime::createFromImmutable($newDate);
    }
}
