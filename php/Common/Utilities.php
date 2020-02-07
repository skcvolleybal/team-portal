<?php

namespace TeamPortal\Common;

class Utilities
{
    public static function Round(float $number, int $digits = 1)
    {
        $fraction = pow(10, $digits);
        return round($number * $fraction) / $fraction;
    }

    public static function IsNullOrEmpty($obj)
    {
        return !$obj || empty($obj);
    }

    public static function FillTemplate($template, $placeholders)
    {
        foreach ($placeholders as $placeholder => $value) {
            if ($value === null) {
                throw new \UnexpectedValueException("Fout bij matchen van template placeholders: value === null");
            }
            if (strpos($template, $placeholder) == -1) {
                throw new \UnexpectedValueException("Kan placeholder '$placeholder' niet vinden");
            }
            $template = str_replace("$placeholder", $value, $template);
        }

        return $template;
    }

    public static function StringToInt($getal)
    {
        return $getal ? intval($getal) : null;
    }

    public static function GetCurrentSeizoen()
    {
        $month = Utilities::StringToInt(date('m', time()));
        $year = Utilities::StringToInt(date('Y', time()));
        if ($month >= 7) {
            return $year . "/" . ($year + 1);
        } else {
            return ($year - 1) . "/" . $year;
        }
    }
}
