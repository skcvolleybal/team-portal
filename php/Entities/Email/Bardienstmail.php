<?php

namespace TeamPortal\Entities;

use DateTime;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;

class Bardienstmail extends Email
{
    function __construct(Barlid $barlid, Persoon $teamtakenco, DateTime $date)
    {

        if ($barlid->isBhv) {
            $this->contructBHVMail($barlid, $teamtakenco, $date);
        } else {
            $this->constructBarMail($barlid, $teamtakenco, $date);
        }
    }

    private function constructBarMail(Barlid $barlid, Persoon $teamtakenco, DateTime $date) {
        $datum = DateFunctions::GetDutchDateLong($date);
        $naam = $barlid->naam;

        $template = file_get_contents("./Entities/Email/templates/barcieTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::NAAM => $naam,
            Placeholder::SHIFT => $shift,
            Placeholder::AFZENDER => $teamtakenco->naam,
            Placeholder::USER_ID => $barlid->id
        ];

        $this->titel = "Bardienst " . $datum;
        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->receiver = $barlid;

    }

    private function contructBHVMail(Barlid $barlid, Persoon $teamtakenco, DateTime $date) {
        $datum = DateFunctions::GetDutchDateLong($date);
        $naam = $barlid->naam;

        $template = file_get_contents("./Entities/Email/templates/BHVTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::NAAM => $naam,
            Placeholder::AFZENDER => $teamtakenco->naam,
            Placeholder::USER_ID => $barlid->id
        ];

        $this->titel = "BHV " . $datum;
        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->receiver = $barlid;

    }
}
