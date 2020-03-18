<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\Gateways\ZaalwachtGateway;

class GetZaalwachtTeams implements Interactor
{
    public function __construct(
        ZaalwachtGateway $zaalwachtGateway,
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway
    ) {
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null): object
    {
        $date = DateFunctions::CreateDateTime($data->date);
        if ($date === null) {
            throw new InvalidArgumentException("Geen datum meegegeven");
        }

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $zaalwachtteams = $this->zaalwachtGateway->GetZaalwachtSamenvatting();
        $spelendeTeams = $this->GetSpelendeTeamsForDate($uscWedstrijden, $date);

        $result = new Teamsamenvatting();
        foreach ($zaalwachtteams as $team) {
            $newTeam = new TeamModel;
            $newTeam->naam = $team->GetSkcNaam();
            $newTeam->aantal = $team->aantalZaalwachten;

            if (in_array($team->GetSkcNaam(), $spelendeTeams)) {
                $result->spelendeTeams[] = $newTeam;
            } else {
                $result->overigeTeams[] = $newTeam;
            }
        }
        return $result;
    }

    private function GetSpelendeTeamsForDate($wedstrijden, $date)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            if (DateFunctions::AreDatesEqual($wedstrijd->timestamp, $date)) {
                $result[] = $wedstrijd->team1->GetSkcNaam();
            }
        }
        return $result;
    }
}
