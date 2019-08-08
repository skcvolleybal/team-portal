<?php

use PHPMailer\PHPMailer\Exception;

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
        $spelers = $this->joomlaGateway->GetSpelers($team);
        $coachteam = $this->joomlaGateway->GetCoachTeam($userId);

        $wedstrijden = array_merge(
            $this->nevoboGateway->GetProgrammaForTeam($team),
            $this->nevoboGateway->GetProgrammaForTeam($coachteam)
        );
        usort($wedstrijden, function ($w1, $w2) {
            return $w1->timestamp < $w2->timestamp;
        });

        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezighedenForTeam($team);

        $this->GetAllInvalTeamsForTeam($team);
        foreach ($wedstrijden as $wedstrijd) {
            $invalTeamInfo = $this->GetInvalTeamInfo($wedstrijd);
            $matchId = $wedstrijd->id;
            $aanwezighedenForMatch = $this->GetAanwezighedenForWedstrijd($matchId, $aanwezigheden);
            $afwezigenForMatch = $this->GetAfwezigen($aanwezighedenForMatch, $spelers);
            if ($wedstrijd->timestamp) {
                $overzicht[] = (object) [
                    'id' => $wedstrijd->id,
                    'datum' => GetDutchDate($wedstrijd->timestamp),
                    'tijd' => $wedstrijd->timestamp->format('G:i'),
                    'team1' => $wedstrijd->team1,
                    'isTeam1' => $wedstrijd->team1 == $team,
                    'team2' => $wedstrijd->team2,
                    'isTeam2' => $wedstrijd->team2 == $team,
                    'aanwezigen' => $aanwezighedenForMatch->aanwezigen,
                    'afwezigen' => $aanwezighedenForMatch->afwezigen,
                    'onbekend' => $aanwezighedenForMatch->onbekend,
                    'coaches' => $aanwezighedenForMatch->coaches,
                    'invalTeams' => $invalTeamInfo,
                ];
            }
        }

        exit(json_encode($overzicht));
    }

    private function GetInvalTeamInfo($wedstrijd)
    {
        if (!$wedstrijd->timestamp) {
            return null;
        }

        $datumWedstrijd = $wedstrijd->timestamp->format("Y-m-d");
        foreach ($this->invalTeams as $invalTeam) {
            $nevobonaam = $invalTeam->nevobonaam;
            $invalTeamWedstrijd = null;
            foreach ($invalTeam->programma as $programmaItem) {
                if ($programmaItem->timestamp === null) {
                    continue;
                }

                $datumInvalwedstrijd = $programmaItem->timestamp->format("Y-m-d");
                if ($datumWedstrijd === $datumInvalwedstrijd) {
                    $invalTeamWedstrijd = (object) [
                        'timestamp' => $programmaItem->timestamp,
                        'tijd' => $programmaItem->timestamp->format('G:i'),
                        'team1' => $programmaItem->team1,
                        'team2' => $programmaItem->team2,
                        'locatie' => $programmaItem->locatie,
                        'shortLocatie' => GetShortLocatie($programmaItem->locatie),
                    ];
                    break;
                }
            }

            $invalTeams[] = (object) [
                'naam' => ToSkcName($nevobonaam),
                'wedstrijd' => $invalTeamWedstrijd,
                'isMogelijk' => IsMogelijk($wedstrijd, $invalTeamWedstrijd),
                'spelers' => $invalTeam->spelers,
            ];
        }
        return $invalTeams;
    }

    private function GetAllInvalTeamsForTeam($teamnaam)
    {
        $this->invalTeams = [];
        $allTeams = (object) [
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
        $teams = substr($teamnaam, 4, 1) == "D" ? $allTeams->dames : $allTeams->heren;
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
                $this->invalTeams[] = (object) [
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

    private function GetAanwezighedenForWedstrijd($matchId, $aanwezigheden)
    {
        $result = (object) [
            'aanwezigen' => [],
            'afwezigen' => [],
            'onbekend' => [],
            'coaches' => []
        ];
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->match_id == $matchId) {
                $isCoach = $aanwezigheid->is_coach === "Y";
                if ($isCoach) {
                    $result->coaches[] = $aanwezigheid;
                }

                $isAanwezig = $aanwezigheid->is_aanwezig === "Y";
                if ($isAanwezig) {
                    $result->aanwezigen[] = $aanwezigheid;
                } else {
                    $result->afwezigen[] = $aanwezigheid;
                }
            }
        }
        return $result;
    }

    private function GetAfwezigen($aanwezigheden, $spelers)
    {
        $result = [];
        $bekendeAanwezigheden = array_merge($aanwezigheden->aanwezigen, $aanwezigheden->afwezigen);
        if (count($bekendeAanwezigheden) == 0 || $spelers == null || count($spelers) == 0) {
            return $result;
        }

        $spelersIndex = 0;
        foreach ($bekendeAanwezigheden as $aanwezigheid) {
            foreach ($spelers as $speler) {
                if ($aanwezigheid->user_id === $speler->id) {
                    $result[] = $speler;
                    break;
                }
            }
        }
        return $result;
    }
}
