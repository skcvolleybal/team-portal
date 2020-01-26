<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;
use TeamPortal\Gateways;
use TeamPortal\Entities;

class QueueWeeklyEmails implements Interactor
{
    private $scheidsco;
    private $webcie;
    private $fromAddress;

    public function __construct(
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\ZaalwachtGateway $zaalwachtGateway,
        Gateways\EmailGateway $emailGateway,
        Gateways\BarcieGateway $barcieGateway,
        Gateways\JoomlaGateway $joomlaGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->mailQueueGateway = $emailGateway;
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $this->scheidsco = $this->joomlaGateway->GetUser(2223); // E. vd B.
        $this->fromAddress = new Entities\Persoon(-1, $this->scheidsco->naam, "scheids@skcvolleybal.nl");
        $this->webcie = $this->joomlaGateway->GetUser(542);

        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal();
        foreach ($wedstrijddagen as $dag) {
            $bardag = $this->barcieGateway->GetBardag($dag->date);
            $dag->barshifts = $bardag->shifts;
            $dag->zaalwacht = $this->zaalwachtGateway->GetZaalwacht($dag->date);
            if ($dag->zaalwacht) {
                $dag->zaalwacht->teamgenoten = $this->joomlaGateway->GetTeamgenoten($dag->zaalwacht->team);
            }
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $fluitwedstrijd = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
                    $wedstrijd->scheidsrechter = $fluitwedstrijd->scheidsrechter;
                    $wedstrijd->telteam = $fluitwedstrijd->telteam;
                    if ($wedstrijd->telteam) {
                        $wedstrijd->telteam->teamgenoten = $this->joomlaGateway->GetTeamgenoten($fluitwedstrijd->telteam);
                    }
                }
            }
        }

        $emails = $this->GetAllEmails($wedstrijddagen);

        $this->mailQueueGateway->QueueEmails($emails);
    }

    private function GetAllEmails(array $wedstrijdagen): array
    {
        $emails = [];

        $samenvatting = new Emailsamenvatting();
        foreach ($wedstrijdagen as $dag) {
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    if ($wedstrijd->scheidsrechter) {
                        $emails[] = $this->CreateScheidsrechterMail($wedstrijd);
                        $samenvatting->scheidsrechters[] = $wedstrijd->scheidsrechter;
                    }

                    if ($wedstrijd->telteam) {
                        foreach ($wedstrijd->telteam->teamgenoten as $teller) {
                            $emails[] = $this->CreateTellerMail($wedstrijd, $teller);
                        }
                        $samenvatting->telteams[] = $wedstrijd->telteam;
                    }
                }
            }

            if ($dag->zaalwacht) {
                foreach ($dag->zaalwacht->teamgenoten as $teamgenoot) {
                    $emails[] = $this->CreateZaalwachtMail($dag, $teamgenoot);
                }
                $samenvatting->zaalwachtteams[] = $dag->zaalwacht;
            }

            foreach ($dag->bardiensten as $bardienst) {
                $emails[] = $this->CreateBarcieMail($bardienst, $dag);
                $samenvatting->barleden[] = $bardienst->persoon;
            }
        }

        $emails[] = $this->CreateSamenvattingMail($samenvatting, $this->webcie);
        $emails[] = $this->CreateSamenvattingMail($samenvatting, $this->scheidsco);

        return $emails;
    }

    private function CreateScheidsrechterMail(Entities\Wedstrijd $wedstrijd): Entities\Email
    {
        $datum = DateFunctions::GetDutchDate($wedstrijd->timestamp);
        $tijd = DateFunctions::GetTime($wedstrijd->timestamp);

        $scheidsrechter = $wedstrijd->scheidsrechter;
        $naam = $scheidsrechter->naam;
        $userId = $scheidsrechter->id;
        $spelendeTeams = $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam;

        $template = file_get_contents("./UseCases/Email/templates/scheidsrechterTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::TIJD => $tijd,
            Placeholder::NAAM => $naam,
            Placeholder::USER_ID => $userId,
            Placeholder::TEAMS => $spelendeTeams,
            Placeholder::AFZENDER => $this->scheidsco->naam
        ];
        $body = Utilities::FillTemplate($template, $placeholders);

        $tijdAanwezig = DateFunctions::AddMinutes($wedstrijd->timestamp, -30, true);
        $titel = "Fluiten $spelendeTeams ($tijdAanwezig aanwezig)";
        return new Entities\Email(
            $titel,
            $body,
            $scheidsrechter,
            $this->fromAddress
        );
    }

    private function CreateTellerMail(Entities\Wedstrijd $wedstrijd, Entities\Persoon $teller): Entities\Email
    {
        $datum = DateFunctions::GetDutchDate($wedstrijd->timestamp);
        $tijd = $wedstrijd->timestamp->format('G:i');
        $naam = $teller->naam;
        $userId = $teller->id;
        $spelendeTeams = $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam;

        $template = file_get_contents("./UseCases/Email/templates/tellerTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::TIJD => $tijd,
            Placeholder::NAAM => $naam,
            Placeholder::USER_ID => $userId,
            Placeholder::TEAMS => $spelendeTeams,
            Placeholder::AFZENDER => $this->scheidsco->naam
        ];
        $body = Utilities::FillTemplate($template, $placeholders);

        $tijdAanwezig = DateFunctions::AddMinutes($wedstrijd->timestamp, -15, true);
        $titel = "Tellen $spelendeTeams ($tijdAanwezig aanwezig)";
        return new Entities\Email(
            $titel,
            $body,
            $teller,
            $this->fromAddress
        );
    }

    private function CreateZaalwachtMail(Entities\Wedstrijddag $wedstrijddag, Entities\Persoon $zaalwachter): Entities\Email
    {
        $naam = $zaalwachter->naam;
        $datum = DateFunctions::GetDutchDateLong($wedstrijddag->date);

        $template = file_get_contents("./UseCases/Email/templates/zaalwachtTemplate.txt");
        $placeholders = [
            Placeholder::NAAM => $naam,
            Placeholder::DATUM => $datum,
            Placeholder::USER_ID => $zaalwachter->id,
            Placeholder::AFZENDER => $this->scheidsco->naam,
        ];
        $body = Utilities::FillTemplate($template, $placeholders);

        $earliestMatch = $wedstrijddag->speeltijden[0]->wedstrijden[0];
        $tijdAanwezig = DateFunctions::AddMinutes($earliestMatch->timestamp, -60, true);
        $titel = "Zaalwacht $datum ($tijdAanwezig aanwezig)";
        return new Entities\Email(
            $titel,
            $body,
            $zaalwachter,
            $this->fromAddress
        );
    }

    private function CreateBarcieMail(Entities\Bardienst $bardienst, \DateTime $dag): Entities\Email
    {
        $datum = DateFunctions::GetDutchDateLong($dag->date);
        $naam = $bardienst->persoon->naam;
        $shift = $bardienst->shift;
        $bhv = $bardienst->isBhv == 1 ? "<br>Je bent BHV'er." : "";

        $template = file_get_contents("./UseCases/Email/templates/barcieTemplate.txt");
        $placeholders = [
            Placeholder::DATUM => $datum,
            Placeholder::NAAM => $naam,
            Placeholder::SHIFT => $shift,
            Placeholder::BHV => $bhv,
            Placeholder::AFZENDER => $this->scheidsco->naam
        ];
        $body = Utilities::FillTemplate($template, $placeholders);

        return new Entities\Email(
            "Bardienst " . $datum,
            $body,
            $bardienst->persoon,
            $this->fromAddress
        );
    }

    private function CreateSamenvattingMail(
        Emailsamenvatting $samenvatting,
        Entities\Persoon $receiver
    ): Entities\Email {
        $barcieContent = $this->GetBoldHeader(count($samenvatting->barleden) > 0 ? "Barleden" : "Geen barleden");
        $scheidsrechtersContent = $this->GetBoldHeader(count($samenvatting->scheidsrechters) > 0 ? "Scheidsrechters" : "Geen scheidsrechters");
        $tellersContent = $this->GetBoldHeader(count($samenvatting->telteams) > 0 ? "Tellers" : "Geen tellers");
        $zaalwachtersContent = $this->GetBoldHeader(count($samenvatting->zaalwachtteams) > 0 ? "Zaalwacht" : "Geen zaalwacht");

        foreach ($samenvatting->scheidsrechters as $scheidsrechter) {
            $scheidsrechtersContent .= $this->GetNaamAndEmail($scheidsrechter);
        }

        foreach ($samenvatting->zaalwachtteams as $team) {
            $zaalwachtersContent .= $this->GetBoldHeader($team->team->naam);
            foreach ($team->teamgenoten as $teamgenoot) {
                $zaalwachtersContent .= $this->GetNaamAndEmail($teamgenoot);
            }
        }

        foreach ($samenvatting->telteams as $team) {
            $tellersContent .= $this->GetBoldHeader($team->naam);
            foreach ($team->teamgenoten as $teamgenoot) {
                $tellersContent .= $this->GetNaamAndEmail($teamgenoot);
            }
        }

        foreach ($samenvatting->barleden as $barlid) {
            $barcieContent  .= $this->GetNaamAndEmail($barlid);
        }

        $template = file_get_contents("./UseCases/Email/templates/samenvattingTemplate.txt");
        $placeholders = [
            Placeholder::NAAM => $this->scheidsco->naam,
            Placeholder::SCHEIDSRECHTERS => $scheidsrechtersContent,
            Placeholder::TELLERS => $tellersContent,
            Placeholder::ZAALWACHTERS => $zaalwachtersContent,
            Placeholder::BARLEDEN => $barcieContent,
        ];
        $body = Utilities::FillTemplate($template, $placeholders);

        $title = "Samenvatting fluit/tel/zaalwacht mails " . date("j-M-Y");

        return new Entities\Email(
            $title,
            $body,
            $receiver
        );
    }

    private function GetNaamAndEmail(Entities\Persoon $persoon): string
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
