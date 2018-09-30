<?php

include 'IInteractorWithData.php';
include 'UserGateway.php';
include 'NevoboGateway.php';
include 'AanwezigheidGateway.php';

class UpdateAanwezigheid implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
    }

    private $nevoboGateway;

    public function Execute($data)
    {
        $userId = $this->userGateway->GetUserId();

        if ($userId == null) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

        $matchId = $data->matchId;
        $aanwezigheid = $data->aanwezigheid;

        $this->aanwezigheidGateway->UpdateAanwezigheid($userId, $matchId, $aanwezigheid);

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
