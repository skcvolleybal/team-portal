<?php
include_once 'IInteractor.php';
include_once 'NevoboGateway.php';
include_once 'TelFluitGateway.php';
include_once 'MailGateway.php';
include_once 'ZaalwachtGateway.php';

class SendWeeklyEmails implements IInteractor
{
    private $scheidsco = [
        "email" => "scheids@skcvolleybal.nl",
        "naam" => "Anne Vieveen",
    ];

    private $nevoboGateway;
    private $telFluitGateway;
    private $zaalwachtGateway;
    private $mailGateway;

    public function __construct($database)
    {
        $this->nevoboGateway = new NevoboGateway();
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
        $this->mailGateway = new MailGateway();
    }

    public function Execute()
    {
        $isServerRequest = $_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'];
        if ($isServerRequest == false) {
            InternalServerError("Dit is niet een publieke api...");
        }

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');

        $wedstrijden = $this->GetWedstrijdenInPeriod($uscWedstrijden, 7);
        $wedstrijdIds = [];
        foreach ($wedstrijden as $wedstrijd) {
            $wedstrijdIds[] = $wedstrijd['id'];
        }

        $scheidsrechters = $this->telFluitGateway->GetScheidsrechtersForWedstrijdenWithMatchId($wedstrijdIds);
        $tellers = $this->telFluitGateway->GetTellersForWedstrijdenWithMatchId($wedstrijdIds);
        $zaalwachters = $this->zaalwachtGateway->GetZaalwachtersWithinPeriod(7);

        foreach ($wedstrijden as $wedstrijd) {
            $scheidsrechter = $this->GetScheidsrechterFromList($scheidsrechters, $wedstrijd);
            if ($scheidsrechter != null) {
                $this->MailScheidsrechter($scheidsrechter, $wedstrijd);
            }
            $wedstrijdTellers = $this->GetTellersFromList($tellers, $wedstrijd);
            foreach ($wedstrijdTellers as $teller) {
                $this->MailTeller($teller, $wedstrijd);
            }
        }

        foreach ($zaalwachters as $zaalwachter) {
            $this->MailZaalwachter($zaalwachter);
        }

        $this->MailSamenvatting($wedstrijden, $scheidsrechters, $tellers, $zaalwachters);

        exit("Verzonden");
    }

    private function GetTellersFromList($tellers, $wedstrijd)
    {
        $result = [];
        foreach ($tellers as $teller) {
            if ($teller['matchId'] == $wedstrijd['id']) {
                $result[] = $teller;
            }
        }
        return $result;
    }

    private function GetScheidsrechterFromList($scheidsrechters, $wedstrijd)
    {
        foreach ($scheidsrechters as $scheidsrechter) {
            if ($scheidsrechter['matchId'] == $wedstrijd['id']) {
                return $scheidsrechter;
            }
        }
        return null;
    }

