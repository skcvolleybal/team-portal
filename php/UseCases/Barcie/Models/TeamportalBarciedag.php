<?php

class  TeamportalBarciedag
{
    public string $date;
    public string $datum;
    public array $shifts = [];

    public function __construct(Barciedag $dag)
    {
        $this->date = DateFunctions::GetYmdNotation($dag->date);
        $this->datum = DateFunctions::GetDutchDate($dag->date);

        foreach ($dag->shifts as $i => $shift) {
            $this->shifts[] = new TeamportalBarcieshift($shift);
            foreach ($shift->barcieleden as $barcielid) {
                $this->shifts[$i]->barcieleden[] = new TeamportalBarcielid($barcielid);
            }
        }
    }
}
