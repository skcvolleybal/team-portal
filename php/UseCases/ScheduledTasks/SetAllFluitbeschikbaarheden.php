<?php

class SetAllFluitbeschikbaarheden implements Interactor
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

    public function Execute(object $data = null)
    {
        $scheidsrechters = $this->telFluitGateway->GetScheidsrechters();
        $numberOfAddedBeschikbaarheden = 0;
        foreach ($scheidsrechters as $scheidsrechter) {
            $scheidsrechterId = $scheidsrechter->id;
            $team = $this->joomlaGateway->GetTeam($scheidsrechterId);
            $coachteam = $this->joomlaGateway->GetCoachTeam($scheidsrechterId);
            $fluitBeschikbaarheden = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheden($scheidsrechterId);

            $programma = $this->nevoboGateway->GetWedstrijdenForTeam($team);
            $coachProgramma = [];
            if ($coachteam) {
                $coachProgramma = $this->nevoboGateway->GetWedstrijdenForTeam($coachteam);
            }

            $skcProgramma = $this->nevoboGateway->GetProgrammaForSporthal();

            $rooster = $this->fluitBeschikbaarheidHelper->GetUscRooster($skcProgramma, $team, $coachteam);
            foreach ($rooster as &$wedstrijdDag) {
                $date = $wedstrijdDag->date;
                $speelWedstrijd = $this->fluitBeschikbaarheidHelper->GetWedstrijdWithDate($programma, $date);
                $coachWedstrijd = $this->fluitBeschikbaarheidHelper->GetWedstrijdWithDate($coachProgramma, $date);
                $eigenWedstrijden = array_filter([$speelWedstrijd, $coachWedstrijd], function ($value) {
                    return $value !== null;
                });

                foreach ($wedstrijdDag->speeltijden as $speeltijd) {
                    $date = $wedstrijdDag->date;
                    $time = $speeltijd->time;

                    $fluitBeschikbaarheid = $this->fluitBeschikbaarheidHelper->GetFluitBeschikbaarheid($fluitBeschikbaarheden, $date, $time);
                    if ($fluitBeschikbaarheid === null) {
                        $fluitBeschikbaarheid = $this->fluitBeschikbaarheidHelper->isMogelijk($eigenWedstrijden, $time);
                        $this->fluitBeschikbaarheidGateway->Insert($scheidsrechterId, $date, $time, $fluitBeschikbaarheid);
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
