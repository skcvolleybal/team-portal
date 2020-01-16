<?php

class GetTelTeams implements IInteractorWithData
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

    public function Execute(object $data)
    {
        $result = [];

        $matchId = $data->matchId ?? null;
        if ($matchId === null) {
            throw new InvalidArgumentException("MatchId niet gezet");
        }
        $telWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal("LDNUN");
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->matchId == $matchId) {
                $telWedstrijd = $wedstrijd;
                break;
            }
        }
        if ($telWedstrijd === null) {
            throw new UnexpectedValueException("Wedstrijd met $matchId niet bekend");
        }

        $teams = $this->telFluitGateway->GetTelTeams();
        $wedstrijden = $this->GetWedstrijdenWithDate($uscWedstrijden, $telWedstrijd->timestamp);

        $result = (object) [
            "spelendeTeams" => [],
            "overigeTeams" => []
        ];
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
