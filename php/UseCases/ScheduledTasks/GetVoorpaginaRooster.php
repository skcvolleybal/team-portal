<?php

class GetVoorpaginaRooster implements Interactor
{
    public function __construct($database)
    {
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
    }

    public function Execute(object $data = null)
    {
        $this->telFluitBeurten = $this->telFluitGateway->GetAllFluitEnTelbeurten();
        $this->zaalwachten = $this->zaalwachtGateway->GetZaalwachtIndeling();

        $result = [];
        $wedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        foreach ($wedstrijden as $counter => $wedstrijd) {
            $i = $this->GetDateWithWedstrijden($result, $wedstrijd);
            if ($i !== null) {
                $result[$i]->wedstrijden[] = $this->MapWedstrijd($wedstrijd);
            } else {
                $date = $wedstrijd->timestamp->format('Y-m-d');
                $result[] = (object) [
                    "date" => $date,
                    "datum" => DateFunctions::GetDutchDate($wedstrijd->timestamp),
                    "zaalwacht" => $this->GetZaalwacht($date),
                    "wedstrijden" => [$this->MapWedstrijd($wedstrijd)],
                ];
            }

            if ($counter >= 20) {
                break;
            }
        }

        return $result;
    }

    private function MapWedstrijd($wedstrijd)
    {
        return (object) [
            "tijd" => $wedstrijd->timestamp->format('H:i'),
            "teams" => $wedstrijd->team1 . " - " . $wedstrijd->team2,
            "scheidsrechter" => $this->GetScheidsrechterForWedstrijd($wedstrijd->matchId),
            "tellers" => $this->GetTellersForWedstrijd($wedstrijd->matchId),
        ];
    }

    private function GetTellersForWedstrijd($matchId)
    {
        foreach ($this->telFluitBeurten as $telFluitBeurt) {
            if ($telFluitBeurt->matchId == $matchId) {
                return $telFluitBeurt->tellers;
            }
        }
        return null;
    }

    private function GetScheidsrechterForWedstrijd($matchId)
    {
        foreach ($this->telFluitBeurten as $telFluitBeurt) {
            if ($telFluitBeurt->matchId == $matchId) {
                return $telFluitBeurt->scheidsrechter;
            }
        }
        return null;
    }

    private function GetZaalwacht($date)
    {
        foreach ($this->zaalwachten as $zaalwacht) {
            if ($zaalwacht->date == $date) {
                return $zaalwacht->team;
            }
        }
        return null;
    }

    private function GetDateWithWedstrijden($result, $wedstrijd)
    {
        foreach ($result as $i => $dateWithWedstrijden) {
            if ($wedstrijd->timestamp && $dateWithWedstrijden->date == $wedstrijd->timestamp->format('Y-m-d')) {
                return $i;
            }
        }
        return null;
    }
}
