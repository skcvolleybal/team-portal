<?php
include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'ZaalwachtGateway.php';
include_once 'NevoboGateway.php';

class GetZaalwachtTeams implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
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

        $date = $data->date ?? null;
        if ($date == null) {
            throw new InvalidArgumentException("Geen datum meegegeven");
        }

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal("LDNUN");
        $zaalwachtTeams = $this->zaalwachtGateway->GetZaalwachtTeams();
        $spelendeTeams = $this->GetSpelendeTeamsForDate($uscWedstrijden, $date);

        $result = (object) [
            "spelendeTeams" => [],
            "overigeTeams" => []
        ];
        foreach ($zaalwachtTeams as $team) {
            if (in_array($team->naam, $spelendeTeams)) {
                $result->spelendeTeams[] = $this->MapToUsecaseModel($team);
            } else {
                $result->overigeTeams[] = $this->MapToUsecaseModel($team);
            }
        }
        exit(json_encode($result));
    }

    private function GetSpelendeTeamsForDate($wedstrijden, $date)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $timestamp = $wedstrijd->timestamp;
            if ($timestamp && $timestamp->format('Y-m-d') == $date) {
                $result[] = ToSkcName($wedstrijd->team1);
            }
        }
        return $result;
    }

    private function MapToUsecaseModel($team)
    {
        return (object) [
            "naam" => $team->naam,
            "zaalwacht" => $team->zaalwacht,
        ];
    }
}
