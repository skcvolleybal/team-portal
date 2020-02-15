<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways;
use TeamPortal\Entities\Team;
use UnexpectedValueException;

class GetTelTeams implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\NevoboGateway $nevoboGateway
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

        $teams = $this->telFluitGateway->GetTelTeams();
        $wedstrijden = $this->GetWedstrijdenWithDate($uscWedstrijden, $telWedstrijd->timestamp);

        $result = new Teamsamenvatting();
        foreach ($teams as $team) {
            $wedstrijd = $team->GetWedstrijdOfTeam($wedstrijden);
            if ($wedstrijd) {
                $isMogelijk = $wedstrijd->IsMogelijk($telWedstrijd);
                $eigenTijd = DateFunctions::GetTime($wedstrijd->timestamp);
                $result->spelendeTeams[] = $this->MapToUsecaseModel($team, $isMogelijk, $eigenTijd);
            } else {
                $result->overigeTeams[] = $this->MapToUsecaseModel($team, true, null);
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

    private function MapToUsecaseModel(Team $team, bool $isMogelijk, ?string $eigenTijd)
    {
        return (object) [
            "naam" => $team->naam,
            "geteld" => $team->aantalKeerGeteld,
            "eigenTijd" => $eigenTijd,
            "isMogelijk" => $isMogelijk,
        ];
    }
}
