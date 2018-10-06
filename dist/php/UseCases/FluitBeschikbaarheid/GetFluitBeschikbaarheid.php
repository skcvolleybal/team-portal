<?php
include 'IInteractor.php';
include 'NevoboGateway.php';
include 'UserGateway.php';
include 'FluitBeschikbaarheid.php';

class GetFluitBeschikbaarheid implements IInteractor
{
    private $userGateway;
    private $nevoboGateway;

    private $uscCode = 'LDNUN';

    private $team;
    private $coachTeam;
    private $fluitBeschikbaarheid;

    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheid($database);
    }

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        $this->team = $this->userGateway->GetTeam($userId);
        $this->coachTeam = $this->userGateway->GetCoachTeam($userId);
        $this->fluitBeschikbaarheid = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheid($userId);

        $programma = $this->nevoboGateway->GetProgrammaForTeam($this->team);
        if ($this->coachTeam != null) {
            $coachProgramma = $this->nevoboGateway->GetProgrammaForTeam($this->coachTeam);
        }

        $skcProgramma = $this->nevoboGateway->GetProgrammaForSporthal($this->uscCode);

        $rooster = $this->GetUscRooster($skcProgramma);

        foreach ($rooster as &$wedstrijdDag) {
            foreach ($wedstrijdDag['speeltijden'] as $tijdslot) {
                $fluitBeschikbaarheid = $this->GetFluitBeschikbaarheid($wedstrijdDag['date'], $tijdslot['time']);
                for ($i = 0; $i < count($wedstrijdDag['speeltijden']); $i++) {
                    if ($wedstrijdDag['speeltijden'][$i]['time'] == $tijdslot['time']) {
                        $wedstrijdDag['speeltijden'][$i]['beschikbaarheid'] = $fluitBeschikbaarheid;
                        break;
                    }
                }

            }
            $datum = $wedstrijdDag['datum'];
            $speelWedstrijd = $this->GetWedstrijdWithDatumAndTijd($programma, $datum);
            $coachWedstrijd = $this->GetWedstrijdWithDatumAndTijd($coachProgramma, $datum);
            if ($speelWedstrijd !== null) {
                $wedstrijdDag['eigenWedstrijden'][] = $this->MapToEigenWedstrijd($speelWedstrijd, $wedstrijdDag['speeltijden']);
            }
            if ($coachWedstrijd !== null) {
                $wedstrijdDag['eigenWedstrijden'][] = $this->MapToEigenWedstrijd($coachWedstrijd, $wedstrijdDag['speeltijden']);
            }
        }

        exit(json_encode($rooster));
    }

    private function GetWedstrijdWithDatumAndTijd($programma, $datum)
    {
        foreach ($programma as $wedstrijd) {
            $wedstrijdDatum = $wedstrijd['timestamp']->format('j F Y');
            if ($wedstrijdDatum == $datum) {
                return $wedstrijd;
            }
        }
        return null;
    }

    private function MapToEigenWedstrijd($wedstrijd, $speeltijden)
    {
        return [
            "datum" => $wedstrijd['timestamp']->format('j F Y'),
            "tijd" => $wedstrijd['timestamp']->format('G:i'),
            "team1" => $wedstrijd['team1'],
            "isTeam1" => $wedstrijd['team1'] == $this->team,
            "isCoachTeam1" => $wedstrijd['team1'] == $this->coachTeam,
            "team2" => $wedstrijd['team2'],
            "isTeam2" => $wedstrijd['team2'] == $this->team,
            "isCoachTeam2" => $wedstrijd['team2'] == $this->coachTeam,
            "locatie" => GetShortLocatie($wedstrijd['locatie']),
            "isThuis" => IsThuis($wedstrijd['locatie']),
            "isMogelijk" => $this->isMogelijk($wedstrijd, $speeltijden),
        ];
    }

    private function isMogelijk($wedstrijd, $speeltijden)
    {
        $bestResult = false;
        foreach ($speeltijden as $speeltijd) {
            $format = 'Y-m-d H:i';
            $timestring = $wedstrijd['timestamp']->format('Y-m-d') . " " . $speeltijd["tijd"];
            $fluitWedstrijd = [
                "timestamp" => $date = DateTime::createFromFormat($format, $timestring),
                "locatie" => "Universitair SC",
            ];
            $isMogelijk = isMogelijk($wedstrijd, $fluitWedstrijd);
            if ($isMogelijk) {
                return true;
            }
            $bestResult = $isMogelijk === false ? $bestResult : null;
        }
        return $bestResult;
    }

    private function GetFluitBeschikbaarheid($datum, $tijd)
    {
        foreach ($this->fluitBeschikbaarheid as $fluitBeschikbaarheid) {
            if ($fluitBeschikbaarheid['datum'] == $datum && $fluitBeschikbaarheid['tijd'] == $tijd) {
                return $fluitBeschikbaarheid['beschikbaarheid'];
            }
        }
        return "Misschien";
    }

    private function GetUscRooster($skcProgramma)
    {
        $rooster = [];
        foreach ($skcProgramma as $wedstrijd) {
            $datum = $wedstrijd['timestamp']->format("j F Y");
            $date = $wedstrijd['timestamp']->format("Y-m-d");
            $tijd = $wedstrijd['timestamp']->format("G:i");
            $time = $wedstrijd['timestamp']->format("G:i:s");
            $team1 = $wedstrijd['team1'];
            $team2 = $wedstrijd['team2'];

            $i = $this->GetIndexOfDatum($rooster, $datum);
            if ($i === null) {
                $rooster[] = [
                    "datum" => $datum,
                    "date" => $wedstrijd['timestamp']->format("Y-m-d"),
                    "speeltijden" => [],
                ];
                $i = count($rooster) - 1;
            }
            $j = $this->GetIndexOfTijd($rooster[$i]['speeltijden'], $tijd);
            if ($j === null) {
                $rooster[$i]['speeltijden'][] = [
                    "tijd" => $tijd,
                    "time" => $time,
                    "wedstrijden" => [],
                ];
                $j = count($rooster[$i]['speeltijden']) - 1;
            }

            $rooster[$i]['speeltijden'][$j]['wedstrijden'][] = [
                "team1" => $team1,
                "isTeam1" => $this->team == $team1,
                "isCoachTeam1" => $this->coachTeam == $team1,
                "team2" => $team2,
                "isTeam2" => $this->team == $team2,
                "isCoachTeam2" => $this->coachTeam == $team2,
            ];
        }

        return $rooster;
    }

    private function GetIndexOfDatum($rooster, $datum)
    {
        for ($i = count($rooster) - 1; $i >= 0; $i--) {
            if ($rooster[$i]['datum'] == $datum) {
                return $i;
            }
        }
        return null;
    }

    private function GetIndexOfTijd($roosterDag, $tijd)
    {
        for ($i = count($roosterDag) - 1; $i >= 0; $i--) {
            if ($roosterDag[$i]['tijd'] == $tijd) {
                return $i;
            }
        }
        return null;
    }
}
