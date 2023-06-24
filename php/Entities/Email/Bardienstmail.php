<?php

namespace TeamPortal\Entities;

use DateTime;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;

class Bardienstmail extends Email
{
    function __construct(Barlid $barlid, Persoon $teamtakenco, DateTime $date)
    {
        $datum = DateFunctions::GetDutchDateLong($date);
        $naam = $barlid->naam;
        $shift = $barlid->shift;
        $bhv = $barlid->isBhv == 1 ? "<br>Je bent BHV'er." : "";

        $template = file_get_contents("./Entities/Email/templates/barcieTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::NAAM => $naam,
            Placeholder::SHIFT => $shift,
            Placeholder::BHV => $bhv,
            Placeholder::AFZENDER => $teamtakenco->naam,
            Placeholder::USER_ID => $barlid->id
        ];

        $this->titel = "Bardienst " . $datum;
        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->receiver = $barlid;
    }
}
