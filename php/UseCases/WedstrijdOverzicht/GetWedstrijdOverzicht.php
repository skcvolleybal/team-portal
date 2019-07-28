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

    public function Execute()
    {

        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        $overzicht = [];
        $team = $this->joomlaGateway->GetTeam($userId);
        if (!$team) {
            InternalServerError("Je zit niet in een team");
        }

        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezighedenForTeam($team);
        usort($aanwezigheden, function ($a, $b) {
            return $a["naam"] > $b["naam"];
        });
        $coachAanwezigheden = $this->aanwezigheidGateway->GetCoachAanwezighedenForTeam(ToSkcName($team));
        $wedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        $aanwezigheidPerWedstrijd = $this->GetAanwezighedenPerWedstrijd($aanwezigheden);

        $this->GetAllInvalTeamsForTeam($team);
        foreach ($wedstrijden as $wedstrijd) {
            $invalTeamInfo = $this->GetInvalTeamInfo($wedstrijd);
            $matchId = $wedstrijd['id'];
            $aanwezigheid = $this->GetAanwezigheidForWedstrijd($matchId, $aanwezigheidPerWedstrijd);
            if ($wedstrijd['timestamp']) {
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
                    "coaches" => $this->GetCoaches($coachAanwezigheden, $wedstrijd['id']),
                    "invalTeams" => $invalTeamInfo,
                ];
            }
        }

        exit(json_encode($overzicht));
    }

    private function GetCoaches($coachAanwezigheden, $matchId)
    {
        $result = [];
        foreach ($coachAanwezigheden as $coachAanwezigheid) {
            if ($coachAanwezigheid['matchId'] === $matchId) {
                $result[] = [
                    "naam" => $coachAanwezigheid['naam'],
                    "aanwezigheid" => $coachAanwezigheid['aanwezigheid'] == "Ja",
                ];
            }
        }
        return $result;
    }

    private function GetInvalTeamInfo($wedstrijd)
    {
        if ($wedstrijd["timestamp"] === null) {
            return null;
        }

        $datumWedstrijd = $wedstrijd["timestamp"]->format("Y-m-d");
        foreach ($this->invalTeams as $invalTeam) {
            $nevobonaam = $invalTeam["nevobonaam"];
            $invalTeamWedstrijd = null;
            foreach ($invalTeam["programma"] as $programmaItem) {
                if ($programmaItem["timestamp"] === null) {
                    continue;
                }

                $datumInvalwedstrijd = $programmaItem["timestamp"]->format("Y-m-d");
                if ($datumWedstrijd === $datumInvalwedstrijd) {
                    $invalTeamWedstrijd = [
                        "timestamp" => $programmaItem['timestamp'],
                        "tijd" => $programmaItem['timestamp']->format('G:i'),
                        "team1" => $programmaItem['team1'],
                        "team2" => $programmaItem['team2'],
                        "locatie" => $programmaItem['locatie'],
                        "shortLocatie" => GetShortLocatie($programmaItem['locatie']),
                    ];
                    break;
                }
            }

            $invalTeams[] = [
                "naam" => ToSkcName($nevobonaam),
                "wedstrijd" => $invalTeamWedstrijd,
                "isMogelijk" => IsMogelijk($wedstrijd, $invalTeamWedstrijd),
                "spelers" => $invalTeam['spelers'],
            ];
        }
        return $invalTeams;
    }

    private function GetAllInvalTeamsForTeam($teamnaam)
    {
        $this->invalTeams = [];
        $allTeams = [
            "dames" => [
                ["naam" => "SKC DS 1", "klasse" => 0],
                ["naam" => "SKC DS 2", "klasse" => 1],
                ["naam" => "SKC DS 3", "klasse" => 2],
                ["naam" => "SKC DS 4", "klasse" => 2],
                ["naam" => "SKC DS 5", "klasse" => 3],
                ["naam" => "SKC DS 6", "klasse" => 3],
                ["naam" => "SKC DS 7", "klasse" => 3],
                ["naam" => "SKC DS 8", "klasse" => 3],
                ["naam" => "SKC DS 9", "klasse" => 3],
                ["naam" => "SKC DS 10", "klasse" => 4],
                ["naam" => "SKC DS 11", "klasse" => 4],
                ["naam" => "SKC DS 12", "klasse" => 4],
                ["naam" => "SKC DS 13", "klasse" => 4],
                ["naam" => "SKC DS 14", "klasse" => 4],
                ["naam" => "SKC DS 15", "klasse" => 4],
            ],
            "heren" => [
                ["naam" => "SKC HS 1", "klasse" => 1],
                ["naam" => "SKC HS 2", "klasse" => 1],
                ["naam" => "SKC HS 3", "klasse" => 2],
                ["naam" => "SKC HS 4", "klasse" => 3],
                ["naam" => "SKC HS 5", "klasse" => 3],
                ["naam" => "SKC HS 6", "klasse" => 4],
                ["naam" => "SKC HS 7", "klasse" => 4],
                ["naam" => "SKC HS 8", "klasse" => 4],
                ["naam" => "SKC HS 9", "klasse" => 4]
            ]
        ];

        $eigenTeam = null;
        $teams = substr($teamnaam, 4, 1) == "D" ? $allTeams["dames"] : $allTeams["heren"];
        foreach ($teams as $team) {
            if ($team["naam"] == $teamnaam) {
                $eigenTeam = $team;
                break;
            }
        }
        if ($eigenTeam === null) {
            return;
        }

        for ($i = 0; $i < count($teams); $i++) {
            $team = $teams[$i];
            if ($team["naam"] != $eigenTeam["naam"] && $team["klasse"] >= $eigenTeam["klasse"]) {
                $this->invalTeams[] = [
                    "nevobonaam" => $team["naam"],
                    "naam" => ToSkcName($team["naam"]),
                    "spelers" => $this->joomlaGateway->GetSpelers($team["naam"]),
                    "programma" => $this->nevoboGateway->GetProgrammaForTeam($team["naam"])
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
