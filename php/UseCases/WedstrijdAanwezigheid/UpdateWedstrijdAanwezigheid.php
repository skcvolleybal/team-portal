<?php

include 'IInteractor.php';
include 'UserGateway.php';
include 'NevoboGateway.php';

class UpdateWedstrijdAanwezigheid implements IInteractor
{
    public function __construct()
    {
        $this->userGateway = new UserGateway();
        $this->aanwezigheidGateway = new AanwezigheidGateway();
    }

    private $nevoboGateway;

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();

        if ($userId == null) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

        $team = $this->userGateway->GetTeam($userId);
        $aanwezigheden = $this->aanwezigheidGateway->GetWedstrijdAanwezigheden($userId);

        $overzicht = [];
        foreach ($wedstrijden as $wedstrijd) {
            $aanwezigheid = $this->GetAanwezigheid($aanwezigheden, $wedstrijd);
            $overzicht[] = $this->MapFromNevoboMatch($wedstrijd, $aanwezigheid, $team);
        }

        print_r($overzicht);exit;
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
