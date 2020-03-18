<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\Gateways\TelFluitGateway;
use UnexpectedValueException;

class GetTelTeams implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        TelFluitGateway $telFluitGateway,
        NevoboGateway $nevoboGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->telFluitGateway =  $telFluitGateway;
        $this->nevoboGateway = $nevoboGateway;
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

        $teams = $this->telFluitGateway->GetTelTeams();
        $wedstrijden = $this->GetWedstrijdenWithDate($uscWedstrijden, $telWedstrijd->timestamp);

        $result = new Teamsamenvatting();
        foreach ($teams as $team) {
            $wedstrijd = $team->GetWedstrijdOfTeam($wedstrijden);

            $telteam = new TelteamModel;
            $telteam->naam = $team->naam;
            $telteam->geteld = $team->aantalKeerGeteld;

            if ($wedstrijd) {
                $telteam->isMogelijk = $wedstrijd->IsMogelijk($telWedstrijd);
                $telteam->eigenTijd = DateFunctions::GetTime($wedstrijd->timestamp);
                $result->spelendeTeams[] = $telteam;;
            } else {
                $telteam->isMogelijk = true;
                $telteam->eigenTijd = null;
                $result->overigeTeams[] = $telteam;
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
}
