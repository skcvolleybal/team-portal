<?php
include_once 'IInteractor.php';
include_once 'BarcieGateway.php';
include_once 'NevoboGateway.php';
include_once 'JoomlaGateway.php';

class SetAllBarcieBeschikbaarheden implements IInteractor
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

        foreach ($barcieLeden as $barcielid) {
            $barcielidId = $barcielid['id'];
            $team = $this->joomlaGateway->GetTeam($barcielidId);
            $coachTeam = $this->joomlaGateway->GetCoachTeam($barcielidId);
            $eigenWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
            $coachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);
            $beschikbaarheden = $this->barcieGateway->GetBeschikbaarheden($barcielidId);
            foreach ($barcieDagen as $barcieDag) {
                $date = $barcieDag['date'];
                $beschikbaarheid = $this->GetBeschikbaarheid($beschikbaarheden, $date);
                if ($beschikbaarheid === null) {
                    $eigenWedstrijd = $this->GetWedstrijdWithDate($eigenWedstrijden, $date);
                    $coachWedstrijd = $this->GetWedstrijdWithDate($coachWedstrijden, $date);

                    $wedstrijden = array_filter([$eigenWedstrijd, $coachWedstrijd], function ($value) {return $value !== null;});

                    $beschikbaarheid = $this->isMogelijk($wedstrijden);
                    $dayId = $this->barcieGateway->GetDateId($date);
                    if ($dayId !== null) {
                        $this->barcieGateway->InsertBeschikbaarheid($barcielidId, $dayId, $beschikbaarheid);
                        $numberOfAddedBeschikbaarheden++;
                    }
                }
            }
        }

        return [
            "numberOfAddedBeschikbaarheden" => $numberOfAddedBeschikbaarheden,
        ];
    }

    private function GetWedstrijdWithDate($wedstrijden, $date)
    {
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd['timestamp'] && $wedstrijd['timestamp']->format("Y-m-d") == $date) {
                return $wedstrijd;
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

    private function IsMogelijk($wedstrijden)
    {
        if (count($wedstrijden) == 0) {
            return "Onbekend";
        }

        $bestResult = "Ja";
        foreach ($wedstrijden as $wedstrijd) {
            if (!IsThuis($wedstrijd['locatie'])) {
                return "Nee";
            }
            if ($wedstrijd['timestamp']) {
                $time = $wedstrijd['timestamp']->format('H:i');
                if ($time == "19:30" || $time == "16:00") {
                    $bestResult = $bestResult == "Ja" ? "Ja" : "Onbekend";
                } else {
                    $bestResult = "Onbekend";
                }
            }
        }

        return $bestResult;
    }
}
