<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\Utilities;
use TeamPortal\UseCases\Emailsamenvatting;

class Samenvattingsmail extends Email
{
    public function __construct(
        Emailsamenvatting $samenvatting,
        Persoon $receiver
    ) {
        $barcieContent = $this->GetBoldHeader(count($samenvatting->barleden) > 0 ? "Barleden" : "Geen barleden");
        $scheidsrechtersContent = $this->GetBoldHeader(count($samenvatting->scheidsrechters) > 0 ? "Scheidsrechters" : "Geen scheidsrechters");
        $tellersContent = $this->GetBoldHeader(count($samenvatting->tellers) > 0 ? "Tellers" : "Geen tellers");
        $zaalwachtersContent = $this->GetBoldHeader(count($samenvatting->zaalwachtteams) > 0 ? "Zaalwacht" : "Geen zaalwacht");

        foreach ($samenvatting->scheidsrechters as $scheidsrechter) {
            $scheidsrechtersContent .= $this->GetNaamAndEmail($scheidsrechter);
        }

        foreach ($samenvatting->zaalwachtteams as $team) {
            $zaalwachtersContent .= $this->GetBoldHeader($team->naam);
            foreach ($team->teamgenoten as $teamgenoot) {
                $zaalwachtersContent .= $this->GetNaamAndEmail($teamgenoot);
            }
        }

        foreach ($samenvatting->tellers as $teller) {
            $tellersContent .= $this->GetNaamAndEmail($teller);
        }

        foreach ($samenvatting->barleden as $barlid) {
            $barcieContent  .= $this->GetNaamAndEmail($barlid);
        }

        $template = file_get_contents("./Entities/Email/templates/samenvattingTemplate.txt");
        $placeholders = [
            Placeholder::NAAM => $receiver->naam,
            Placeholder::SCHEIDSRECHTERS => $scheidsrechtersContent,
            Placeholder::TELLERS => $tellersContent,
            Placeholder::ZAALWACHTERS => $zaalwachtersContent,
            Placeholder::BARLEDEN => $barcieContent,
        ];

        $this->body = Utilities::FillTemplate($template, $placeholders);
        $this->titel = "Samenvatting fluit/tel/zaalwacht mails " . date("j-M-Y");
        $this->receiver = $receiver;
    }

    private function GetNaamAndEmail(Persoon $persoon): string
    {
        return $persoon->naam .  " (" . $persoon->email . ")" . $this->GetNewLine();
    }

    private function GetBoldHeader(string $titel): string
    {
        return "<b>" . $titel . "</b>" . $this->GetNewLine();
    }

    private function GetNewLine(): string
    {
        return "<br>";
    }
}
