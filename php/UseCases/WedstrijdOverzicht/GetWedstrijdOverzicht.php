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
        $spelers = $this->joomlaGateway->GetTeamgenoten($team);
        $coachteam = $this->joomlaGateway->GetCoachTeam($userId);

        $teamprogramma = $this->nevoboGateway->GetProgrammaForTeam($team);
        $coachProgramma = $this->nevoboGateway->GetProgrammaForTeam($coachteam);
        $wedstrijden = array_merge(
            $teamprogramma,
            $coachProgramma
        );
        usort($wedstrijden, "WedstrijdenSortFunction");

        $teamMatchIds = array_map(function ($wedstrijd) {
            return $wedstrijd->id;
        }, $teamprogramma);
        $coachMatchIds = array_map(function ($wedstrijd) {
            return $wedstrijd->id;
        }, $coachProgramma);
        $allMatchIds = array_merge($teamMatchIds, $coachMatchIds);

        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezighedenForMatchIds($allMatchIds);

        $this->GetAllInvalTeamsForTeam($team);
        foreach ($wedstrijden as $wedstrijd) {
            $matchId = $wedstrijd->id;
            $isAanwezig = $this->IsAanwezig($aanwezigheden, $matchId, $userId);

            $aanwezighedenForMatch = [];
            $invalTeamInfo = null;
            $onbekendForMatch = [];

            $isEigenWedstrijd = $this->isEigenWedstrijd($wedstrijd, $team);
            if ($isEigenWedstrijd) {
                $aanwezighedenForMatch = $this->GetAanwezighedenForWedstrijd($matchId, $aanwezigheden, $team);
                $invalTeamInfo = $this->GetInvalTeamInfo($wedstrijd);
                $onbekendForMatch = $this->GetOnbekenden($aanwezighedenForMatch, $spelers);
            }

            if ($wedstrijd->timestamp) {
                $overzicht[] = (object) [
                    'id' => $wedstrijd->id,
                    'datum' => GetDutchDate($wedstrijd->timestamp),
                    'tijd' => $wedstrijd->timestamp->format('G:i'),
                    'team1' => $wedstrijd->team1,
                    'isTeam1' => $wedstrijd->team1 == $team,
                    'isCoachTeam1' => $wedstrijd->team1 == $coachteam,
                    'team2' => $wedstrijd->team2,
                    'isTeam2' => $wedstrijd->team2 == $team,
                    'isCoachTeam2' => $wedstrijd->team2 == $coachteam,
                    'aanwezigen' => $aanwezighedenForMatch != null ? $aanwezighedenForMatch->aanwezigen : [],
                    'afwezigen' => $aanwezighedenForMatch != null ? $aanwezighedenForMatch->afwezigen : [],
                    'onbekend' => $onbekendForMatch,
                    'coaches' => $aanwezighedenForMatch != null ? $aanwezighedenForMatch->coaches : [],
                    'invalTeams' => $invalTeamInfo,
                    'isEigenWedstrijd' => $isEigenWedstrijd,
                    'isAanwezig' => $isAanwezig
                ];
            }
        }

        exit(json_encode($overzicht));
    }

    private function isEigenWedstrijd($wedstrijd, $team)
    {
        return $wedstrijd->team1 == $team || $wedstrijd->team2 == $team;
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
        $allTeams = include('AllTeams.php');

        $eigenTeam = null;
        $teams = substr($teamnaam, 4, 1) == "D" ? $allTeams->dames : $allTeams->heren;
        foreach ($teams as $team) {
            if ($team->naam == $teamnaam) {
                $eigenTeam = $team;
                break;
            }
        }
        if ($eigenTeam === null) {
            return;
        }

        for ($i = 0; $i < count($teams); $i++) {
            $team = $teams[$i];
            if (
                $team->naam != $eigenTeam->naam &&
                $team->klasse >= $eigenTeam->klasse
            ) {
                $this->invalTeams[] = (object) [
                    "nevobonaam" => $team->naam,
                    "naam" => ToSkcName($team->naam),
                    "spelers" => $this->joomlaGateway->GetTeamgenoten($team->naam),
                    "programma" => $this->nevoboGateway->GetProgrammaForTeam($team->naam)
                ];
            }

            if (count($this->invalTeams) >= 5) {
                return;
            }
        }
    }

    private function GetAanwezighedenForWedstrijd($matchId, $aanwezigheden, $team)
    {
        $result = (object) [
            'aanwezigen' => [],
            'afwezigen' => [],
            'onbekend' => [],
            'coaches' => []
        ];
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->match_id == $matchId) {
                $isAanwezig = $aanwezigheid->is_aanwezig === "Ja";
                if (substr($aanwezigheid->rol, 0, 5) === 'coach') {
                    $result->coaches[] = $aanwezigheid;
                } else {
                    $newAanwezigheid = (object) [
                        "id" => $aanwezigheid->user_id,
                        "naam" => $aanwezigheid->naam,
                        "isInvaller" => $aanwezigheid->team !== ToSkcName($team)
                    ];
                    if ($isAanwezig) {
                        $result->aanwezigen[] = $newAanwezigheid;
                    } else {
                        $result->afwezigen[] = $newAanwezigheid;
                    }
                }
            }
        }
        return $result;
    }

    private function GetOnbekenden($aanwezigheden, $spelers)
    {
        $bekendeAanwezigheden = array_merge($aanwezigheden->aanwezigen, $aanwezigheden->afwezigen);
        if (count($bekendeAanwezigheden) == 0 || $spelers == null || count($spelers) == 0) {
            return $spelers;
        }

        foreach ($bekendeAanwezigheden as $aanwezigheid) {
            for ($i = 0; $i < count($spelers); $i++) {
                if ($aanwezigheid->id === $spelers[$i]->id) {
                    array_splice($spelers, $i, 1);
                    break;
                }
            }
        }
        return $spelers;
    }

    private function IsAanwezig($aanwezigheden, $matchId, $userId)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if (
                $aanwezigheid->user_id === $userId &&
                $aanwezigheid->match_id === $matchId
            ) {
                return $aanwezigheid->is_aanwezig === 'Ja' ? "Ja" : "Nee";
            }
        }
        return 'Onbekend';
    }
}
