<?php
include 'IInteractor.php';
include 'NevoboGateway.php';
include 'JoomlaGateway.php';
include 'FluitBeschikbaarheidGateway.php';

class GetFluitBeschikbaarheid implements IInteractor
{
    private $joomlaGateway;
    private $nevoboGateway;

    private $uscCode = 'LDNUN';

    private $team;
    private $coachTeam;
    private $fluitBeschikbaarheid;

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheid($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsScheidsrechter($userId)) {
            InternalServerError("Je bent (helaas) geen scheidsrechter");
        }

        $this->team = $this->joomlaGateway->GetTeam($userId);
        $this->coachTeam = $this->joomlaGateway->GetCoachTeam($userId);
        $this->fluitBeschikbaarheid = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheid($userId);

        $programma = $this->nevoboGateway->GetProgrammaForTeam($this->team);
        $coachProgramma = [];
        if ($this->coachTeam != null) {
            $coachProgramma = $this->nevoboGateway->GetProgrammaForTeam($this->coachTeam);
        }

        $skcProgramma = $this->nevoboGateway->GetProgrammaForSporthal($this->uscCode);
        $skcProgramma = RemoveMatchesWithoutData($skcProgramma);

        $rooster = $this->GetUscRooster($skcProgramma);

        foreach ($rooster as &$wedstrijdDag) {
            $datum = $wedstrijdDag['datum'];
            $speelWedstrijd = $this->GetWedstrijdWithDatumAndTijd($programma, $datum);
            $coachWedstrijd = $this->GetWedstrijdWithDatumAndTijd($coachProgramma, $datum);
            $eigenWedstrijden = array_filter([$speelWedstrijd, $coachWedstrijd], function ($value) {return $value !== null;});

            foreach ($wedstrijdDag['speeltijden'] as $tijdslot) {
                $fluitBeschikbaarheid = $this->GetFluitBeschikbaarheid($wedstrijdDag['date'], $tijdslot['time']);
                $i = $this->GetIndexOfTijd($wedstrijdDag['speeltijden'], $tijdslot['tijd']);

                $wedstrijdDag['speeltijden'][$i]['beschikbaarheid'] = $fluitBeschikbaarheid;

                $wedstrijdDag['speeltijden'][$i]['isMogelijk'] = $this->isMogelijk($eigenWedstrijden, $tijdslot['tijd']);
            }

            foreach ($eigenWedstrijden as $wedstrijd) {
                $wedstrijdDag['eigenWedstrijden'][] = $this->MapToEigenWedstrijd($wedstrijd);
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

    private function MapToEigenWedstrijd($wedstrijd)
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
        ];
    }

    private function isMogelijk($wedstrijden, $tijd)
    {
        $bestResult = true;
        foreach ($wedstrijden as $wedstrijd) {
            $format = 'Y-m-d H:i';
            $timestring = $wedstrijd['timestamp']->format('Y-m-d') . " " . $tijd;
            $fluitWedstrijd = [
                "timestamp" => $date = DateTime::createFromFormat($format, $timestring),
                "locatie" => "Universitair SC",
            ];
            $isMogelijk = isMogelijk($wedstrijd, $fluitWedstrijd);
            if (!$isMogelijk) {
                return false;
            }
            $bestResult = $isMogelijk === true ? $bestResult : null;
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
