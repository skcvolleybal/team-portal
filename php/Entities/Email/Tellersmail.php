<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;

class Tellersmail extends Email
{
    function __construct(Wedstrijd $wedstrijd, Persoon $teller, Persoon $sender)
    {
        $datum = DateFunctions::GetDutchDate($wedstrijd->timestamp);
        $tijd = $wedstrijd->timestamp->format('G:i');
        $naam = $teller->naam;
        $userId = $teller->id;
        $spelendeTeams = $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam;

        $template = file_get_contents("./Entities/Email/templates/tellerTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::TIJD => $tijd,
            Placeholder::NAAM => $naam,
            Placeholder::USER_ID => $userId,
            Placeholder::TEAMS => $spelendeTeams,
            Placeholder::AFZENDER => $sender->naam
        ];
        $tijdAanwezig = DateFunctions::AddMinutes($wedstrijd->timestamp, -15, true);

        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->titel = "Tellen $spelendeTeams ($tijdAanwezig aanwezig)";
        $this->receiver = $teller;
    }
}
