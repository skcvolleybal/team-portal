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

            $wedstrijden = $this->MapToUsecase(array_merge($eigenWedstrijden, $coachWedstrijden), $team, $coachTeam);

            $beschikbaarheid = $this->GetBeschikbaarheid($beschikbaarheden, $date);

            $response[] = [
                "datum" => GetDutchDate(new DateTime($date)),
                "date" => $barcieDag['date'],
                "beschikbaarheid" => $beschikbaarheid,
                "eigenWedstrijden" => $wedstrijden,
                "isMogelijk" => $this->isMogelijk($wedstrijden) ? "Ja" : "Nee",
            ];
        }

        exit(json_encode($response));
    }

    private function IsMogelijk($wedstrijden)
    {
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd['isTeam1'] || $wedstrijd['isTeam2']) {
                if (IsThuis($wedstrijd['locatie'])) {
                    return true;
                } else {
                    return false;
                }
            }
            if ($wedstrijd['isCoachTeam1'] || $wedstrijd['isCoachTeam2']) {
                if (IsThuis($wedstrijd['locatie'])) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    private function MapToUsecase($wedstrijden, $team, $coachTeam)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $result[] = [
                "datum" => GetDutchDate($wedstrijd['timestamp']),
                "tijd" => $wedstrijd['timestamp']->format('H:i'),
                "team1" => $wedstrijd['team1'],
                "isTeam1" => $wedstrijd['team1'] == $team,
                "isCoachTeam1" => $wedstrijd['team1'] == $coachTeam,
                "team2" => $wedstrijd['team2'],
                "isTeam2" => $wedstrijd['team2'] == $team,
                "isCoachTeam2" => $wedstrijd['team2'] == $coachTeam,
                "locatie" => GetShortLocatie($wedstrijd['locatie']),
            ];
        }
        return $result;
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
}