    private function GetWedstrijdenInPeriod($wedstrijden, $numberOfDays)
    {
        $result = [];
        $startDate = date("Y-m-d");
        $endDate = date("Y-m-d", strtotime("+$numberOfDays days"));
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd['timestamp'] != null) {
                $date = $wedstrijd['timestamp']->format('Y-m-d');
                $wedstrijdId = $wedstrijd['id'];
                if ($startDate <= $date && $date <= $endDate && $wedstrijdId != null) {
                    $result[] = $wedstrijd;
                }
            }
        }

        return $result;
    }

    private function MailScheidsrechter($scheidsrechter, $wedstrijd)
    {
        $body = file_get_contents("./UseCases/Email/templates/scheidsrechterTemplate.txt");

        $datum = GetDutchDate($wedstrijd['timestamp']);
        $tijd = $wedstrijd['timestamp']->format('G:i');
        $naam = $scheidsrechter['naam'];
        $spelendeTeams = $wedstrijd['team1'] . " - " . $wedstrijd['team2'];
        $email = $scheidsrechter['email'];
        $title = "Fluiten " . $spelendeTeams;
        $userId = $scheidsrechter['userId'];

        $body = str_replace("{{naam}}", $naam, $body);
        $body = str_replace("{{datum}}", $datum, $body);
        $body = str_replace("{{tijd}}", $tijd, $body);
        $body = str_replace("{{userId}}", $userId, $body);
        $body = str_replace("{{teams}}", $spelendeTeams, $body);
        $body = str_replace("{{afzender}}", $this->scheidsco['naam'], $body);

        $this->mailGateway->SendMail($this->scheidsco['email'], $this->scheidsco['naam'], $email, $naam, $title, $body);
    }

    private function MailTeller($teller, $wedstrijd)
    {
        $body = file_get_contents("./UseCases/Email/templates/tellerTemplate.txt");

        $datum = GetDutchDate($wedstrijd['timestamp']);
        $tijd = $wedstrijd['timestamp']->format('G:i');
        $naam = $teller['naam'];
        $spelendeTeams = $wedstrijd['team1'] . " - " . $wedstrijd['team2'];
        $email = $teller['email'];
        $title = "Tellen " . $spelendeTeams;
        $userId = $teller['userId'];

        $body = str_replace("{{naam}}", $naam, $body);
        $body = str_replace("{{datum}}", $datum, $body);
        $body = str_replace("{{tijd}}", $tijd, $body);
        $body = str_replace("{{userId}}", $userId, $body);
        $body = str_replace("{{teams}}", $spelendeTeams, $body);
        $body = str_replace("{{afzender}}", $this->scheidsco['naam'], $body);

        $this->mailGateway->SendMail($this->scheidsco['email'], $this->scheidsco['naam'], $email, $naam, $title, $body);
    }

    private function MailZaalwachter($zaalwachter)
    {
        $body = file_get_contents("./UseCases/Email/templates/zaalwachtTemplate.txt");

        $email = $zaalwachter['email'];
        $naam = $zaalwachter['naam'];
        $date = $zaalwachter['date'];
        if (!IsDateValid($date)) {
            return;
        }
        $datum = GetDutchDateLong(new DateTime());
        $title = "Zaalwacht " . $datum;

        $body = str_replace("{{naam}}", $naam, $body);
        $body = str_replace("{{datum}}", $datum, $body);
        $body = str_replace("{{afzender}}", $this->scheidsco['naam'], $body);

        $this->mailGateway->SendMail($this->scheidsco['email'], $this->scheidsco['naam'], $email, $naam, $title, $body);
    }

    private function MailSamenvatting($wedstrijden, $scheidsrechters, $tellers, $zaalwachters)
    {
        $body = file_get_contents("./UseCases/Email/templates/samenvattingTemplate.txt");

        $scheidsrechtersContent = count($scheidsrechters) == 0 ? "Geen scheidsrechters" : "";
        $tellersContent = count($tellers) == 0 ? "Geen tellers" : "";
        $zaalwachtersContent = count($zaalwachters) == 0 ? "Geen zaalwacht" : "";
        foreach ($wedstrijden as $wedstrijd) {
            $wedstrijdScheidsrechter = $this->GetScheidsrechterFromList($scheidsrechters, $wedstrijd);
            if ($wedstrijdScheidsrechter != null) {
                $scheidsrechtersContent .= $wedstrijdScheidsrechter['naam'] . " (" . $wedstrijdScheidsrechter['email'] . ")<br>";
            }

            $wedstrijdTellers = $this->GetTellersFromList($tellers, $wedstrijd);
            if (count($wedstrijdTellers) > 0) {
                $tellersContent .= "<b>" . $wedstrijdTellers[0]['tellers'] . "</b><br>";
            }
            foreach ($wedstrijdTellers as $teller) {
                $tellersContent .= $teller['naam'] . " (" . $teller['email'] . ")<br>";
            }
        }

        if (count($zaalwachters) > 0) {
            $zaalwachtersContent .= "<b>" . $zaalwachters[0]['zaalwacht'] . "</b><br>";
        }
        foreach ($zaalwachters as $zaalwachter) {
            $zaalwachtersContent .= $zaalwachter['naam'] . " (" . $zaalwachter['email'] . ")<br>";
        }

        $body = str_replace("{{scheidsco}}", $this->scheidsco['naam'], $body);
        $body = str_replace("{{scheidsrechters}}", $scheidsrechtersContent, $body);
        $body = str_replace("{{tellers}}", $tellersContent, $body);
        $body = str_replace("{{zaalwachters}}", $zaalwachtersContent, $body);

        $title = "Samenvatting fluit/tel/zaalwacht mails " . date("j-M-Y");

        $this->mailGateway->SendMail(
            $this->scheidsco['email'],
            $this->scheidsco['naam'],
            $this->scheidsco['email'],
            $this->scheidsco['naam'],
            $title,
            $body);

        $this->mailGateway->SendMail(
            $this->scheidsco['email'],
            $this->scheidsco['naam'],
            "jonathan.neuteboom@gmail.com",
            "Jonathan Neuteboom",
            $title,
            $body);
    }
}
