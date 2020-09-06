<?php

namespace TeamPortal\UseCases;

use DateTime;
use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Beschikbaarheid;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways\BeschikbaarheidGateway;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\Gateways\TelFluitGateway;
use UnexpectedValueException;

class GetTellers implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        TelFluitGateway $telFluitGateway,
        NevoboGateway $nevoboGateway,
        BeschikbaarheidGateway $beschikbaarheidGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->telFluitGateway =  $telFluitGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->beschikbaarheidGateway = $beschikbaarheidGateway;
    }

    public function Execute(object $data = null)
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("MatchId niet gezet");
        }
        $telWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->matchId == $data->matchId) {
                $telWedstrijd = $wedstrijd;
                break;
            }
        }
        if ($telWedstrijd === null) {
            throw new UnexpectedValueException("Wedstrijd met $data->matchId niet bekend");
        }

        if ($telWedstrijd->timestamp === null) {
            throw new UnexpectedValueException("Wedstrijd staat op het programma, maar heeft geen tijdstip");
        }

        $teams = $this->telFluitGateway->GetTellers();
        $wedstrijden = $this->GetWedstrijdenWithDate($uscWedstrijden, $telWedstrijd->timestamp);
        $beschikbaarheden = $this->beschikbaarheidGateway->GetAllBeschikbaarheden($telWedstrijd->timestamp);

        $result = new Teamsamenvatting();
        foreach ($teams as $team) {
            $wedstrijd = $team->GetWedstrijdOfTeam($wedstrijden);

            $newTeam = new TeamModel;
            $newTeam->naam = $team->naam;
            $newTeam->teamgenoten = $team->teamgenoten;

            if ($wedstrijd) {
                $newTeam->isMogelijk = $wedstrijd->IsMogelijk($telWedstrijd);
                $newTeam->eigenTijd = DateFunctions::GetTime($wedstrijd->timestamp);
                $result->spelendeTeams[] = $newTeam;
            } else {
                $newTeam->isMogelijk = true;
                $newTeam->eigenTijd = null;
                $result->overigeTeams[] = $newTeam;
            }

            foreach ($newTeam->teamgenoten as $teamgenoot) {
                $beschikbaarheid = $this->GetBeschikbaarheid($beschikbaarheden, $teamgenoot, $telWedstrijd->timestamp);
                if ($beschikbaarheid) {
                    $teamgenoot->isBeschikbaar = $beschikbaarheid->isBeschikbaar;
                }
            }
        }
        return $result;
    }

    private function GetWedstrijdenWithDate($wedstrijden, $date): array
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $timestamp = $wedstrijd->timestamp;
            if ($timestamp && $timestamp->format('Y-m-d') == $date->format('Y-m-d')) {
                $result[] = $wedstrijd;
            }
        }
        return $result;
    }

    private function GetBeschikbaarheid($beschikbaarheden, Persoon $teamgenoot, DateTime $timestamp): ?Beschikbaarheid
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $timestamp && $beschikbaarheid->persoon->id === $teamgenoot->id) {
                return $beschikbaarheid;
            }
        }

        return null;
    }
}
