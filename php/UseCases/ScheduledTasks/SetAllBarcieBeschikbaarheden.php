<?php

class SetAllBarcieBeschikbaarheden implements Interactor
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->barcieGateway = new BarcieGateway($database);
        $this->barcieBeschikbaarheidHelper = new BarcieBeschikbaarheidHelper();
    }

    public function Execute(object $data = null)
    {
        $barleden = $this->barcieGateway->GetBarleden();
        $numberOfAddedBeschikbaarheden = 0;
        $bardagen = $this->barcieGateway->GetBardagen();

        foreach ($barleden as $barlid) {
            $barlidId = $barlid->id;
            $team = $this->joomlaGateway->GetTeam($barlidId);
            $coachteam = $this->joomlaGateway->GetCoachTeam($barlidId);
            $eigenWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
            $coachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($coachteam);
            $beschikbaarheden = $this->barcieGateway->GetBeschikbaarheden($barlidId);
            foreach ($bardagen as $bardag) {
                $date = $bardag->date;
                $beschikbaarheid = $this->GetBeschikbaarheid($beschikbaarheden, $date);
                if ($beschikbaarheid === null) {
                    $eigenWedstrijd = $this->GetWedstrijdWithDate($eigenWedstrijden, $date);
                    $coachWedstrijd = $this->GetWedstrijdWithDate($coachWedstrijden, $date);

                    $wedstrijden = array_filter([$eigenWedstrijd, $coachWedstrijd], function ($value) {
                        return $value !== null;
                    });

                    $beschikbaarheid = $this->barcieBeschikbaarheidHelper->isMogelijk($wedstrijden);
                    $bardag = $this->barcieGateway->GetBardag($date);
                    if ($dayId !== null && $beschikbaarheid != "Onbekend") {
                        $this->barcieGateway->InsertBeschikbaarheid($barlidId, $dayId, $beschikbaarheid);
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
