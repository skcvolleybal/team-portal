<?php

include_once 'IInteractor.php';
include_once 'NevoboGateway.php';
include_once 'BarcieGateway.php';
include_once 'GetNevoboMatchByDate.php';

class GetBarcieBeschikbaarheid extends GetNevoboMatchByDate implements IInteractor
{
    private $nevoboGateway;
    private $barcieGateway;
    private $joomlaGateway;

    public function __construct($database)
    {
        $this->nevoboGateway = new NevoboGateway();
        $this->barcieGateway = new BarcieGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $team = $this->joomlaGateway->GetTeam($userId);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($userId);

        $alleWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        $alleCoachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);

        $barcieDagen = $this->barcieGateway->GetBarcieDagen();
        $beschikbaarheden = $this->barcieGateway->GetBeschikbaarheden($userId);

        $response = [];
        foreach ($barcieDagen as $barcieDag) {
            $date = $barcieDag['date'];
            $eigenWedstrijden = array_filter($alleWedstrijden, function ($wedstrijd) use ($barcieDag, $date) {
                return $wedstrijd['timestamp'] && $wedstrijd['timestamp']->format("Y-m-d") == $date;
            });

            $coachWedstrijden = array_filter($alleCoachWedstrijden, function ($wedstrijd) use ($barcieDag, $date) {
                return $wedstrijd['timestamp'] && $wedstrijd['timestamp']->format("Y-m-d") == $date;
            });

            $beschikbaarheid = $this->GetBeschikbaarheid($beschikbaarheden, $date);

            $response[] = [
                "datum" => GetDutchDate(new DateTime($date)),
                "date" => $barcieDag['date'],
                "available" => $beschikbaarheid,
            ];
        }

        exit(json_encode($response));
    }

    private function GetBeschikbaarheid($beschikbaarheden, $date)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid['date'] == $date) {
                return $beschikbaarheid['available'];
            }
        }

        return null;
    }
}
