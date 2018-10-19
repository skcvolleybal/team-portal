<?php
include_once 'IInteractor.php';
require_once 'iCalcreator/autoload.php';
include_once 'ZaalwachtGateway.php';
include_once 'NevoboGateway.php';
include_once 'TelFluitGateway.php';

class GetCalendar implements IInteractor
{
    public function __construct($database)
    {
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->telFluitGateway = new TelFluitGateway($database);
    }

    public function Execute()
    {
        $userId = GetQueryStringParamater('userid');

        $withFluiten = GetQueryStringParamater('fluiten');
        $withTellen = GetQueryStringParamater('tellen');

        if (!$userId) {
            InternalServerError("userid is not set");
        }

        if (!$this->joomlaGateway->DoesUserIdExist($userId)) {
            InternalServerError();
        }

        $team = $this->joomlaGateway->GetTeam($userId);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($userId);
        $uscLocatie = "Universitair SC, Einsteinweg 6, 2333CC LEIDEN";
        $this->uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');

        $skcTeam = ToSkcName($team) ?? "je team";
        if ($withTellen !== null && $withFluiten !== null) {
            $title = "Fluit-, tel- en zaalwachtrooster van $skcTeam";
        } else if ($withTellen !== null) {
            $title = "Tel- en zaalwachtrooster van $skcTeam";
        } else {
            $title = "Fluit- en zaalwachtrooster van $skcTeam";
        }

        $this->CreateCalendar($title);

        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtForUserId($userId);
        foreach ($zaalwachten as $zaalwacht) {
            [$start, $end] = $this->GetStartAndEndDateOfZaalwacht($zaalwacht);
            $this->AddEvent($start, $end, $uscLocatie, "Zaalwacht");
        }

        if ($withTellen !== null) {
            $telbeurten = $this->telFluitGateway->GetTelbeurten($userId);
            foreach ($telbeurten as $telbeurt) {
                $telWedstrijd = $this->GetMatchWithId($telbeurt['matchId']);
                if ($telWedstrijd) {
                    $start = $telWedstrijd['timestamp'];
                    $end = (clone $start)->add(new DateInterval('PT2H'));
                    $teams = $telWedstrijd['team1'] . ' ' . $telWedstrijd['team2'];
                    $this->AddEvent($start, $end, $uscLocatie, "Tellen", $teams);
                }
            }
        }

        if ($withFluiten !== null) {
            $fluitbeurten = $this->telFluitGateway->GetFluitbeurten($userId);
            foreach ($fluitbeurten as $fluitbeurt) {
                $fluitWedstrijd = $this->GetMatchWithId($fluitbeurt['matchId']);
                if ($fluitWedstrijd) {
                    $start = $fluitWedstrijd['timestamp'];
                    $end = (clone $start)->add(new DateInterval('PT2H'));
                    $teams = $fluitWedstrijd['team1'] . ' ' . $fluitWedstrijd['team2'];
                    $this->AddEvent($start, $end, $uscLocatie, "Tellen", $teams);
                }
            }
        }

        exit($this->calendar->createCalendar());
    }

    private function GetMatchWithId($matchId)
    {
        foreach ($this->uscWedstrijden as $wedstrijd) {
            if ($wedstrijd['id'] == $matchId) {
                return $wedstrijd;
            }
        }
        return null;
    }

    private function GetStartAndEndDateOfZaalwacht($zaalwacht)
    {
        $wedstrijden = [];
        foreach ($this->uscWedstrijden as $wedstrijd) {
            if ($wedstrijd['timestamp'] && $wedstrijd['timestamp']->format('Y-m-d') == $zaalwacht['date']) {
                $wedstrijden[] = $wedstrijd;
            }
        }
        if (count($wedstrijden) == 0) {
            InternalServerError();
        }

        $firstWedstrijd = $wedstrijden[0];
        $lastWedstrijd = $wedstrijden[0];
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd['timestamp'] < $firstWedstrijd['timestamp']) {
                $firstWedstrijd = $wedstrijd;
            }
            if ($lastWedstrijd['timestamp'] < $wedstrijd['timestamp']) {
                $lastWedstrijd = $wedstrijd;
            }
        }
        return [$firstWedstrijd['timestamp'], $lastWedstrijd['timestamp']->add(new DateInterval('PT2H'))];
    }

    private function CreateCalendar($title)
    {
        $timezone = "Europe/Amsterdam";
        $config = array(
            "UNIQUE_ID" => "https://www.skcvolleybal.nl/team-portal/",
            "TZID" => $timezone,
        );
        $this->calendar = new kigkonsult\iCalcreator\vcalendar($config);
        $this->calendar->setProperty(kigkonsult\iCalcreator\util\util::$METHOD, "PUBLISH");
        $this->calendar->setProperty("x-wr-calname", $title);
        $this->calendar->setProperty("X-WR-CALDESC", "Alle Fluit-, telwedstrijden of zaalwachtendiensten van jouw team");
        $this->calendar->setProperty("X-WR-TIMEZONE", $timezone);
    }

    private function AddEvent(DateTime $start, DateTime $end, $location, $summary, $description = null)
    {
        if ($start && $end) {
            $event = $this->calendar->newVevent();
            $event->setProperty(kigkonsult\iCalcreator\util\util::$DTSTART, $this->GetDateArray($start));
            $event->setProperty(kigkonsult\iCalcreator\util\util::$DTEND, $this->GetDateArray($end));
            $event->setProperty(kigkonsult\iCalcreator\util\util::$LOCATION, $location);
            $event->setProperty(kigkonsult\iCalcreator\util\util::$SUMMARY, $summary);
            if ($description) {
                $event->setProperty(kigkonsult\iCalcreator\util\util::$DESCRIPTION, $description);
            }
        }
    }

    private function GetDateArray($date)
    {
        return array(
            "year" => $date->format('Y'),
            "month" => $date->format('n'),
            "day" => $date->format('j'),
            "hour" => $date->format('G'),
            "min" => $date->format('i'),
            "sec" => 0,
        );
    }
}
