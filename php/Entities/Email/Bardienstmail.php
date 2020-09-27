<?php

namespace TeamPortal\Entities;

use DateTime;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;

class Bardienstmail extends Email
{
    function __construct(Bardienst $bardienst, DateTime $dag)
    {
        $datum = DateFunctions::GetDutchDateLong($dag->date);
        $naam = $bardienst->persoon->naam;
        $shift = $bardienst->shift;
        $bhv = $bardienst->isBhv == 1 ? "<br>Je bent BHV'er." : "";

        $template = file_get_contents("./Entities/Email/templates/barcieTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::NAAM => $naam,
            Placeholder::SHIFT => $shift,
            Placeholder::BHV => $bhv,
            Placeholder::AFZENDER => $this->scheidsco->naam
        ];

        $this->titel = "Bardienst " . $datum;
        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->receiver = $bardienst->persoon;
    }
}
