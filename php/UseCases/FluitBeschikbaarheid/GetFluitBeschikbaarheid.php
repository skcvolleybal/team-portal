<?php
include_once 'IInteractor.php';
include_once 'NevoboGateway.php';
include_once 'JoomlaGateway.php';
include_once 'FluitBeschikbaarheidGateway.php';
include_once 'shared/FluitBeschikbaarheidHelper.php';

class GetFluitBeschikbaarheid implements IInteractor
{
    private $uscCode = 'LDNUN';

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheidGateway($database);
        $this->fluitBeschikbaarheidHelper = new FluitBeschikbaarheidHelper();
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsScheidsrechter($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen scheidsrechter");
        }

        $team = $this->joomlaGateway->GetTeam($userId);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($userId);
        $fluitBeschikbaarheden = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheden($userId);

        $programma = $this->nevoboGateway->GetProgrammaForTeam($team);
        $coachProgramma = [];
        if ($coachTeam) {
            $coachProgramma = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);
        }

        $skcProgramma = $this->nevoboGateway->GetProgrammaForSporthal($this->uscCode);
        $skcProgramma = RemoveMatchesWithoutData($skcProgramma);

        $rooster = $this->fluitBeschikbaarheidHelper->GetUscRooster($skcProgramma, $team, $coachTeam);

        foreach ($rooster as &$wedstrijdDag) {
            $date = $wedstrijdDag->date;
            $speelWedstrijd = $this->fluitBeschikbaarheidHelper->GetWedstrijdWithDate($programma, $date);
            $coachWedstrijd = $this->fluitBeschikbaarheidHelper->GetWedstrijdWithDate($coachProgramma, $date);
            $eigenWedstrijden = array_filter([$speelWedstrijd, $coachWedstrijd], function ($value) {
                return $value !== null;
            });

            foreach ($wedstrijdDag->speeltijden as $tijdslot) {
                $date = $wedstrijdDag->date;
                $time = $tijdslot->time;
                $i = $this->fluitBeschikbaarheidHelper->GetIndexOfTijd($wedstrijdDag->speeltijden, $time);

                $wedstrijdDag->speeltijden[$i]->beschikbaarheid = $this->fluitBeschikbaarheidHelper->GetFluitBeschikbaarheid($fluitBeschikbaarheden, $date, $time) ?? "Onbekend";
                $wedstrijdDag->speeltijden[$i]->isMogelijk = $this->fluitBeschikbaarheidHelper->isMogelijk($eigenWedstrijden, $time);
            }

            foreach ($eigenWedstrijden as $wedstrijd) {
                $wedstrijdDag->eigenWedstrijden[] = $this->MapToEigenWedstrijd($wedstrijd, $team, $coachTeam);
            }
        }

        exit(json_encode($rooster));
    }

    private function MapToEigenWedstrijd($wedstrijd, $team, $coachTeam)
    {
        return (object) [
            "datum" => GetDutchDate($wedstrijd->timestamp),
            "tijd" => $wedstrijd->timestamp->format('H:i'),
            "team1" => $wedstrijd->team1,
            "isTeam1" => $wedstrijd->team1 == $team,
            "isCoachTeam1" => $wedstrijd->team1 == $coachTeam,
            "team2" => $wedstrijd->team2,
            "isTeam2" => $wedstrijd->team2 == $team,
            "isCoachTeam2" => $wedstrijd->team2 == $coachTeam,
            "locatie" => GetShortLocatie($wedstrijd->locatie),
        ];
    }
}
