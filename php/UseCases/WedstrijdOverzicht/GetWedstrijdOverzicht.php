<?php


class GetWedstrijdOverzicht implements IInteractor
{
    private array $invalTeams;

    public function __construct(
        JoomlaGateway $joomlaGateway,
        AanwezigheidGateway $aanwezigheidGateway,
        NevoboGateway $nevoboGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            throw new UnauthorizedException();
        }

        $overzicht = [];
        $team = $this->joomlaGateway->GetTeam($userId);
        $team->spelers = $this->joomlaGateway->GetTeamgenoten($team);
        $coachteam = $this->joomlaGateway->GetCoachTeam($userId);

        $teamprogramma = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        $coachProgramma = $this->nevoboGateway->GetWedstrijdenForTeam($coachteam);
        $wedstrijden = array_merge(
            $teamprogramma,
            $coachProgramma
        );
        usort($wedstrijden, Wedstrijd::class . "::Compare");

        $teamMatchIds = array_map(function ($wedstrijd) {
            return $wedstrijd->matchId;
        }, $teamprogramma);
        $coachMatchIds = array_map(function ($wedstrijd) {
            return $wedstrijd->matchId;
        }, $coachProgramma);
        $allMatchIds = array_merge($teamMatchIds, $coachMatchIds);

        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezighedenForMatchIds($allMatchIds);

        $this->GetAllInvalTeamsForTeam($team);
        foreach ($wedstrijden as $wedstrijd) {
            $matchId = $wedstrijd->matchId;
            $isAanwezig = $this->IsAanwezig($aanwezigheden, $matchId, $userId);

            $aanwezighedenForMatch = [];
            $invalTeamInfo = null;
            $onbekendForMatch = [];

            $isEigenWedstrijd = $wedstrijd->isEigenWedstrijd($team);
            if ($isEigenWedstrijd) {
                $aanwezighedenForMatch = $this->GetAanwezighedenForWedstrijd($matchId, $aanwezigheden, $team);
                $invalTeamInfo = $this->GetInvalTeamInfo($wedstrijd);
                $onbekendForMatch = $this->GetOnbekenden($aanwezighedenForMatch, $team);
            }

            if ($wedstrijd->timestamp) {
                $overzicht[] = (object) [
                    'matchId' => $wedstrijd->matchId,
                    'datum' => DateFunctions::GetDutchDate($wedstrijd->timestamp),
                    'tijd' => $wedstrijd->timestamp->format('G:i'),
                    'team1' => $wedstrijd->team1->naam,
                    'isTeam1' => $wedstrijd->team1->Equals($team),
                    'isCoachTeam1' => $wedstrijd->team1->Equals($coachteam),
                    'team2' => $wedstrijd->team2->naam,
                    'isTeam2' => $wedstrijd->team2->Equals($team),
                    'isCoachTeam2' => $wedstrijd->team2->Equals($coachteam),
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

        return $overzicht;
    }

    private function GetInvalTeamInfo(Wedstrijd $eigenWedstrijd)
    {
        $datumWedstrijd = DateFunctions::GetYmdNotation($eigenWedstrijd->timestamp);
        foreach ($this->invalTeams as $invalTeam) {
            $invalTeamWedstrijd = null;
            foreach ($invalTeam->programma as $wedstrijd) {
                $datumInvalwedstrijd = DateFunctions::GetYmdNotation($wedstrijd->timestamp);
                if ($datumWedstrijd === $datumInvalwedstrijd) {
                    $invalTeamWedstrijd = Wedstrijd::CreateFromNevoboWedstrijd(
                        $wedstrijd->matchId,
                        $wedstrijd->team1,
                        $wedstrijd->team2,
                        $wedstrijd->poule,
                        $wedstrijd->timestamp,
                        $wedstrijd->locatie
                    );
                    break;
                }
            }

            $invalTeams[] = (object) [
                'naam' => $invalTeam->GetSkcNaam(),
                'wedstrijd' => $invalTeamWedstrijd != null ? (object) [
                    'team1' => $invalTeamWedstrijd->team1->naam,
                    'team2' => $invalTeamWedstrijd->team2->naam,
                    'locatie' => $invalTeamWedstrijd->locatie,
                    'tijd' => DateFunctions::GetTime($invalTeamWedstrijd->timestamp)
                ] : null,
                'isMogelijk' => $wedstrijd->IsMogelijk($invalTeamWedstrijd),
                'spelers' => $invalTeam->teamgenoten,
            ];
        }
        return $invalTeams;
    }

    private function GetAllInvalTeamsForTeam(Team $eigenTeam)
    {
        $teams = $eigenTeam->IsMale() ? Team::GetAlleHerenTeams() : Team::GetAlleDamesTeams();

        $this->invalTeams = [];
        foreach ($teams as $team) {
            if ($team->naam != $eigenTeam->naam && $team->niveau >= $eigenTeam->niveau) {
                $team->teamgenoten = $this->joomlaGateway->GetTeamgenoten($team);
                $team->programma = $this->nevoboGateway->GetWedstrijdenForTeam($team);
                $this->invalTeams[] =  $team;
            }

            if (count($this->invalTeams) >= 5) {
                return;
            }
        }
    }

    private function GetAanwezighedenForWedstrijd(string $matchId, array $aanwezigheden, Team $team): object
    {
        $result = (object) [
            'aanwezigen' => [],
            'afwezigen' => [],
            'onbekend' => [],
            'coaches' => []
        ];
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->matchId == $matchId) {
                if ($aanwezigheid->IsCoach()) {
                    $result->coaches[] = $aanwezigheid;
                } else {
                    $newAanwezigheid = (object) [
                        "id" => $aanwezigheid->persoon->id,
                        "naam" => $aanwezigheid->persoon->naam,
                        "isInvaller" => !$team->Equals($aanwezigheid->persoon->team ?? null)
                    ];
                    if ($aanwezigheid->isAanwezig) {
                        $result->aanwezigen[] = $newAanwezigheid;
                    } else {
                        $result->afwezigen[] = $newAanwezigheid;
                    }
                }
            }
        }
        return $result;
    }

    private function GetOnbekenden(object $aanwezigheden, Team $team)
    {
        $spelers = $team->spelers;
        $bekendeAanwezigheden = array_merge($aanwezigheden->aanwezigen, $aanwezigheden->afwezigen);
        if (count($bekendeAanwezigheden) == 0 || $spelers === null || count($spelers) == 0) {
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

    private function IsAanwezig(array $aanwezigheden, string $matchId, int $userId)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->persoon->id === $userId && $aanwezigheid->matchId === $matchId) {
                return $aanwezigheid->isAanwezig;
            }
        }
        return null;
    }
}
