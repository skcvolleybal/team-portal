<?php

use Kigkonsult\Icalcreator\Vcalendar;

class GetCalendar implements Interactor
{
    public function __construct(
        ZaalwachtGateway $zaalwachtGateway,
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway,
        TelFluitGateway $telFluitGateway,
        BarcieGateway $barcieGateway
    ) {
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data = null)
    {
        if (!$data->userid) {
            throw new InvalidArgumentException("userid is not set");
        }

        $user = $this->joomlaGateway->GetUser($data->userid);
        if ($user === null) {
            return null;
        }

        $isScheidsrechter = $this->joomlaGateway->IsScheidsrechter($user);
        $isTeller = !$isScheidsrechter;

        $title = $this->GetTitle($user, $isScheidsrechter);
        $calendar = $this->CreateCalendar($user, $title);
        $uscLocatie = "Universitair SC, Einsteinweg 6, 2333CC LEIDEN";

        $allBardiensten = $this->barcieGateway->GetBardienstenForUser($user);
        $telbeurten = $isTeller ? $this->telFluitGateway->GetTelbeurten($user) : [];
        $fluitbeurten = $isScheidsrechter ? $this->telFluitGateway->GetFluitbeurten($user) : [];

        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 355);
        foreach ($wedstrijddagen as $wedstrijddag) {
            $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($wedstrijddag->date);
            if ($zaalwacht && $zaalwacht->team->Equals($user->team)) {
                $firstMatch = $wedstrijddag->speeltijden[0]->wedstrijden[0];
                $i = count($wedstrijddag->speeltijden) - 1;
                $lastMatch = $wedstrijddag->speeltijden[$i]->wedstrijden[0];
                $start = $firstMatch->timestamp;
                $end = DateFunctions::AddMinutes($lastMatch->timestamp, 120);
                $this->AddEvent($calendar, $start, $end, $uscLocatie, "Zaalwacht");
            }

            $bardiensten = $this->GetBardienstenForDate($allBardiensten, $wedstrijddag->date);
            foreach ($bardiensten as $bardienst) {
                $start = $bardienst->GetStartTime();
                $end = DateFunctions::AddMinutes($start, 4 * 60); // 4uur?
                $this->AddEvent($calendar, $start, $end, $uscLocatie, "Bardienst");
            }

            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    if ($isTeller) {
                        $telWedstrijd = Wedstrijd::GetWedstrijdWithMatchId($telbeurten, $wedstrijd->matchId);
                        if ($telWedstrijd) {
                            $telWedstrijd->AppendInformation($wedstrijd);
                            $start = $telWedstrijd->timestamp;
                            $end = DateFunctions::AddMinutes($start, 120);
                            $teams = $telWedstrijd->team1 . ' ' . $telWedstrijd->team2;
                            $this->AddEvent($calendar, $start, $end, $uscLocatie, "Tellen", "Tellen: $teams");
                        }
                    }

                    if ($isScheidsrechter) {
                        $fluitWedstrijd = Wedstrijd::GetWedstrijdWithMatchId($fluitbeurten, $wedstrijd->matchId);
                        if ($fluitWedstrijd) {
                            $fluitWedstrijd->AppendInformation($wedstrijd);
                            $start = $fluitWedstrijd->timestamp;
                            $end = DateFunctions::AddMinutes($start, 120);
                            $teams = $fluitWedstrijd->team1->naam . ' ' . $fluitWedstrijd->team2->naam;
                            $this->AddEvent($calendar, $start, $end, $uscLocatie, "Tellen", $teams);
                        }
                    }
                }
            }
        }
        echo $calendar->createCalendar();
    }

    private function GetBardienstenForDate(array $allBardiensten, DateTime $date): array
    {
        $result = [];
        foreach ($allBardiensten as $bardienst) {
            if (DateFunctions::AreDatesEqual($bardienst->bardag->date, $date)) {
                $result[] = $bardienst;
            }
        }
        return $result;
    }

    private function GetTitle(Persoon $persoon, bool $isScheidsrechter): string
    {
        $seizoen = GetCurrentSeizoen();
        $title = null;
        if ($persoon->team === null) {
            if ($isScheidsrechter) {
                $title = "Jouw SKC-fluitrooster, seizoen $seizoen";
            }
        } else {
            $team = $persoon->team->GetSkcNaam();
            if ($isScheidsrechter) {
                $title = "Fluit- en zaalwachtrooster van $team, seizoen $seizoen";
            } else {
                $title = "Tel- en zaalwachtrooster van $team, seizoen $seizoen";
            }
        }

        return $title;
    }

    private function CreateCalendar(Persoon $persoon, string $title): Vcalendar
    {
        $postfix = $persoon->team ? $persoon->team->GetSkcNaam() : "jou";
        return Vcalendar::factory([Vcalendar::UNIQUE_ID => "https://www.skcvolleybal.nl/team-portal/"])
            ->setMethod(Vcalendar::PUBLISH)
            ->setXprop(Vcalendar::X_WR_CALNAME, $title)
            ->setXprop(Vcalendar::X_WR_CALDESC, "Alle Fluit-, telwedstrijden of zaalwachtendiensten van $postfix")
            ->setXprop(Vcalendar::X_WR_RELCALID, "")
            ->setXprop(Vcalendar::X_WR_TIMEZONE, "Europe/Amsterdam");
    }

    private function AddEvent(Vcalendar $calendar, DateTime $start, DateTime $end, string $location, string $summary, string $description = null)
    {
        if ($start && $end) {
            $event = $calendar->newVevent()
                ->setDtstart($start)
                ->setDtend($end)
                ->setLocation($location)
                ->setSummary($summary);
            if ($description) {
                $event->setDescription($description);
            }
        }
    }
}
