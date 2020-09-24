<?php

namespace TeamPortal\UseCases;

use DateTime;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;
use TeamPortal\Gateways;
use TeamPortal\Entities\Bardienst;
use TeamPortal\Entities\Email;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Wedstrijd;
use TeamPortal\Entities\Wedstrijddag;

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
        $this->scheidsco = $this->joomlaGateway->GetUser(2221); // M. M.
        $this->fromAddress = new Persoon(-1, $this->scheidsco->naam, "scheids@skcvolleybal.nl");
        $this->webcie = $this->joomlaGateway->GetUser(542);

        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 100);
        foreach ($wedstrijddagen as $dag) {
            $bardag = $this->barcieGateway->GetBardag($dag->date);
            $dag->barshifts = $bardag->shifts;
            $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($dag->date);
            if ($zaalwacht) {
                if ($zaalwacht->eersteZaalwacht) {
                    $dag->eersteZaalwacht = $zaalwacht->eersteZaalwacht;
                    $dag->eersteZaalwacht->teamgenoten = $this->joomlaGateway->GetTeamgenoten($dag->eersteZaalwacht);
                }

                if ($zaalwacht->tweedeZaalwacht) {
                    $dag->tweedeZaalwacht = $zaalwacht->tweedeZaalwacht;
                    $dag->tweedeZaalwacht->teamgenoten = $this->joomlaGateway->GetTeamgenoten($dag->tweedeZaalwacht);
                }
            }
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $fluitwedstrijd = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
                    $wedstrijd->scheidsrechter = $fluitwedstrijd->scheidsrechter;
                    $wedstrijd->tellers = $fluitwedstrijd->tellers;
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

                    if ($wedstrijd->tellers[0]) {
                        $emails[] = $this->CreateTellerMail($wedstrijd, $wedstrijd->tellers[0]);
                        $samenvatting->tellers[] = $wedstrijd->tellers[0];
                    }

                    if ($wedstrijd->tellers[1]) {
                        $emails[] = $this->CreateTellerMail($wedstrijd, $wedstrijd->tellers[1]);
                        $samenvatting->tellers[] = $wedstrijd->tellers[1];
                    }
                }
            }

            if ($dag->eersteZaalwacht) {
                foreach ($dag->eersteZaalwacht->teamgenoten as $teamgenoot) {
                    $emails[] = $this->CreateZaalwachtMail($dag, $teamgenoot, '1e zaalwacht shift');
                }
                $samenvatting->zaalwachtteams[] = $dag->eersteZaalwacht;
            }

            if ($dag->tweedeZaalwacht) {
                foreach ($dag->tweedeZaalwacht->teamgenoten as $teamgenoot) {
                    $emails[] = $this->CreateZaalwachtMail($dag, $teamgenoot, '2e zaalwacht shift');
                }
                $samenvatting->zaalwachtteams[] = $dag->tweedeZaalwacht;
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

    private function CreateScheidsrechterMail(Wedstrijd $wedstrijd): Email
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
        return new Email(
            $titel,
            $body,
            $scheidsrechter,
            $this->fromAddress
        );
    }

    private function CreateTellerMail(Wedstrijd $wedstrijd, Persoon $teller): Email
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
        return new Email(
            $titel,
            $body,
            $teller,
            $this->fromAddress
        );
    }

    private function CreateZaalwachtMail(Wedstrijddag $wedstrijddag, Persoon $zaalwachter, string $zaalwachttype): Email
    {
        $naam = $zaalwachter->naam;
        $datum = DateFunctions::GetDutchDateLong($wedstrijddag->date);

        $template = file_get_contents("./UseCases/Email/templates/zaalwachtTemplate.txt");
        $placeholders = [
            Placeholder::NAAM => $naam,
            Placeholder::DATUM => $datum,
            Placeholder::USER_ID => $zaalwachter->id,
            Placeholder::AFZENDER => $this->scheidsco->naam,
            Placeholder::ZAALWACHTTYPE => $zaalwachttype
        ];
        $body = Utilities::FillTemplate($template, $placeholders);
        $titel = "Zaalwacht $datum";
        if ($zaalwachttype === '1e zaalwacht shift') {
            $earliestMatch = $wedstrijddag->speeltijden[0]->wedstrijden[0];
            $tijdAanwezig = DateFunctions::AddMinutes($earliestMatch->timestamp, -60, true);
            $titel .= " ($tijdAanwezig aanwezig)";
        }

        return new Email(
            $titel,
            $body,
            $zaalwachter,
            $this->fromAddress
        );
    }

    private function CreateBarcieMail(Bardienst $bardienst, DateTime $dag): Email
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

        return new Email(
            "Bardienst " . $datum,
            $body,
            $bardienst->persoon,
            $this->fromAddress
        );
    }

    private function CreateSamenvattingMail(
        Emailsamenvatting $samenvatting,
        Persoon $receiver
    ): Email {
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

        return new Email(
            $title,
            $body,
            $receiver
        );
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
