<?php

class Overzichtsitem
{
    public string $type;
    public string $date;
    public string $datum;

    public function __construct(string $type, DateTime $date)
    {
        $this->type = $type;
        $this->date = DateFunctions::GetYmdNotation($date);
        $this->date = DateFunctions::GetDutchDate($date);
    }
}
