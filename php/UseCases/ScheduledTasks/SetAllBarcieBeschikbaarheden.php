<?php
include_once 'IInteractor.php';
include_once 'BarcieGateway.php';
include_once 'NevoboGateway.php';
include_once 'JoomlaGateway.php';
include_once 'shared' . DIRECTORY_SEPARATOR . 'BarcieBeschikbaarheidHelper.php';

class SetAllBarcieBeschikbaarheden implements IInteractor
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->barcieGateway = new BarcieGateway($database);
        $this->barcieBeschikbaarheidHelper = new BarcieBeschikbaarheidHelper();
    }

    public function Execute()
    {
        $barcieleden = $this->barcieGateway->GetBarcieleden();
        $numberOfAddedBeschikbaarheden = 0;
        $barcieDagen = $this->barcieGateway->GetBarcieDagen();

        foreach ($barcieleden as $barcielid) {
            $barcielidId = $barcielid->id;
            $team = $this->joomlaGateway->GetTeam($barcielidId);
            $coachTeam = $this->joomlaGateway->GetCoachTeam($barcielidId);
            $eigenWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
            $coachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);
            $beschikbaarheden = $this->barcieGateway->GetBeschikbaarheden($barcielidId);
            foreach ($barcieDagen as $barcieDag) {
                $date = $barcieDag->date;
                $beschikbaarheid = $this->GetBeschikbaarheid($beschikbaarheden, $date);
                if ($beschikbaarheid === null) {
                    $eigenWedstrijd = $this->GetWedstrijdWithDate($eigenWedstrijden, $date);
                    $coachWedstrijd = $this->GetWedstrijdWithDate($coachWedstrijden, $date);

                    $wedstrijden = array_filter([$eigenWedstrijd, $coachWedstrijd], function ($value) {
                        return $value !== null;
                    });

                    $beschikbaarheid = $this->barcieBeschikbaarheidHelper->isMogelijk($wedstrijden);
                    $dayId = $this->barcieGateway->GetDateId($date);
                    if ($dayId !== null && $beschikbaarheid != "Onbekend") {
                        $this->barcieGateway->InsertBeschikbaarheid($barcielidId, $dayId, $beschikbaarheid);
                        $numberOfAddedBeschikbaarheden++;
                    }
                }
            }
        }

        return (object) [
            "numberOfAddedBeschikbaarheden" => $numberOfAddedBeschikbaarheden,
        ];
    }

    private function GetWedstrijdWithDate($wedstrijden, $date)
    {
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp && $wedstrijd->timestamp->format("Y-m-d") == $date) {
                return $wedstrijd;
            }
        }
        return null;
    }

    private function GetBeschikbaarheid($beschikbaarheden, $date)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $date) {
                return $beschikbaarheid->is_beschikbaar;
            }
        }
        return null;
    }
}
