<?php

include 'IInteractor.php';
include 'UserGateway.php';
include 'NevoboGateway.php';
include 'AanwezigheidGateway.php';
include_once 'Utilities.php';

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
    private $invalTeams;

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();

        if ($userId == null) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

        $overzicht = [];
        $team = $this->userGateway->GetTeam($userId);
        $spelers = $this->userGateway->GetSpelers($team);
        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezighedenForTeam($team);
        $wedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        $aanwezigheidPerWedstrijd = $this->GetAanwezighedenPerWedstrijd($aanwezigheden);

        $this->GetAllInvalTeamsForTeam($team);
        foreach ($wedstrijden as $wedstrijd) {
            $invalTeamInfo = $this->GetInvalTeamInfo($wedstrijd);
            $matchId = $wedstrijd['id'];
            $aanwezigheid = $this->GetAanwezigheidForWedstrijd($matchId, $aanwezigheidPerWedstrijd);
            $overzicht[] = [
                "id" => $wedstrijd['id'],
                "datum" => $wedstrijd['timestamp']->format('j F Y'),
                "tijd" => $wedstrijd['timestamp']->format('G:i'),
                "team1" => $wedstrijd['team1'],
                "isTeam1" => $wedstrijd['team1'] == $team,
                "team2" => $wedstrijd['team2'],
                "isTeam2" => $wedstrijd['team2'] == $team,
                "aanwezigen" => $aanwezigheid['aanwezigen'],
                "afwezigen" => $aanwezigheid['afwezigen'],
                "onbekend" => $aanwezigheid["onbekend"],
                "invalTeams" => $invalTeamInfo,
            ];
        }

        echo json_encode($overzicht);
        exit;
    }

    private function GetInvalTeamInfo($wedstrijd)
    {
        $datum = $wedstrijd['timestamp']->format('j F Y');
        foreach ($this->invalTeams as $nevobonaam => $invalTeam) {
            $programma = $this->nevoboGateway->GetProgrammaForTeam($nevobonaam);
            $invalTeamWedstrijd = null;
            foreach ($programma as $programmaItem) {
                if ($programmaItem['timestamp']->format('j F Y') == $datum) {
                    $invalTeamWedstrijd = [
                        "timestamp" => $programmaItem['timestamp'],
                        "tijd" => $programmaItem['timestamp']->format('G:i'),
                        "team1" => $programmaItem['team1'],
                        "team2" => $programmaItem['team2'],
                        "locatie" => GetShortLocatie($programmaItem['locatie']),
                        "shortLocatie" => GetShortLocatie($programmaItem['locatie']),
                    ];
                    break;
                }
            }

            $invalTeams[] = [
                "naam" => GetSkcTeam($nevobonaam),
                "wedstrijd" => $invalTeamWedstrijd,
                "isMogelijk" => CheckIfPossible($wedstrijd, $invalTeamWedstrijd),
                "spelers" => $this->invalTeams[$nevobonaam]['spelers'],
            ];
        }
        return $invalTeams;
    }

    private function GetAllInvalTeamsForTeam($team)
    {
        $teams = [];
        $sequence = intval(substr($team, 7)) + 1;
        $maxSequence = substr($team, 4, 1) == "D" ? 15 : 8;
        for ($sequence + 1; $sequence <= $maxSequence; $sequence++) {
            $teamnaam = substr($team, 0, 7) . $sequence;
            $this->invalTeams[$teamnaam] = [
                "naam" => GetSkcTeam($teamnaam),
                "spelers" => $this->userGateway->GetSpelers($teamnaam),
            ];
        }
        return $teams;
    }

    private function GetAanwezigheidForWedstrijd($matchId, $aanwezigheden)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid['matchId'] == $matchId) {
                return $aanwezigheid;
            }
        }
        return [];
    }

    private function GetAanwezighedenPerWedstrijd($aanwezigheden)
    {
        $result = [];
        foreach ($aanwezigheden as $aanwezigheid) {
            $matchId = $aanwezigheid['matchId'];
            $antwoord = $aanwezigheid['aanwezigheid'];
            $naam = $aanwezigheid['naam'];

            if (!isset($result[$matchId])) {
                $result[$matchId] = [
                    "matchId" => $matchId,
                    "aanwezigen" => [],
                    "onbekend" => [],
                    "afwezigen" => [],
                ];
            }

            switch ($antwoord) {
                case "Ja":
                    $result[$matchId]["aanwezigen"][] = [
                        "id" => $aanwezigheid['userId'],
                        "naam" => $naam,
                        "isInvaller" => $aanwezigheid['isInvaller'] == "1",
                    ];
                    break;
                case "Nee":
                    $result[$matchId]["afwezigen"][] = [
                        "id" => $aanwezigheid['userId'],
                        "naam" => $naam,
                    ];
                    break;
                default:
                    $result[$matchId]["onbekend"][] = [
                        "id" => $aanwezigheid['userId'],
                        "naam" => $naam,
                    ];
                    break;
            }
        }
        return $result;
    }
}
