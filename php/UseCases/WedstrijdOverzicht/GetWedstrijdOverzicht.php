<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways;
use TeamPortal\Entities\Speler;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\Wedstrijd;

class GetWedstrijdOverzicht implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\AanwezigheidGateway $aanwezigheidGateway,
        Gateways\NevoboGateway $nevoboGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null)
    {
        $overzicht = [];
        $user = $this->joomlaGateway->GetUser();
        if ($user->team !== null) {
            $user->team->teamgenoten = $this->joomlaGateway->GetTeamgenoten($user->team);
        }

        $teamprogramma = $this->nevoboGateway->GetWedstrijdenForTeam($user->team);
        $coachProgramma = $this->nevoboGateway->GetWedstrijdenForTeam($user->coachteam);
        $wedstrijden = array_merge(
            $teamprogramma,
            $coachProgramma
        );
        usort($wedstrijden, [Wedstrijd::class, "Compare"]);

        $teamMatchIds = array_map(function ($wedstrijd) {
            return $wedstrijd->matchId;
        }, $teamprogramma);
        $coachMatchIds = array_map(function ($wedstrijd) {
            return $wedstrijd->matchId;
        }, $coachProgramma);
        $allMatchIds = array_merge($teamMatchIds, $coachMatchIds);

        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezighedenForMatchIds($allMatchIds);

        $invalteams = $this->GetInvalteamsForTeam($user->team);
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp) {
                $newWedstrijd = new WedstrijdModel($wedstrijd);
                $newWedstrijd->SetPersonalInformation($user);

                $aanwezighedenForMatch = new Aanwezigheidssamenvatting();

                $isEigenWedstrijd = $wedstrijd->IsEigenWedstrijd($user);
                if ($isEigenWedstrijd) {
                    $teams = $this->GetInvalteams($invalteams, $wedstrijd);
                    foreach ($teams as $team) {
                        $newWedstrijd->invalTeams[] = new InvalteamModel($team);
                    }

                    $aanwezighedenForMatch = $this->GetAanwezighedenForWedstrijd($wedstrijd->matchId, $aanwezigheden, $user->team);
                    $aanwezighedenForMatch->onbekend = $this->GetOnbekenden($aanwezighedenForMatch, $user->team);
                }

                $newWedstrijd->isAanwezig = $this->IsAanwezig($aanwezigheden, $wedstrijd->matchId, $user);
                $newWedstrijd->aanwezigen = $aanwezighedenForMatch->aanwezigen;
                $newWedstrijd->afwezigen = $aanwezighedenForMatch->afwezigen;
                $newWedstrijd->onbekend = $aanwezighedenForMatch->onbekend;
                foreach ($aanwezighedenForMatch->coaches as $coach) {
                    $newCoach = new CoachModel($coach->persoon);
                    $newCoach->isAanwezig = $coach->isAanwezig;
                    $newWedstrijd->coaches[] = $newCoach;
                }

                $newWedstrijd->isEigenWedstrijd = $isEigenWedstrijd;

                $overzicht[] = $newWedstrijd;
            }
        }

        return $overzicht;
    }

    private function GetInvalteams(array $invalteams, Wedstrijd $eigenWedstrijd)
    {
        $result = [];
        $datumWedstrijd = DateFunctions::GetYmdNotation($eigenWedstrijd->timestamp);
        foreach ($invalteams as $invalTeam) {
            $invalTeamWedstrijd = null;
            foreach ($invalTeam->programma as $wedstrijd) {
                if ($wedstrijd->timestamp === null) {
                    continue;
                }

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

            $newInvalteam = new Invalteam($invalTeam, $invalTeamWedstrijd);
            $newInvalteam->isMogelijk = $wedstrijd->IsMogelijk($invalTeamWedstrijd);
            $result[] = $newInvalteam;
        }
        return $result;
    }

    private function GetInvalteamsForTeam(?Team $eigenTeam): array
    {
        $invalTeams = [];

        if ($eigenTeam === null) {
            return $invalTeams;
        }
        $teams = $eigenTeam->IsMale() ?
            Team::GetAlleHerenTeams() :
            Team::GetAlleDamesTeams();


        foreach ($teams as $team) {
            if ($team->naam != $eigenTeam->naam && $team->niveau >= $eigenTeam->niveau) {
                $team->teamgenoten = $this->joomlaGateway->GetTeamgenoten($team);
                $team->programma = $this->nevoboGateway->GetWedstrijdenForTeam($team);
                $invalTeams[] =  $team;
            }

            if (count($invalTeams) >= 5) {
                return $invalTeams;
            }
        }

        return $invalTeams;
    }

    private function GetAanwezighedenForWedstrijd(string $matchId, array $aanwezigheden, Team $team): Aanwezigheidssamenvatting
    {
        $result = new Aanwezigheidssamenvatting();
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->matchId == $matchId) {
                if ($aanwezigheid->IsCoach()) {
                    $result->coaches[] = $aanwezigheid;
                } else {
                    $newAanwezigheid = new Speler(
                        $aanwezigheid->persoon->id,
                        $aanwezigheid->persoon->naam,
                        !$team->Equals($aanwezigheid->persoon->team)
                    );
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
        $teamgenoten = $team->teamgenoten;
        $bekendeAanwezigheden = array_merge($aanwezigheden->aanwezigen, $aanwezigheden->afwezigen);
        if (count($bekendeAanwezigheden) == 0) {
            return $teamgenoten;
        }

        foreach ($bekendeAanwezigheden as $aanwezigheid) {
            for ($i = 0; $i < count($teamgenoten); $i++) {
                if ($aanwezigheid->id === $teamgenoten[$i]->id) {
                    array_splice($teamgenoten, $i, 1);
                    break;
                }
            }
        }
        return $teamgenoten;
    }

    private function IsAanwezig(array $aanwezigheden, string $matchId, Persoon $user)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->persoon->id === $user->id && $aanwezigheid->matchId === $matchId) {
                return $aanwezigheid->isAanwezig;
            }
        }
        return null;
    }
}
