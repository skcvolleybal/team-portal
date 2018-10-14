<?php
include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'TelFluitGateway.php';
include_once 'NevoboGateway.php';
include_once 'ScheidscoFunctions.php';

class GetTelTeams implements IInteractorWithData
{
    private $telFluitGateway;

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId == null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }
        $result = [];

        $matchId = $data->matchId ?? null;
        if ($matchId == null) {
            InternalServerError("MatchId niet gezet");
        }
        $telWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal("LDNUN");
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd['id'] == $matchId) {
                $telWedstrijd = $wedstrijd;
                break;
            }
        }
        if ($telWedstrijd == null) {
            InternalServerError("Wedstrijd met $matchId niet bekend");
        }

        $telTeams = $this->telFluitGateway->GetTelTeams();
        $wedstrijdenWithSameDate = GetWedstrijdenWithDate($uscWedstrijden, $telWedstrijd['timestamp']);

        $result = ["spelendeTeams" => [], "overigeTeams" => []];
        foreach ($telTeams as $team) {
            $wedstrijd = GetWedstrijdOfTeam($wedstrijdenWithSameDate, $team['naam']);
            if ($wedstrijd != null) {
                $result["spelendeTeams"][] = $this->MapToUsecaseModel($team, $wedstrijd, $telWedstrijd);
            } else {
                $result["overigeTeams"][] = $this->MapToUsecaseModel($team);
            }
        }
        exit(json_encode($result));
    }

    private function MapToUsecaseModel($team, $wedstrijd = null, $telWedstrijd = null)
    {
        $eigenTijd = null;
        $isMogelijk = true;
        if ($wedstrijd != null && $telWedstrijd != null && $wedstrijd['timestamp'] != null && $telWedstrijd['timestamp']) {
            $interval = $wedstrijd['timestamp']->diff($telWedstrijd['timestamp']);
            $verschil = $interval->h;
            $isMogelijk = $verschil == 0 ? false : ($verschil == 2 ? true : null);
            $eigenTijd = $wedstrijd['timestamp']->format("G:i");
        }
        return [
            "naam" => $team['naam'],
            "geteld" => $team['geteld'],
            "eigenTijd" => $eigenTijd,
            "isMogelijk" => $isMogelijk,
        ];
    }
}
