<?php

include 'IInteractor.php';
include 'UserGateway.php';
include 'NevoboGateway.php';
include 'AanwezigheidGateway.php';

class GetWedstrijdOverzicht implements IInteractor
{
    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    private $aanwezigheidGateway;
    private $userGateway;
    private $nevoboGateway;

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();

        if ($userId == null) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

        $overzicht = [];
        $team = $this->userGateway->GetTeam($userId);
        $players = $this->userGateway->GetPlayers($team);
        $wedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        $matchIds = $this->GetMatchIds($wedstrijden);
        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezigheidForTeam($matchIds);
        $aanwezighedenPerWedstrijd = $this->GetAanwezighedenPerWedstrijd($aanwezigheden);

        foreach ($wedstrijden as $wedstrijd) {
            $matchId = $wedstrijd['id'];
            foreach ($aanwezighedenPerWedstrijd as $key => $value) {
                if ($key == $matchId) {
                    break;
                }
            }
            $aanwezigheidVoorDezeWedstrijd = $this->GetAanwezigheidForWedstrijd($matchId, $aanwezighedenPerWedstrijd);
            $overzicht[] = [
                "datum" => $wedstrijd['datum'],
                "tijd" => $wedstrijd['tijd'],
                "team1" => $wedstrijd['team1'],
                "isTeam1" => $wedstrijd['team1'] == $team,
                "team2" => $wedstrijd['team2'],
                "isTeam2" => $wedstrijd['team2'] == $team,
                "aanwezig" => $value['aanwezig'],
                "afwezig" => $value['afwezig'],
                "onbekend" => $value["onbekend"],
            ];
        }

        echo json_encode($overzicht);
        exit;
    }

    private function GetAanwezighedenPerWedstrijd($aanwezigheden)
    {
        $result = [];
        foreach ($aanwezigheden as $aanwezigheid) {
            $matchId = $aanwezigheid['match_id'];
            $antwoord = $aanwezigheid['aanwezigheid'];
            $naam = $aanwezigheid['naam'];

            if (!isset($result[$matchId])) {
                $result[$matchId] = [
                    "id" => $matchId,
                    "aanwezig" => [],
                    "onbekend" => [],
                    "afwezig" => [],
                ];
            }

            switch ($antwoord) {
                case "Ja":
                    $result[$matchId]["aanwezig"][] = $naam;
                    break;
                case "Nee":
                    $result[$matchId]["afwezig"][] = $naam;
                    break;
                case "Misschien":
                    $result[$matchId]["onbekend"][] = $naam;
                    break;
            }
        }
        return $result;
    }

    private function GetMatchIds($wedstrijden)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $result[] = $wedstrijd['id'];
        }
        return $result;
    }
}
