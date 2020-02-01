<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Bardag;
use TeamPortal\Entities\Barshift;

class BardagModel
{
    public string $date;
    public string $datum;
    public array $shifts = [];

    public function __construct(Bardag $dag)
    {
        $this->date = DateFunctions::GetYmdNotation($dag->date);
        $this->datum = DateFunctions::GetDutchDate($dag->date);

        foreach ($dag->shifts as $i => $shift) {
            $this->shifts[] = new BarshiftModel($shift);
            foreach ($shift->barleden as $barlid) {
                $this->shifts[$i]->barleden[] = new BarlidModel($barlid);
            }
        }

        if (count($this->shifts) == 0) {
            $this->shifts[] = new BarshiftModel(new Barshift(1));
        }
    }
}
