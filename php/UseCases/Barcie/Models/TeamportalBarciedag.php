<?php

class TeamportalBardag
{
    public string $date;
    public string $datum;
    public array $shifts = [];

    public function __construct(Bardag $dag)
    {
        $this->date = DateFunctions::GetYmdNotation($dag->date);
        $this->datum = DateFunctions::GetDutchDate($dag->date);

        foreach ($dag->shifts as $i => $shift) {
            $this->shifts[] = new TeamportalBarshift($shift);
            foreach ($shift->barleden as $barlid) {
                $this->shifts[$i]->barleden[] = new TeamportalBarlid($barlid);
            }
        }
    }
}
