<?php

use Kigkonsult\Icalcreator\Vcalendar;


class GetCalendar implements Interactor
{
    public function __construct(
        ZaalwachtGateway $zaalwachtGateway,
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway,
        TelFluitGateway $telFluitGateway
    ) {
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
    }

    public function Execute(object $data = null)
    {
        $withFluiten = $data->fluiten;
        $withTellen = $data->tellen;

        if (!$data->userid) {
            throw new InvalidArgumentException("userid is not set");
        }

        $user = $this->JoomlaGateway->GetUser($data->userid);
        if ($user === null || $user->team === null) {
            return null;
        }

        $this->uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();

        $skcTeam = $user->team->GetSkcNotatie() ?? "je team";
        $title = $this->GetTitle($skcTeam, $withFluiten, $withTellen);

        $this->CreateCalendar($title);

        $uscLocatie = "Universitair SC, Einsteinweg 6, 2333CC LEIDEN";
        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtenOfUser($user);
        foreach ($zaalwachten as $zaalwacht) {
            [$start, $end] = $this->GetStartAndEndDateOfZaalwacht($zaalwacht);
            $this->AddEvent($start, $end, $uscLocatie, "Zaalwacht");
        }

        if ($withTellen !== null) {
            $telbeurten = $this->telFluitGateway->GetTelbeurten($user);
            foreach ($telbeurten as $telbeurt) {
                $telWedstrijd = $this->GetMatchWithId($telbeurt->id);
                if ($telWedstrijd) {
                    $start = $telWedstrijd->timestamp;
                    $end = (clone $start)->add(new DateInterval('PT2H'));
                    $teams = $telWedstrijd->team1 . ' ' . $telWedstrijd->team2;
                    $this->AddEvent($start, $end, $uscLocatie, "Tellen", $teams);
                }
            }
        }

        if ($withFluiten !== null) {
            $fluitbeurten = $this->telFluitGateway->GetFluitbeurten($user);
            foreach ($fluitbeurten as $fluitbeurt) {
                $fluitWedstrijd = $this->GetMatchWithId($fluitbeurt->id);
                if ($fluitWedstrijd) {
                    $start = $fluitWedstrijd->timestamp;
                    $end = (clone $start)->add(new DateInterval('PT2H'));
                    $teams = $fluitWedstrijd->team1 . ' ' . $fluitWedstrijd->team2;
                    $this->AddEvent($start, $end, $uscLocatie, "Tellen", $teams);
                }
            }
        }

        return $this->calendar->createCalendar();
    }

    private function GetTitle($skcTeam, $withFluiten, $withTellen)
    {
        if ($withTellen !== null && $withFluiten !== null) {
            $title = "Fluit-, tel- en zaalwachtrooster van $skcTeam";
        } else if ($withTellen !== null) {
            $title = "Tel- en zaalwachtrooster van $skcTeam";
        } else {
            $title = "Fluit- en zaalwachtrooster van $skcTeam";
        }

        return $title;
    }

    private function GetMatchWithId($matchId)
    {
        foreach ($this->uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->matchId == $matchId) {
                return $wedstrijd;
            }
        }
        return null;
    }

    private function GetStartAndEndDateOfZaalwacht($zaalwacht)
    {
        $wedstrijden = [];
        foreach ($this->uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp && $wedstrijd->timestamp->format('Y-m-d') == $zaalwacht->date) {
                $wedstrijden[] = $wedstrijd;
            }
        }
        if (count($wedstrijden) == 0) {
            throw new UnexpectedValueException();
        }

        $firstWedstrijd = $wedstrijden[0];
        $lastWedstrijd = $wedstrijden[0];
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp < $firstWedstrijd->timestamp) {
                $firstWedstrijd = $wedstrijd;
            }
            if ($lastWedstrijd->timestamp < $wedstrijd->timestamp) {
                $lastWedstrijd = $wedstrijd;
            }
        }
        return [
            $firstWedstrijd->timestamp,
            $lastWedstrijd->timestamp->add(new DateInterval('PT2H'))
        ];
    }

    private function CreateCalendar($title)
    {
        $timezone = "Europe/Amsterdam";
        $config = array(
            "UNIQUE_ID" => "https://www.skcvolleybal.nl/team-portal/",
            "TZID" => $timezone,
        );
        $this->calendar = new Vcalendar($config);
        $this->calendar->setProperty(\kigkonsult\iCalcreator\util\util::$METHOD, "PUBLISH");
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
