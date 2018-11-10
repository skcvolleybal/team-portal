<?php

include_once 'IInteractor.php';
include_once 'JoomlaGateway.php';
include_once 'AanwezigheidGateway.php';
include_once 'NevoboGateway.php';

class GetWedstrijdAanwezigheid implements IInteractor
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    private $nevoboGateway;
    private $joomlaGateway;
    private $aanwezigheidGateway;

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $team = $this->joomlaGateway->GetTeam($userId);
        if (!$team) {
            InternalServerError("Je zit niet in een team");
        }
        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezigheden($userId);
        $wedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);

        $overzicht = [];
        foreach ($wedstrijden as $wedstrijd) {
            $aanwezigheid = $this->GetAanwezigheid($aanwezigheden, $wedstrijd['id']);
            $newWedstrijd = $this->MapFromNevoboMatch($wedstrijd, $aanwezigheid, $team);
            if ($newWedstrijd) {
                $overzicht[] = $newWedstrijd;
            }
        }

        echo json_encode($overzicht);
        exit;
    }

    private function MapFromNevoboMatch($wedstrijd, $aanwezigheid, $team)
    {
        if (!$wedstrijd['timestamp']) {
            return null;
        }
        return [
            "id" => $wedstrijd['id'],
            "datum" => GetDutchDate($wedstrijd["timestamp"]),
            "tijd" => $wedstrijd["timestamp"]->format('G:i'),
            "team1" => $wedstrijd["team1"],
            "isTeam1" => $wedstrijd["team1"] == $team,
            "team2" => $wedstrijd["team2"],
            "isTeam2" => $wedstrijd["team2"] == $team,
            "aanwezigheid" => $aanwezigheid['aanwezigheid'] ?? "Onbekend",
        ];
    }

    private function GetAanwezigheid($aanwezigheden, $matchId)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid['matchId'] == $matchId) {
                return $aanwezigheid;
            }
        }
        return null;
    }
}
