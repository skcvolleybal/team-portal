<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;

class Scheidsrechtersmail extends Email
{
    public function __construct(Wedstrijd $wedstrijd, Persoon $sender)
    {
        $this->wedstrijd = $wedstrijd;

        $datum = DateFunctions::GetDutchDate($this->wedstrijd->timestamp);
        $tijd = DateFunctions::GetTime($this->wedstrijd->timestamp);

        $scheidsrechter = $this->wedstrijd->scheidsrechter;
        $spelendeTeams = $this->wedstrijd->team1->naam . " - " . $this->wedstrijd->team2->naam;

        $template = file_get_contents("./Entities/Email/templates/scheidsrechterTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::TIJD => $tijd,
            Placeholder::NAAM => $scheidsrechter->naam,
            Placeholder::USER_ID => $scheidsrechter->id,
            Placeholder::TEAMS => $spelendeTeams,
            Placeholder::AFZENDER => $sender->naam
        ];
        $tijdAanwezig = DateFunctions::AddMinutes($this->wedstrijd->timestamp, -30, true);

        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->titel = "Fluiten $spelendeTeams ($tijdAanwezig aanwezig)";
        $this->receiver = $scheidsrechter;
        $this->sender = $sender;
    }
}
