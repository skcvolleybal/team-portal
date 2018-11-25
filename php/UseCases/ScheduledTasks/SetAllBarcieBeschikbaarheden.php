<?php
include_once 'IInteractor.php';
include_once 'TelFluitGateway.php';
include_once 'NevoboGateway.php';

class SetAllFluitbeschikbaarheden implements IInteractor
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->barcieGateway = new BarcieGateway($database);
    }

    public function Execute()
    {
        $barcieLeden = $this->barcieGateway->GetBarcieLeden();
        $numberOfAddedBeschikbaarheden = 0;
        $barcieDagen = $this->barcieGateway->GetBarcieDagen();

        foreach ($barcieLeden as $barcieLid) {
            $barcieLidId = $barcieLid['id'];
            $team = $this->joomlaGateway->GetTeam($barcieLidId);
            $coachTeam = $this->joomlaGateway->GetCoachTeam($barcieLidId);
            $eigenWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
            $coachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);
            $beschikbaarheden = $this->barcieGateway->GetBarcieBeschikbaarheid($barcieLidId);
            foreach ($barcieDagen as $barcieDag) {
                $date = $barcieDag['date'];
                $beschikbaarheid = $this->GetBeschikbaarheid($beschikbaarheden, $date);
                if ($beschikbaarheid === null) {
                    $eigenWedstrijd = GetWedstrijdWithDate($eigenWedstrijden, $date);
                    $coachWedstrijd = GetWedstrijdWithDate($coachWedstrijden, $date);

                    $wedstrijden = array_filter([$eigenWedstrijd, $coachWedstrijd], function ($value) {return $value !== null;});

                    $beschikbaarheid = $this->fluitBeschikbaarheidHelper->isMogelijk($wedstrijden);
                    $this->barcieGateway->UpdateBeschikbaarheid($barcieLidId, $date, $beschikbaarheid);
                }
            }
        }
    }

    private function GetWedstrijdWithDate($eigenWedstrijden, $date)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid['date'] == $date) {
                return $beschikbaarheid['beschikbaarheid'];
            }
        }
        return null;
    }

    private function GetBeschikbaarheid($beschikbaarheden, $date)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid['date'] == $date) {
                return $beschikbaarheid['beschikbaarheid'];
            }
        }
        return null;
    }

    private function isMogelijk($wedstrijden)
    {
        foreach ($wedstrijden as $wedstrijd) {
            if (!IsThuis($wedstrijd['locatie'])) {
                return "Nee";
            }
            if ($wedstrijd['timestamp'] && $wedstrijd['timestamp']->format('G:H:s') == "19:30:00") {
                return "Ja";
            }
        }

        return "Onbekend";
    }
}
