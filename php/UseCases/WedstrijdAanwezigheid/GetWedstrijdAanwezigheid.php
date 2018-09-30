<?php

include 'IInteractor.php';
include 'UserGateway.php';
include 'AanwezigheidGateway.php';
include 'NevoboGateway.php';

class GetWedstrijdAanwezigheid implements IInteractor
{
    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    private $nevoboGateway;
    private $userGateway;
    private $aanwezigheidGateway;

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();

        if ($userId == null) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

        $team = $this->userGateway->GetTeam($userId);
        $aanwezigheden = $this->aanwezigheidGateway->GetWedstrijdAanwezigheden($userId);
        $wedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);

        $overzicht = [];
        foreach ($wedstrijden as $wedstrijd) {
            $aanwezigheid = $this->GetAanwezigheid($aanwezigheden, $wedstrijd);
            $overzicht[] = $this->MapFromNevoboMatch($wedstrijd, $aanwezigheid, $team);
        }

        echo json_encode($overzicht);
        exit;
    }

    private function MapFromNevoboMatch($wedstrijd, $aanwezigheid, $team)
    {
        return [
            "datum" => $wedstrijd["timestamp"]->format("j F Y"),
            "tijd" => $wedstrijd["timestamp"]->format('G:i'),
            "team1" => $wedstrijd["team1"],
            "isTeam1" => $wedstrijd["team1"] == $team,
            "team2" => $wedstrijd["team2"],
            "isTeam" => $wedstrijd["team2"] == $team,
            "aanwezig" => $aanwezigheid,
        ];
    }

    private function GetAanwezigheid($aanwezigheden, $wedstrijd)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($wedstrijd['id'] == $aanwezigheid['match_id']) {
                return $aanwezigheid;
            }
        }
        return null;
    }
}
