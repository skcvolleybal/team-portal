<?php
include_once 'IInteractor.php';
include_once 'TelFluitGateway.php';
include_once 'NevoboGateway.php';
include_once 'FluitBeschikbaarheidGateway.php';
include_once 'FluitBeschikbaarheid/shared/FluitBeschikbaarheidHelper.php';

class SetAllFluitbeschikbaarheden implements IInteractor
{

    public function __construct($database)
    {
        $this->database = $database;
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->fluitBeschikbaarheidHelper = new FluitBeschikbaarheidHelper();
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheidGateway($database);
    }

    public function Execute()
    {
        $scheidsrechters = $this->telFluitGateway->GetScheidsrechters();
        $numberOfAddedBeschikbaarheden = 0;
        foreach ($scheidsrechters as $scheidsrechter) {
            $scheidsrechterId = $scheidsrechter->id;
            $team = $this->joomlaGateway->GetTeam($scheidsrechterId);
            $coachTeam = $this->joomlaGateway->GetCoachTeam($scheidsrechterId);
            $fluitBeschikbaarheden = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheden($scheidsrechterId);

            $programma = $this->nevoboGateway->GetProgrammaForTeam($team);
            $coachProgramma = [];
            if ($coachTeam) {
                $coachProgramma = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);
            }

            $skcProgramma = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');
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

                    $fluitBeschikbaarheid = $this->fluitBeschikbaarheidHelper->GetFluitBeschikbaarheid($fluitBeschikbaarheden, $date, $time);
                    if ($fluitBeschikbaarheid === null) {
                        $fluitBeschikbaarheid = $this->fluitBeschikbaarheidHelper->isMogelijk($eigenWedstrijden, $time);
                        $this->fluitBeschikbaarheidGateway->UpdateBeschikbaarheid($scheidsrechterId, $date, $time, $fluitBeschikbaarheid);
                        $numberOfAddedBeschikbaarheden++;
                    }
                }
            }
        }

        return (object) [
            "numberOfAddedBeschikbaarheden" => $numberOfAddedBeschikbaarheden,
        ];
    }
}
