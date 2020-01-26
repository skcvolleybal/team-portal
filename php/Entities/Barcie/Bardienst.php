<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;

class Bardienst
{
    public ?int $id;
    public Bardag $bardag;
    public ?Persoon $persoon;
    public ?int $shift;
    public ?bool $isBhv;

    public function __construct(Bardag $bardag, ?Persoon $persoon, ?int $shift, ?bool $isBhv, int $id = null)
    {
        $this->id = $id;
        $this->bardag = $bardag;
        $this->persoon = $persoon;
        $this->shift = $shift;
        $this->isBhv = $isBhv;
    }

    public function GetStartTime()
    {
        $weekday = Utilities::StringToInt(date('w', $this->bardag->date));
        $date = DateFunctions::GetYmdNotation($this->bardag->date);
        switch ($weekday) {
            case 0:
                return DateFunctions::CreateDateTime($date, "13:00");
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                return DateFunctions::CreateDateTime($date, "22:00");
            case 6:
                return DateFunctions::CreateDateTime($date, "18:00");
            default:
                throw new \UnexpectedValueException();
        }
    }
}
