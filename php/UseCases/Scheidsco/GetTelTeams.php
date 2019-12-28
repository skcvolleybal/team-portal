<?php

class GetTelTeams implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen teamcoordinator");
        }
        $result = [];

        $matchId = $data->matchId ?? null;
        if ($matchId == null) {
            throw new InvalidArgumentException("MatchId niet gezet");
        }
        $telWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal("LDNUN");
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->id == $matchId) {
                $telWedstrijd = $wedstrijd;
                break;
            }
        }
        if ($telWedstrijd == null) {
            throw new UnexpectedValueException("Wedstrijd met $matchId niet bekend");
        }

        $telTeams = $this->telFluitGateway->GetTelTeams();
        $wedstrijdenWithSameDate = GetWedstrijdenWithDate($uscWedstrijden, $telWedstrijd->timestamp);

        $result = (object) [
            "spelendeTeams" => [],
            "overigeTeams" => []
        ];
        foreach ($telTeams as $team) {
            $wedstrijd = GetWedstrijdOfTeam($wedstrijdenWithSameDate, $team->naam);
            if ($wedstrijd) {
                $result->spelendeTeams[] = $this->MapToUsecaseModel($team, $wedstrijd, $telWedstrijd);
            } else {
                $result->overigeTeams[] = $this->MapToUsecaseModel($team);
            }
        }
        exit(json_encode($result));
    }

    private function MapToUsecaseModel($team, $wedstrijd = null, $telWedstrijd = null)
    {
        $eigenTijd = null;
        $isMogelijk = true;
        if ($wedstrijd && $telWedstrijd && $wedstrijd->timestamp && $telWedstrijd->timestamp) {
            $interval = $wedstrijd->timestamp->diff($telWedstrijd->timestamp);
            $verschil = $interval->h;
            $isMogelijk = $verschil == 0 ? false : ($verschil == 2 ? true : null);
            $eigenTijd = $wedstrijd->timestamp->format("G:i");
        }
        return (object) [
            "naam" => $team->naam,
            "geteld" => $team->geteld,
            "eigenTijd" => $eigenTijd,
            "isMogelijk" => $isMogelijk === null ? "Onbekend" : $isMogelijk ? "Ja" : "Nee",
        ];
    }
}
