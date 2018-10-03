<?php

include 'IInteractor.php';
include 'UserGateway.php';
include 'AanwezigheidGateway.php';
include_once 'NevoboGateway.php';

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
        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezigheden($userId);
        $wedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);

        $overzicht = [];
        foreach ($wedstrijden as $wedstrijd) {
            $aanwezigheid = $this->GetAanwezigheid($aanwezigheden, $wedstrijd['id']);
            $overzicht[] = $this->MapFromNevoboMatch($wedstrijd, $aanwezigheid, $team);
        }

        echo json_encode($overzicht);
        exit;
    }

    private function MapFromNevoboMatch($wedstrijd, $aanwezigheid, $team)
    {
        return [
            "id" => $wedstrijd['id'],
            "datum" => $wedstrijd["timestamp"]->format("j F Y"),
            "tijd" => $wedstrijd["timestamp"]->format('G:i'),
            "team1" => $wedstrijd["team1"],
            "isTeam1" => $wedstrijd["team1"] == $team,
            "team2" => $wedstrijd["team2"],
            "isTeam2" => $wedstrijd["team2"] == $team,
            "aanwezigheid" => $aanwezigheid['aanwezigheid'] ?? "Misschien",
        ];
    }

    private function GetAanwezigheid($aanwezigheden, $matchId)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid['match_id'] == $matchId) {
                return $aanwezigheid;
            }
        }
        return null;
    }
}
