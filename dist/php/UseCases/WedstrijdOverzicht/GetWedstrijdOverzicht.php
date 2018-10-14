<?php

include_once 'IInteractor.php';
include_once 'JoomlaGateway.php';
include_once 'NevoboGateway.php';
include_once 'AanwezigheidGateway.php';
include_once 'Utilities.php';

class GetWedstrijdOverzicht implements IInteractor
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    private $aanwezigheidGateway;
    private $joomlaGateway;
    private $nevoboGateway;
    private $invalTeams;

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $overzicht = [];
        $team = $this->joomlaGateway->GetTeam($userId);
        $spelers = $this->joomlaGateway->GetSpelers($team);
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
                "datum" => GetDutchDate($wedstrijd['timestamp']),
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
        foreach ($this->invalTeams as $nevobonaam => $invalTeam) {
            $programma = $this->nevoboGateway->GetProgrammaForTeam($nevobonaam);
            $invalTeamWedstrijd = null;
            foreach ($programma as $programmaItem) {
                if ($programmaItem['timestamp'] == $wedstrijd['timestamp']) {
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
                "naam" => ToSkcName($nevobonaam),
                "wedstrijd" => $invalTeamWedstrijd,
                "isMogelijk" => IsMogelijk($wedstrijd, $invalTeamWedstrijd),
                "spelers" => $this->invalTeams[$nevobonaam]['spelers'],
            ];
        }
        return $invalTeams;
    }

    private function GetAllInvalTeamsForTeam($team)
    {
        $this->invalTeams = [];
        $startSequences = [
            "SKC DS 1" => 2,
            "SKC DS 2" => 3,
            "SKC DS 3" => 3,
            "SKC DS 4" => 3,
            "SKC DS 5" => 5,
            "SKC DS 6" => 5,
            "SKC DS 7" => 5,
            "SKC DS 8" => 8,
            "SKC DS 9" => 8,
            "SKC DS 10" => 8,
            "SKC DS 11" => 8,
            "SKC DS 12" => 8,
            "SKC DS 13" => 8,
            "SKC DS 14" => 8,
            "SKC DS 15" => 8,
            "SKC HS 1" => 2,
            "SKC HS 2" => 3,
            "SKC HS 3" => 4,
            "SKC HS 4" => 5,
            "SKC HS 5" => 5,
            "SKC HS 6" => 5,
            "SKC HS 7" => 5,
            "SKC HS 8" => 5,
        ];
        if (!isset($startSequences[$team])) {
            return [];
        }
        $startSequence = intval($startSequences[$team]);
        $maxSequence = substr($team, 4, 1) == "D" ? 15 : 8;
        for ($sequence = $startSequence; $sequence <= $maxSequence; $sequence++) {
            $teamnaam = substr($team, 0, 7) . $sequence;
            if ($team != $teamnaam) {
                $this->invalTeams[$teamnaam] = [
                    "naam" => ToSkcName($teamnaam),
                    "spelers" => $this->joomlaGateway->GetSpelers($teamnaam),
                ];
            }

            if (count($this->invalTeams) >= 5) {
                return;
            }
        }
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
