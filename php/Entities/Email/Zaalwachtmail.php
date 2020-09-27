<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;

class Zaalwachtmail extends Email
{
    function __construct(Wedstrijddag $dag, Persoon $zaalwachter, Persoon $sender, string $zaalwachttype)
    {
        $naam = $zaalwachter->naam;
        $datum = DateFunctions::GetDutchDateLong($dag->date);

        $template = file_get_contents("./Entities/Email/templates/zaalwachtTemplate.txt");
        $placeholders = [
            Placeholder::NAAM => $naam,
            Placeholder::DATUM => $datum,
            Placeholder::USER_ID => $zaalwachter->id,
            Placeholder::AFZENDER => $sender->naam,
            Placeholder::ZAALWACHTTYPE => $zaalwachttype
        ];

        $titel = "Zaalwacht $datum";
        if ($zaalwachttype === Zaalwachttype::EersteZaalwacht) {
            $earliestMatch = $dag->speeltijden[0]->wedstrijden[0];
            $tijdAanwezig = DateFunctions::AddMinutes($earliestMatch->timestamp, -60, true);
            $titel .= " ($tijdAanwezig aanwezig)";
        }

        $this->titel = $titel;
        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->receiver = $zaalwachter;
    }
}
