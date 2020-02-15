<?php

namespace TeamPortal\UseCases;

use DateTime;
use TeamPortal\Common\DateFunctions;

class Overzichtsitem
{
    public string $type;
    public string $date;
    public string $datum;

    public function __construct(string $type, DateTime $date)
    {
        $this->type = $type;
        $this->date = DateFunctions::GetYmdNotation($date);
        $this->datum = DateFunctions::GetDutchDate($date);
    }
}
