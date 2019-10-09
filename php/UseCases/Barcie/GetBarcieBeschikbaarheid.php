<?php

include_once 'IInteractor.php';
include_once 'NevoboGateway.php';
include_once 'BarcieGateway.php';
include_once 'JoomlaGateway.php';
include_once 'GetNevoboMatchByDate.php';
include_once 'shared/BarcieBeschikbaarheidHelper.php';

class GetBarcieBeschikbaarheid extends GetNevoboMatchByDate implements IInteractor
{
    public function __construct($database)
    {
        $this->nevoboGateway = new NevoboGateway();
        $this->barcieGateway = new BarcieGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->barcieBeschikbaarheidHelper = new BarcieBeschikbaarheidHelper();
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
            $date = $barcieDag->date;
            $eigenWedstrijden = array_filter($alleWedstrijden, function ($wedstrijd) use ($date) {
                return $wedstrijd->timestamp && $wedstrijd->timestamp->format("Y-m-d") == $date;
            });

            $coachWedstrijden = array_filter($alleCoachWedstrijden, function ($wedstrijd) use ($date) {
                return $wedstrijd->timestamp && $wedstrijd->timestamp->format("Y-m-d") == $date;
            });

            $wedstrijden = array_merge($eigenWedstrijden, $coachWedstrijden);

            $isBeschikbaar = $this->GetBeschikbaarheid($beschikbaarheden, $date);

            $response[] = (object) [
                "datum" => GetDutchDate(new DateTime($date)),
                "date" => $barcieDag->date,
                "beschikbaarheid" => $isBeschikbaar,
                "eigenWedstrijden" => $this->MapToUsecase($wedstrijden, $team, $coachTeam),
                "isMogelijk" => $this->barcieBeschikbaarheidHelper->isMogelijk($wedstrijden),
            ];
        }

        exit(json_encode($response));
    }



    private function MapToUsecase($wedstrijden, $team, $coachTeam)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $result[] = (object) [
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
        return $result;
    }

    private function GetBeschikbaarheid($beschikbaarheden, $date)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $date) {
                return $beschikbaarheid->is_beschikbaar;
            }
        }

        return "Onbekend";
    }
}
