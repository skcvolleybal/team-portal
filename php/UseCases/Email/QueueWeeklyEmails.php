<?php


class Template
{
    public const NAAM = "{{naam}}";
    public const DATUM = "{{datum}}";
    public const TIJD = "{{tijd}}";
    public const USER_ID = "{{userId}}";
    public const TEAM = "{{team}}";
    public const TEAMS = "{{teams}}";
    public const AFZENDER = "{{afzender}}";
    public const SHIFT = "{{shift}}";
    public const BHV = "{{bhv}}";
    public const SCHEIDSRECHTERS = "{{scheidsrechters}}";
    public const TELLERS = "{{tellers}}";
    public const ZAALWACHTERS = "{{zaalwachters}}";
    public const BARLEDEN = "{{barleden}}";
}

class QueueWeeklyEmails implements Interactor
{
    private $scheidsco;
    private $webcie;
    private $fromAddress;

    public function __construct(
        NevoboGateway $nevoboGateway,
        TelFluitGateway $telFluitGateway,
        ZaalwachtGateway $zaalwachtGateway,
        EmailGateway $emailGateway,
        BarcieGateway $barcieGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->mailQueueGateway = $emailGateway;
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute()
    {
        $isServerRequest = $_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'];
        if (!$isServerRequest) {
            throw new UnexpectedValueException("Dit is niet een publieke api...");
        }

        $this->scheidsco = $this->joomlaGateway->GetUser(2223); // E. vd B.
        $this->fromAddress = new Persoon(-1, $this->scheidsco->naam, "scheids@skcvolleybal.nl");
        $this->webcie = $this->joomlaGateway->GetUser(542);

        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN');
        foreach ($wedstrijddagen as $dag) {

            $dag->barshifts = $this->barcieGateway->GetBardag($dag->date);
            $dag->zaalwacht = $this->zaalwachtGateway->GetZaalwacht($dag->date);
            if ($dag->zaalwacht) {
                $dag->zaalwacht->teamgenoten = $this->joomlaGateway->GetTeamgenoten($dag->zaalwacht->naam);
            }
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    list($scheidsrechter, $telteam) = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
                    $wedstrijd->scheidsrechter = $scheidsrechter;
                    $wedstrijd->telteam = $telteam;
                    if ($wedstrijd->telteam) {
                        $wedstrijd->telteam->teamgenoten = $this->joomlaGateway->GetTeamgenoten($telteam->naam);
                    }
                }
            }
        }

        $emails = $this->GetAllEmails($wedstrijddagen);

