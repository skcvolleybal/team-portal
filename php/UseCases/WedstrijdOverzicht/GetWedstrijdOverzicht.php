<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Coach;
use TeamPortal\Entities\Invaller;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways;
use TeamPortal\Entities\Speler;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\Wedstrijd;
use UnexpectedValueException;

error_reporting(E_ALL ^ E_DEPRECATED); // Suppress warnings on PHP 8.0. Make sure to fix the usort() functions in this file for PHP 8.1. 


class GetWedstrijdOverzicht implements Interactor
{
    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\AanwezigheidGateway $aanwezigheidGateway,
        Gateways\NevoboGateway $nevoboGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null)
    {
        $overzicht = [];
        $user = $this->wordPressGateway->GetUser();
        if ($user->team !== null) {
            $user->team->teamgenoten = $this->wordPressGateway->GetTeamgenoten($user->team);
        }

        $teamprogramma = $this->nevoboGateway->GetWedstrijdenForTeam($user->team);
        $coachProgramma = [];
        foreach ($user->coachteams as $team) {
            $coachwedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
            $coachProgramma = array_merge(
                $coachwedstrijden,
                $coachProgramma
            );
        }
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

        $eigenInvalteams = $this->GetInvalteamsForTeam($user->team);
        $coachInvalteams = [];
        foreach ($user->coachteams as $team) {
            $coachInvalteams[] = $this->GetInvalteamsForTeam($team);
        }

        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp) {
                $newWedstrijd = new WedstrijdModel($wedstrijd);
                $newWedstrijd->SetPersonalInformation($user);

                $isEigenWedstrijd = $wedstrijd->IsEigenWedstrijd($user);
                if ($isEigenWedstrijd) {
                    $invalteams = $this->GetInvalteams($eigenInvalteams, $wedstrijd);
                    $team = $user->team;
                } else {
                    $i = $this->GetCoachteamIndex($wedstrijd, $user->coachteams);
                    $invalteams = $this->GetInvalteams($coachInvalteams[$i], $wedstrijd);
                    $team = $user->coachteams[$i];
                }

                foreach ($invalteams as $invalteam) {
                    $newWedstrijd->invalTeams[] = new InvalteamModel($invalteam);
                }

                $aanwezighedenForMatch = $this->GetAanwezighedenForWedstrijd($wedstrijd->matchId, $aanwezigheden, $team);
                $newWedstrijd->onbekend = $this->GetOnbekenden($aanwezighedenForMatch, $team);
                $newWedstrijd->isAanwezig = $this->IsAanwezig($aanwezigheden, $wedstrijd->matchId, $user);
                $newWedstrijd->aanwezigen = $aanwezighedenForMatch->aanwezigen;
                $newWedstrijd->afwezigen = $aanwezighedenForMatch->afwezigen;

                $newWedstrijd->isEigenWedstrijd = $isEigenWedstrijd;

                $overzicht[] = $newWedstrijd;
            }
        }

        return $overzicht;
    }

    private function GetCoachteamIndex(Wedstrijd $wedstrijd, array $coachteams): int
    {
        foreach ($coachteams as $i => $team) {
            if ($team->naam === $wedstrijd->team1->naam  || $team->naam === $wedstrijd->team2->naam) {
                return $i;
            }
        }
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
                $team->teamgenoten = $this->wordPressGateway->GetTeamgenoten($team);
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
                if ($aanwezigheid->rol == "speler") {
                    if ($team->Equals($aanwezigheid->persoon->team)) {
                        $persoon = new Speler(
                            $aanwezigheid->persoon->id,
                            $aanwezigheid->persoon->naam,
                            $aanwezigheid->persoon->email
                        );
                    } else {
                        $persoon = new Invaller(
                            $aanwezigheid->persoon->id,
                            $aanwezigheid->persoon->naam,
                            $aanwezigheid->persoon->email
                        );
                    }
                } else if ($aanwezigheid->rol == "coach") {
                    $persoon = new Coach(
                        $aanwezigheid->persoon->id,
                        $aanwezigheid->persoon->naam,
                        $aanwezigheid->persoon->email
                    );
                }

                if ($aanwezigheid->isAanwezig) {
                    $result->aanwezigen[] = $persoon;
                } else {
                    $result->afwezigen[] = $persoon;
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