        $this->mailQueueGateway->QueueEmails($emails);
    }

    private function GetAllEmails(array $wedstrijdagen)
    {
        $emails = [];

        foreach ($wedstrijdagen as $dag) {
            foreach ($dag->wedstrijden as $wedstrijd) {
                if ($wedstrijd->scheidsrechter) {
                    $emails[] = $this->CreateScheidsrechterMail($wedstrijd);
                }

                if ($wedstrijd->telteam) {
                    foreach ($wedstrijd->telteam->teamgenoten as $teller) {
                        $emails[] = $this->CreateTellerMail($teller, $wedstrijd);
                    }
                }
            }

            if ($dag->zaalwacht) {
                foreach ($dag->zaalwacht->teamgenoten as $zaalwachter) {
                    $emails[] = $this->CreateZaalwachtMail($zaalwachter, $dag);
                }
            }

            foreach ($dag->bardiensten as $bardienst) {
                $emails[] = $this->CreateBarcieMail($bardienst, $dag);
            }
        }

        $emails[] = $this->CreateSamenvattingMail($wedstrijdagen, $this->webcie);
        $emails[] = $this->CreateSamenvattingMail($wedstrijdagen, $this->scheidsco);

        return $emails;
    }

    private function CreateScheidsrechterMail(Wedstrijd $wedstrijd)
    {
        $datum = DateFunctions::GetDutchDate($wedstrijd->timestamp);
        $tijd = DateFunctions::GetTime($wedstrijd->timestamp);

        $scheidsrechter = $wedstrijd->scheidsrechter;
        $naam = $scheidsrechter->naam;
        $userId = $scheidsrechter->id;
        $team = $scheidsrechter->team ?? "je team";
        $spelendeTeams = $wedstrijd->team1 . " - " . $wedstrijd->team2;

        $template = file_get_contents("./UseCases/Email/templates/scheidsrechterTemplate.txt");
        $placeholders = [
            Template::DATUM => $datum,
            Template::TIJD => $tijd,
            Template::NAAM => $naam,
            Template::USER_ID => $userId,
            Template::TEAM => $team,
            Template::TEAMS => $spelendeTeams,
            Template::AFZENDER => $this->scheidsco->naam
        ];
        $body = FillTemplate($template, $placeholders);

        $titel = "Fluiten " . $spelendeTeams;
        return new Email(
            $titel,
            $body,
            $scheidsrechter,
            $this->fromAddress
        );
    }

    private function CreateTellerMail(Persoon $teller, $wedstrijd)
    {
        $datum = DateFunctions::GetDutchDate($wedstrijd->timestamp);
        $tijd = $wedstrijd->timestamp->format('G:i');
        $naam = $teller->naam;
        $userId = $teller->id;
        $spelendeTeams = $wedstrijd->team1 . " - " . $wedstrijd->team2;

        $template = file_get_contents("./UseCases/Email/templates/tellerTemplate.txt");
        $placeholders = [
            Template::DATUM => $datum,
            Template::TIJD => $tijd,
            Template::NAAM => $naam,
            Template::USER_ID => $userId,
            Template::TEAMS => $spelendeTeams,
            Template::AFZENDER => $this->scheidsco->naam
        ];
        $body = FillTemplate($template, $placeholders);

        $titel = "Tellen " . $spelendeTeams;
        return new Email(
            $titel,
            $body,
            $teller,
            $this->fromAddress
        );
    }

    private function CreateZaalwachtMail($zaalwachter, $wedstrijddag)
    {
        $naam = $zaalwachter->naam;
        $datum = DateFunctions::GetDutchDateLong($wedstrijddag->date);

        $template = file_get_contents("./UseCases/Email/templates/zaalwachtTemplate.txt");
        $placeholders = [
            Template::NAAM => $naam,
            Template::DATUM => $datum,
            Template::AFZENDER => $this->scheidsco->naam,
        ];
        $body = FillTemplate($template, $placeholders);

        $titel = "Zaalwacht " . $datum;
        return new Email(
            $titel,
            $body,
            $zaalwachter,
            $this->fromAddress
        );
    }

    private function CreateBarcieMail($bardienst, $dag)
    {
        $datum = DateFunctions::GetDutchDateLong($dag->date);
        $naam = $bardienst->persoon->naam;
        $shift = $bardienst->shift;
        $bhv = $bardienst->isBhv == 1 ? "<br>Je bent BHV'er." : "";

        $template = file_get_contents("./UseCases/Email/templates/barcieTemplate.txt");
        $placeholders = [
            Template::DATUM => $datum,
            Template::NAAM => $naam,
            Template::SHIFT => $shift,
            Template::BHV => $bhv,
            Template::AFZENDER => $this->scheidsco->naam
        ];
        $body = FillTemplate($template, $placeholders);

        return new Email(
            "Bardienst " . $datum,
            $body,
            $bardienst->persoon,
            $this->fromAddress
        );
    }

    private function CreateSamenvattingMail($wedstrijddagen, $receiver)
    {
        $barcieContent = "";
        $scheidsrechtersContent = "";
        $tellersContent = "";
        $zaalwachtersContent = "";

        foreach ($wedstrijddagen as $dag) {
            foreach ($dag->bardiensten as $bardienst) {
                $barcieContent  .= $this->GetNaamAndEmail($bardienst->persoon);
            }
            if ($dag->zaalwacht) {
                $zaalwachtersContent .= $this->GetBoldHeader($dag->zaalwacht->naam);
                foreach ($dag->zaalwacht->teamgenoten as $persoon) {
                    $zaalwachtersContent .= $this->GetNaamAndEmail($persoon);
                }
                $zaalwachtersContent .= $this->GetNewLine();
            }

            foreach ($dag->wedstrijden as $wedstrijd) {
                if ($wedstrijd->scheidsrechter) {
                    $scheidsrechtersContent .= $this->GetNaamAndEmail($wedstrijd->scheidsrechter);
                }
                if ($wedstrijd->telteam) {
                    $tellersContent .= $this->GetBoldHeader($wedstrijd->telteam->naam);
                    foreach ($wedstrijd->telteam->teamgenoten as $teller) {
                        $tellersContent .= $this->GetNaamAndEmail($teller);
                    }
                    $tellersContent .= $this->GetNewLine();
                }
            }
        }

        $template = file_get_contents("./UseCases/Email/templates/samenvattingTemplate.txt");
        $placeholders = [
            Template::NAAM => $this->scheidsco->naam,
            Template::SCHEIDSRECHTERS => $scheidsrechtersContent,
            Template::TELLERS => $tellersContent,
            Template::ZAALWACHTERS => $zaalwachtersContent,
            Template::BARLEDEN => $barcieContent,
        ];
        $body = FillTemplate($template, $placeholders);

        $title = "Samenvatting fluit/tel/zaalwacht mails " . date("j-M-Y");

        return new Email(
            $title,
            $body,
            $receiver
        );
    }

    private function GetNaamAndEmail($persoon)
    {
        return $persoon->naam .  " (" . $persoon->email . ")" . $this->GetNewLine();
    }

    private function GetBoldHeader($titel)
    {
        return "<b>" . $titel . "</b>" . $this->GetNewLine();
    }

    private function GetNewLine()
    {
        return "<br>";
    }
}
